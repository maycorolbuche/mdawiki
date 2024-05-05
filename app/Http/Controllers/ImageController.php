<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\StringHelper;

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
        $warning = null;
        $max_size = $this->max_size;

        if (!auth()->check()) {
            $warning = 'Você precisa estar logado para enviar imagens! '
                . '<a href="' . route('login') . '">Faça seu login</a> ou '
                . '<a href="' . route('register') . '">cadastre-se</a> em nosso site.';
        }

        return view('image', compact('page', 'title', 'warning', 'max_size'));
        /*'URL acessada: ' . $page
            . '<br>To URL: ' . StringHelper::toUrl($page)
            . '<br>To Slug: ' . StringHelper::toSlug($page)
            . '<br>To Title: ' . StringHelper::toTitle($page)
            . '<br>To Title TESTE: ' . StringHelper::toTitle("Máscas f_d s Tés T%C3%A9s re 10%")
            . '<br>To URL TESTE: ' . StringHelper::toUrl("Olá te_rr_");*/
    }

    public function update(Request $request)
    {
        dd($request->all());
    }
}
