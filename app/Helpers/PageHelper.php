<?php

namespace App\Helpers;

use Drnxloc\LaravelHtmlDom\HtmlDomParser;
use App\Helpers\StringHelper;

class PageHelper
{
    public static function process($content)
    {
        $content = self::toEditor($content);

        $data = [
            'editor' => $content,
            'preview' => self::toPreview($content),
            'text' => self::toText($content),
            'markdown' => self::toMarkdown($content),
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
        function nodeToText($el, $level = 0, $param = [])
        {
            $p = [];
            $text = "";
            foreach ($el as $indx => $node) {
                $tag = $node->tag;
                $p['parent_tag'] = $tag;
                switch ($tag) {
                    case 'ul':
                        break;
                    case 'ol':
                        $p['start'] =  $node->attr['start'] ?? 1;
                        $p['type'] =  StringHelper::getStyle($node->attr['style'] ?? '', 'list-style-type') ?? 'decimal';
                        break;
                    case 'li':
                        if ($param['parent_tag'] == 'ul') {
                            $text .= PHP_EOL . str_repeat(' ', $level) . '- ' . $node->innertext;
                        } else {
                            $start = $param['start'] ?? 1;
                            $number = ($start + $indx);
                            if ($param['type'] == 'lower-alpha') {
                                $number = StringHelper::numberToLetter($number);
                            }
                            $text .= PHP_EOL . str_repeat(' ', $level) . $number . '. ' . $param['type'] . $node->innertext;
                        }
                        break;
                    case 'p':
                        $text .= PHP_EOL . $node->innertext;
                        break;
                    default:
                        break;
                }
                $text .= nodeToText($node->children, $level + 1, $p);
            }
            return strip_tags($text);
        }

        $dom = HtmlDomParser::str_get_html($content);
        $text = nodeToText($dom->root->children);
        $dom->clear();

        return $text;
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
