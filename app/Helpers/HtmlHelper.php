<?php

namespace App\Helpers;

class HtmlHelper
{
    public static function getStyle($styleString, $property)
    {
        $styles = explode(';', $styleString);
        foreach ($styles as $pair) {
            $parts = explode(':', $pair);

            $propertyTrimmed = trim($parts[0]);
            $valueTrimmed = trim($parts[1] ?? null);

            if ($propertyTrimmed === $property) {
                return $valueTrimmed;
            }
        }

        return null;
    }
    public static function removeTag($html, $tag)
    {
        $pattern = '%<' . $tag . '(.*?)>|</' . $tag . '>%s';
        $html_replace = preg_replace($pattern, '', $html);
        return $html_replace;
    }
}
