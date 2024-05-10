<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;

class JsonController extends Controller
{

    public function images(Request $request)
    {
        $perPage = 20;

        $search = $request->input("search");

        $images = Image::orderBy('title', 'asc');
        if ($search) {
            $keywords = explode(' ', $search);
            $images->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('title', 'like', '%' . $keyword . '%');
                }
            });
        }
        $images = $images->paginate($perPage);

        $images->getCollection()->transform(function ($image) {
            return [
                ...$image->toArray(),
                'image_url' => $image->image_url,
                'url' => $image->image_url,
            ];
        });

        return response()->json($images);
    }
}
