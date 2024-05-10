<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\ImageHistory;
use App\Helpers\StringHelper;
use App\Helpers\ImageHelper;
use App\Helpers\MailHelper;
use Carbon\Carbon;
use DataTables;

class ImageController extends Controller
{
    protected $max_size = 400;

    public function index(Request $request, $page = '')
    {
        if ($page == '') {
            echo "TESTE";
            exit;
        }

        $real_page = $this->realUrl($page);
        if ($page <> $real_page) {
            return redirect()->route('image', ['page' => $real_page]);
        }

        $title = StringHelper::toTitle($page);
        $slug = StringHelper::toSlug($page);

        $image = Image::where('slug', $slug)->first();
        $image_histories = ImageHistory::with('user')->where('slug', $slug)->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('image.index', compact('page', 'title', 'image', 'image_histories'));
    }

    public function edit(Request $request, $page)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('warning', 'É necessário estar logado para enviar imagens!');
        }

        $real_page = $this->realUrl($page);
        if ($page <> $real_page) {
            return redirect()->route('image.edit', ['page' => $real_page]);
        }

        $title = StringHelper::toTitle($page);
        $max_size = $this->max_size;
        $slug = StringHelper::toSlug($page);

        $image = Image::where('slug', $slug)->first();

        return view('image.edit', compact('page', 'title', 'max_size', 'image'));
    }

    public function update(Request $request, $page)
    {
        $real_page = $this->realUrl($page);
        if ($page <> $real_page) {
            return redirect()->route('image.edit', ['page' => $real_page])->withError('URL inválida!');
        }


        $image = ImageHelper::saveBase64($request->input('image'), 'images/pages');
        if (!$image) {
            return redirect()->route('image.edit', ['page' => StringHelper::toUrl($page)])->withError('Ocorreu um erro ao enviar esta imagem! Verifique se é um arquivo de imagem válido, ou se não está corrompido.');
        }

        $image_info = ImageHelper::info($image);

        $this->save($page, [
            'path' => $image,
            'width' => $image_info["width"],
            'height' => $image_info["height"],
            'mime' => $image_info["mime"],
            'size' => $image_info["size"],
        ]);

        return redirect()->route('image', ['page' => $page]);
    }

    public function delete($page)
    {
        $real_page = $this->realUrl($page);
        if ($page <> $real_page) {
            return redirect()->route('image.edit', ['page' => $real_page])->withError('URL inválida!');
        }

        $slug = StringHelper::toSlug($page);

        $image = Image::where('slug', $slug)->first();
        if (!$image) {
            return redirect()->route('image.edit', ['page' => StringHelper::toUrl($page)])->withError('Imagem não encontrada!');
        }

        ImageHistory::create([
            'image_id' => $image->id,
            'title' => $image->title,
            'name' => $image->name,
            'slug' => $image->slug,
            'updated_by' => auth()->user()->id,
        ]);

        Image::where('slug', $image->slug)->delete();

        MailHelper::addToQueue('notify_update_page', '[delete_image] ' . $image->title, 'Imagem apagada! ' . MailHelper::imageData($page));

        return redirect()->route('image', ['page' => $page]);
    }

    public function history(Request $request, $page)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('warning', 'É necessário estar logado para visualizar o histórico de envios!');
        }

        $real_page = $this->realUrl($page);
        if ($page <> $real_page) {
            return redirect()->route('image.log', ['page' => $real_page]);
        }

        $title = StringHelper::toTitle($page);
        $slug = StringHelper::toSlug($page);

        $image = Image::where('slug', $slug)->first();

        return view('image.log', compact('page', 'title', 'image'));
    }
    public function restore($page, $id)
    {
        $real_page = $this->realUrl($page);
        if ($page <> $real_page) {
            return redirect()->route('image.log', ['page' => $real_page])->withError('URL inválida!');
        }

        $slug = StringHelper::toSlug($page);

        $image = ImageHistory::where(['slug' => $slug, 'id' => $id])->first();
        if (!$image) {
            return redirect()->route('image.log', ['page' => StringHelper::toUrl($page)])->withError('Não foi possível reverter alteração!');
        }

        $data = $image->toArray();
        $data["restore_id"] = $id;
        $this->save($page, $data);

        return redirect()->route('image', ['page' => $page]);
    }

    public function datatable($page)
    {
        $slug = StringHelper::toSlug($page);
        $data = ImageHistory::with('user')->where('slug', $slug)->select();
        $maxId = ImageHistory::with('user')->where('slug', $slug)->max('id');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('actions', function ($row) use ($page, $maxId) {
                $btn = "";
                if ($row->id <> $maxId && $row->path <> "") {
                    $btn .= "<a class='btn btn-sm btn-success' href='" . route('image.restore', ['page' => StringHelper::toUrl($page), 'id' => $row->id]) . "'>Restaurar</a>";
                }
                return $btn;
            })
            ->editColumn('updated_at', function ($row) {
                return Carbon::parse($row->updated_at)->format('d/m/Y H:i:s');
            })
            ->addColumn('image', function ($row) {
                if ($row->path <> "") {
                    return "<img src='" . $row->image_url . "' class='avatar img-fluid rounded' style='object-fit: cover;'>";
                } else {
                    return "<span class='badge bg-danger'>Imagem Excluída</span>";
                }
            })
            ->editColumn('user.name', function ($row) {
                return "<img src='" . $row->user->avatar . "' class='avatar img-fluid rounded-circle' alt='" . $row->user->name . "'>"
                    . "<span class='px-2 fw-bold'>" . $row->user->name . "</span>";
            })
            ->rawColumns(['actions', 'image', 'user.name'])
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

        $image = Image::where('slug', $slug)->first();
        if ($image) {
            $image->update($data);
            $mail_subject = '[update_image]';
            $mail_text = 'Imagem alterada!';
        } else {
            Image::create($data);
            $image = Image::where('slug', $slug)->first();
            $mail_subject = '[new_image]';
            $mail_text = 'Nova imagem cadastrada!';
        }

        $data["image_id"] = $image->id;

        ImageHistory::create($data);

        MailHelper::addToQueue('notify_update_page', $mail_subject . ' ' . $data['title'], $mail_text . MailHelper::imageData($page));
    }

    public function realUrl($page)
    {
        if ($page <> StringHelper::toUrl($page)) {
            return StringHelper::toUrl($page);
        }

        $slug = StringHelper::toSlug($page);
        $data = Image::where('slug', $slug)->first();
        if ($data) {
            if ($page <> $data->name) {
                return  $data->name;
            }
        }

        return $page;
    }
}
