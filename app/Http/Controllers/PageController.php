<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Page;
use App\Models\PageHistory;
use App\Helpers\StringHelper;
use App\Helpers\MailHelper;
use App\Helpers\PageHelper;
use Carbon\Carbon;
use DataTables;

class PageController extends Controller
{

    public function index(Request $request, $page)
    {
        $real_page = $this->realUrl($page);
        if ($page <> $real_page) {
            return redirect()->route('page', ['page' => $real_page]);
        }

        $title = StringHelper::toTitle($page);
        $slug = StringHelper::toSlug($page);

        $data = Page::where('slug', $slug)->first();
        $data_histories = PageHistory::with('user')->where('slug', $slug)->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $description = ($data ? StringHelper::substring($data->content_text, 150) : 'Página inexistente');

        return view('page.index', compact('page', 'title', 'data', 'data_histories', 'description'));
    }

    public function edit(Request $request, $page)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('warning', 'É necessário estar logado para editar páginas!');
        }

        $real_page = $this->realUrl($page);
        if ($page <> $real_page) {
            return redirect()->route('page.edit', ['page' => $real_page]);
        }

        $title = StringHelper::toTitle($page);
        $slug = StringHelper::toSlug($page);

        $data = Page::where('slug', $slug)->first();

        return view('page.edit', compact('page', 'title', 'data'));
    }

    public function update(Request $request, $page)
    {
        $real_page = $this->realUrl($page);
        if ($page <> $real_page) {
            return redirect()->route('page.edit', ['page' => $real_page])->withError('URL inválida!');
        }

        $slug = StringHelper::toSlug($page);
        $p = Page::where('slug', $slug)->first();
        if ($p) {
            if ($p["content_editor"] == $request->input('content')) {
                PageHelper::refresh($slug);
                return redirect()->route('page', ['page' => $page])->withWarning('Nenhuma alteração foi feita!');
            }
        }

        $this->save($page, [
            'path' => $page,
            'content_editor' => $request->input('content'),
        ]);

        return redirect()->route('page', ['page' => $page]);
    }

    public function refresh(Request $request, $page)
    {
        $real_page = $this->realUrl($page);
        if ($page <> $real_page) {
            return redirect()->route('page', ['page' => $real_page])->withError('URL inválida!');
        }

        $slug = StringHelper::toSlug($page);
        PageHelper::refresh($slug);
        return redirect()->route('page', ['page' => $page])->withSuccess('Página atualizada!');
    }
    /*
    public function delete($page)
    {
        $real_page = $this->realUrl($page);
        if ($page <> $real_page) {
            return redirect()->route('page.edit', ['page' => $real_page])->withError('URL inválida!');
        }

        $slug = StringHelper::toSlug($page);

        $page = Page::where('slug', $slug)->first();
        if (!$page) {
            return redirect()->route('page.edit', ['page' => StringHelper::toUrl($page)])->withError('Pagem não encontrada!');
        }

        PageHistory::create([
            'page_id' => $page->id,
            'title' => $page->title,
            'name' => $page->name,
            'slug' => $page->slug,
            'updated_by' => auth()->user()->id,
        ]);

        Page::where('slug', $page->slug)->delete();

        MailHelper::addToQueue('notify_update_page', '[delete_page] ' . $page->title, 'Pagem apagada! ' . MailHelper::pageData($page));

        return redirect()->route('page', ['page' => $page]);
    }
*/
    public function history(Request $request, $page)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('warning', 'É necessário estar logado para visualizar o histórico de envios!');
        }

        $real_page = $this->realUrl($page);
        if ($page <> $real_page) {
            return redirect()->route('page.log', ['page' => $real_page]);
        }

        $title = StringHelper::toTitle($page);
        $slug = StringHelper::toSlug($page);

        $page = Page::where('slug', $slug)->first();

        return view('page.log', compact('page', 'title', 'page'));
    }

    public function pdf(Request $request, $page)
    {
        $real_page = $this->realUrl($page);
        if ($page <> $real_page) {
            return redirect()->route('page.log', ['page' => $real_page]);
        }

        $title = StringHelper::toTitle($page);
        $slug = StringHelper::toSlug($page);

        $data = Page::where('slug', $slug)->first();

        //return view('pdf.page', $page);
        $pdf = Pdf::loadView('pdf.page', compact('data', 'page', 'slug', 'title'));
        $pdf->setPaper('a4');

        return $pdf->stream($title . '.pdf');
    }

    public function restore($page, $id)
    {
        $real_page = $this->realUrl($page);
        if ($page <> $real_page) {
            return redirect()->route('page.log', ['page' => $real_page])->withError('URL inválida!');
        }

        $slug = StringHelper::toSlug($page);

        $page = PageHistory::where(['slug' => $slug, 'id' => $id])->first();
        if (!$page) {
            return redirect()->route('page.log', ['page' => StringHelper::toUrl($page)])->withError('Não foi possível reverter alteração!');
        }

        $data = $page->toArray();
        $data["restore_id"] = $id;
        $this->save($page, $data);

        return redirect()->route('page', ['page' => $page]);
    }

    public function datatable($page)
    {
        $slug = StringHelper::toSlug($page);
        $data = PageHistory::with('user')->where('slug', $slug)->select();
        $maxId = PageHistory::with('user')->where('slug', $slug)->max('id');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('actions', function ($row) use ($page, $maxId) {
                $btn = "";
                if ($row->id <> $maxId && $row->path <> "") {
                    $btn .= "<a class='btn btn-sm btn-success' href='" . route('page.restore', ['page' => StringHelper::toUrl($page), 'id' => $row->id]) . "'>Restaurar</a>";
                }
                return $btn;
            })
            ->editColumn('updated_at', function ($row) {
                return Carbon::parse($row->updated_at)->format('d/m/Y H:i:s');
            })
            ->addColumn('page', function ($row) {
                if ($row->path <> "") {
                    return "<img src='" . $row->page_url . "' class='avatar img-fluid rounded' style='object-fit: cover;'>";
                } else {
                    return "<span class='badge bg-danger'>Pagem Excluída</span>";
                }
            })
            ->editColumn('user.name', function ($row) {
                return "<img src='" . $row->user->avatar . "' class='avatar img-fluid rounded-circle' alt='" . $row->user->name . "'>"
                    . "<span class='px-2 fw-bold'>" . $row->user->name . "</span>";
            })
            ->rawColumns(['actions', 'page', 'user.name'])
            ->make(true);
    }


    public function save($page, $data = [])
    {
        $mail_subject = '';
        $mail_text = '';

        $slug = StringHelper::toSlug($page);

        $data['title'] = StringHelper::toTitle($page);
        $data['slug'] = $slug;
        $data['name'] = StringHelper::toUrl($page);
        $data['updated_by'] = auth()->user()->id;

        $page = Page::where('slug', $slug)->first();
        if ($page) {
            $page->update($data);
            $mail_subject = '[update_page]';
            $mail_text = 'Página alterada!';
        } else {
            Page::create($data);
            $page = Page::where('slug', $slug)->first();
            $mail_subject = '[new_page]';
            $mail_text = 'Nova página cadastrada!';
        }

        PageHelper::refresh($slug);

        $data["page_id"] = $page->id;

        PageHistory::create($data);

        MailHelper::addToQueue('notify_update_page', $mail_subject . ' ' . $data['title'], $mail_text . MailHelper::pageData($page));
    }

    public function realUrl($page)
    {
        if ($page <> StringHelper::toUrl($page)) {
            return StringHelper::toUrl($page);
        }

        $slug = StringHelper::toSlug($page);
        $data = Page::where('slug', $slug)->first();
        if ($data) {
            if ($page <> $data->name) {
                return  $data->name;
            }
        }

        return $page;
    }
}
