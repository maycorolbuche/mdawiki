<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class ImageHelper
{
    public static function saveBase64($base64Image, $directory = 'images')
    {
        if (!$base64Image) {
            return null;
        }


        if (substr($base64Image, 0, 5) <> 'data:') {
            return $base64Image;
        }

        $part = explode(";", $base64Image);
        $part = explode("/", $part[0]);
        $imageType = $part[1];

        if ($imageType == "svg" || $imageType == "svg+xml") {
            $imageType = "svg";
        }

        $imageName = uniqid() . '.' . $imageType;

        $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
        $decodedImage = base64_decode($imageData);

        $path = $directory . '/' . $imageName;
        $return = Storage::disk('public')->put($path, $decodedImage);

        if ($return) {
            return $path;
        }

        return null;
    }

    public static function info($path)
    {
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        $path = Storage::disk('public')->path($path);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension === 'svg') {
            $imageInfo = [
                'width' => null,
                'height' => null,
                'size' => filesize($path),
                'mime' => 'image/svg+xml',
            ];
        } else {

            $image = Image::make($path);

            $imageInfo = [
                'width' => $image->width(),
                'height' => $image->height(),
                'size' => $image->filesize(),
                'mime' => $image->mime(),
            ];
        }

        return $imageInfo;
    }

    public static function delete($path)
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return true;
        } else {
            return false;
        }
    }
}
