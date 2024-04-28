<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class ImageHelper
{
    public static function saveBase64Image($base64Image, $directory = 'images')
    {
        if (!$base64Image) {
            return null;
        }

        if (substr($base64Image, 0, 5) <> 'data:') {
            return $base64Image;
        }

        preg_match("/^data:image\/(.*);base64/i", $base64Image, $matches);
        $imageType = $matches[1];

        $imageName = uniqid() . '.' . $imageType;

        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));

        $path = $directory . '/' . $imageName;
        $return = Storage::disk('public')->put($path, $imageData);

        if ($return) {
            return $path;
        }

        return null;
    }

    public static function delete($path)
    {
        $path = 'public/' . $path;
        if (Storage::exists($path)) {
            Storage::delete($path);
            return true;
        } else {
            return false;
        }
    }
}
