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

    public function categories_input(Request $request, $page = '')
    {
        $array = [
            [
                "value" => "server1" . $page,
                "label" => "Server 1"
            ],
            [
                "value" => "server2",
                "label" => "Server 2 Orange",
                "data" => [
                    "badgeStyle" => "warning",
                    "badgeClass" => "text-dark"
                ]
            ],
            [
                "value" => "server3",
                "label" => "Server 3",
                "selected" => true
            ],
            [
                "value" => "disabledserver",
                "label" => "Server (disabled)",
                "disabled" => true
            ],
            [
                "value" => "af",
                "label" => "Server language Afrikaans",
                "nativeName" => "Afrikaans",
                "selected" => true
            ],
            [
                "value" => "sq",
                "label" => "Server language Albanian",
                "nativeName" => "Shqip"
            ],
            [
                "value" => "ar",
                "label" => "Server language Arabic",
                "nativeName" => "العربية"
            ],
            [
                "value" => "hy",
                "label" => "Server language Armenian",
                "nativeName" => "Հայերեն"
            ],
            [
                "value" => "be",
                "label" => "Server language Belarusian",
                "nativeName" => "Беларуская"
            ],
            [
                "value" => "bs",
                "label" => "Server language Bosnian",
                "nativeName" => "bosanski jezik"
            ],
            [
                "value" => "bg",
                "label" => "Server language Bulgarian",
                "nativeName" => "български език"
            ],
            [
                "value" => "ca",
                "label" => "Server language Catalan; Valencian",
                "nativeName" => "Català"
            ],
            [
                "value" => "zh",
                "label" => "Server language Chinese",
                "nativeName" => "中文 (Zhōngwén), 汉语, 漢語"
            ],
            [
                "value" => "hr",
                "label" => "Server language Croatian",
                "nativeName" => "hrvatski"
            ],
            [
                "value" => "cs",
                "label" => "Server language Czech",
                "nativeName" => "česky, čeština"
            ],
            [
                "value" => "da",
                "label" => "Server language Danish",
                "nativeName" => "dansk"
            ],
            [
                "value" => "nl",
                "label" => "Server language Dutch",
                "nativeName" => "Nederlands, Vlaams"
            ],
            [
                "value" => "en",
                "label" => "Server language English",
                "nativeName" => "English"
            ],
            [
                "value" => "et",
                "label" => "Server language Estonian",
                "nativeName" => "eesti, eesti keel"
            ],
            [
                "value" => "fi",
                "label" => "Server language Finnish",
                "nativeName" => "suomi, suomen kieli"
            ],
            [
                "value" => "fr",
                "label" => "Server language French",
                "nativeName" => "français, langue française"
            ],
            [
                "value" => "de",
                "label" => "Server language German",
                "nativeName" => "Deutsch"
            ],
            [
                "value" => "el",
                "label" => "Server language Greek, Modern",
                "nativeName" => "Ελληνικά"
            ],
            [
                "value" => "he",
                "label" => "Server language Hebrew (modern)",
                "nativeName" => "עברית"
            ],
            [
                "value" => "hi",
                "label" => "Server language Hindi",
                "nativeName" => "हिन्दी, हिंदी"
            ],
            [
                "value" => "hu",
                "label" => "Server language Hungarian",
                "nativeName" => "Magyar"
            ],
            [
                "value" => "id",
                "label" => "Server language Indonesian",
                "nativeName" => "Bahasa Indonesia"
            ],
            [
                "value" => "ga",
                "label" => "Server language Irish",
                "nativeName" => "Gaeilge"
            ],
            [
                "value" => "is",
                "label" => "Server language Icelandic",
                "nativeName" => "Íslenska"
            ],
            [
                "value" => "it",
                "label" => "Server language Italian",
                "nativeName" => "Italiano"
            ],
            [
                "value" => "ja",
                "label" => "Server language Japanese",
                "nativeName" => "日本語 (にほんご／にっぽんご)"
            ],
            [
                "value" => "ko",
                "label" => "Server language Korean",
                "nativeName" => "한국어 (韓國語), 조선말 (朝鮮語)"
            ],
            [
                "value" => "lb",
                "label" => "Server language Luxembourgish, Letzeburgesch",
                "nativeName" => "Lëtzebuergesch"
            ],
            [
                "value" => "lt",
                "label" => "Server language Lithuanian",
                "nativeName" => "lietuvių kalba"
            ],
            [
                "value" => "lv",
                "label" => "Server language Latvian",
                "nativeName" => "latviešu valoda"
            ],
            [
                "value" => "mk",
                "label" => "Server language Macedonian",
                "nativeName" => "македонски јазик"
            ]
        ];
        return response()->json($array);
    }
}
