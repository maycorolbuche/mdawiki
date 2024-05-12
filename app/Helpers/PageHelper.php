<?php

namespace App\Helpers;

class PageHelper
{
    public static function process($content)
    {
        $content = self::toEditor($content);

        $data = [
            'editor' => $content,
            'preview' => self::toPreview($content),
            'text' => self::toText($content),
            'markdown' => self::toText($content),
            'pdf' => self::toPdf($content),
        ];

        return $data;
    }
    public static function toEditor($content)
    {
        return $content;
    }
    public static function toPreview($content)
    {
        return $content;
    }
    public static function toText($content)
    {
        return $content;
    }
    public static function toMarkdown($content)
    {
        return $content;
    }
    public static function toPdf($content)
    {
        return $content;
    }
}
