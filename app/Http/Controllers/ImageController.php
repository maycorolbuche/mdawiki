<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\ImageHistory;
use App\Helpers\StringHelper;
use App\Helpers\ImageHelper;

class ImageController extends Controller
{
    protected $max_size = 500;

    public function index(Request $request)
    {
        $page = $request->route('page');

        if ($page <> StringHelper::toUrl($page)) {
            return redirect()->route('image', ['page' => StringHelper::toUrl($page)]);
        }

        $title = StringHelper::toTitle($page);
        $slug = StringHelper::toSlug($page);

        $image = Image::where('slug', $slug)->first();
        $image_histories = ImageHistory::with('user')->where('slug', $slug)->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('image.index', compact('page', 'title', 'image', 'image_histories'));
    }

    public function edit(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('warning', 'É necessário estar logado para enviar imagens!');
        }

        $page = $request->route('page');

        if ($page <> StringHelper::toUrl($page)) {
            return redirect()->route('image.edit', ['page' => StringHelper::toUrl($page)]);
        }

        $title = StringHelper::toTitle($page);
        $warning = null;
        $max_size = $this->max_size;
        $slug = StringHelper::toSlug($page);

        $image = Image::where('slug', $slug)->first();

        return view('image.edit', compact('page', 'title', 'max_size', 'image'));
    }

    public function update(Request $request)
    {
        $page = $request->route('page');

        if ($page <> StringHelper::toUrl($page)) {
            return redirect()->route('image.edit', ['page' => StringHelper::toUrl($page)])->withError('URL inválida!');
        }


        $image = ImageHelper::saveBase64($request->input('image'), 'images/pages');
        if (!$image) {
            return redirect()->route('image.edit', ['page' => StringHelper::toUrl($page)])->withError('Ocorreu um erro ao enviar esta imagem! Verifique se é um arquivo de imagem válido, ou se não está corrompido.');
        }

        $title = StringHelper::toTitle($page);
        $slug = StringHelper::toSlug($page);

        $image_info = ImageHelper::info($image);

        $data = [
            'title' => $title,
            'url' => $page,
            'slug' => $slug,
            'path' => $image,
            'width' => $image_info["width"],
            'height' => $image_info["height"],
            'mime' => $image_info["mime"],
            'size' => $image_info["size"],
            'updated_by' => auth()->user()->id,
        ];

        $image = Image::where('slug', $slug)->first();
        if ($image) {
            $image->update($data);
        } else {
            Image::create($data);
            $image = Image::where('slug', $slug)->first();
        }

        $data = collect($image->toArray())->except(['id', 'created_at', 'updated_at'])->toArray();
        $data["image_id"] = $image->id;

        ImageHistory::create($data);

        return redirect()->route('image', ['page' => $page]);
    }

    public function delete(Request $request)
    {
        $page = $request->route('page');

        if ($page <> StringHelper::toUrl($page)) {
            return redirect()->route('image.edit', ['page' => StringHelper::toUrl($page)])->withError('URL inválida!');
        }

        $slug = StringHelper::toSlug($page);

        $image = Image::where('slug', $slug)->first();
        if (!$image) {
            return redirect()->route('image.edit', ['page' => StringHelper::toUrl($page)])->withError('Imagem não encontrada!');
        }

        ImageHistory::create([
            'image_id' => $image->id,
            'title' => $image->title,
            'url' => $image->url,
            'slug' => $image->slug,
            'updated_by' => auth()->user()->id,
        ]);

        Image::where('slug', $image->slug)->delete();

        return redirect()->route('image', ['page' => $page]);
    }

    public function history(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('warning', 'É necessário estar logado para visualizar o histórico de envios!');
        }

        $page = $request->route('page');

        if ($page <> StringHelper::toUrl($page)) {
            return redirect()->route('image.edit', ['page' => StringHelper::toUrl($page)]);
        }

        $title = StringHelper::toTitle($page);
        $warning = null;
        $slug = StringHelper::toSlug($page);

        $image = Image::where('slug', $slug)->first();

        return view('image.log', compact('page', 'title', 'image'));
    }
}
