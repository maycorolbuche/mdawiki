<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;

class JsonController extends Controller
{

    public function images(Request $request)
    {
        $perPage = 20;

        $images = Image::orderBy('title', 'asc')->paginate($perPage);

        $images->getCollection()->transform(function ($image) {
            return [
                ...$image->toArray(),
                'full_url' => $image->full_url,
            ];
        });

        return response()->json($images);
    }
}
