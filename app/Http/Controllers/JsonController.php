<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\StringHelper;
use App\Models\Image;
use App\Models\PageCategory;
use Illuminate\Support\Facades\DB;

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

    public function categories_input(Request $request, $page = '')
    {
        $array = [];
        $categories = PageCategory::select(['category_title as value', 'category_title as label'])->distinct()->get();
        if ($categories) {
            $array = $categories->toArray();

            $slug = StringHelper::toSlug($page);
            $categories_selected = PageCategory::where('slug', $slug)->select(['category_title as value', 'category_title as label', DB::raw('"true" as selected')])->distinct()->get();
            $array = array_merge($array, $categories_selected->toArray());
        }
        return response()->json($array);
    }
}
