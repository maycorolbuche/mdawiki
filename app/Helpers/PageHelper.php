<?php
//https://simplehtmldom.sourceforge.io/docs/1.9/index.html

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
        $dom = HtmlDomParser::str_get_html($content);
        foreach ($dom->find("li > p") as $el) {
            $el->parent()->innertext = StringHelper::removeTag($el->parent()->innertext, 'p');
        }
        $content = $dom->save();
        $dom->clear();


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
                            $text .= PHP_EOL . str_repeat(' ', $level - 1) . $number . '. ' . $node->innertext;
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
        $content = nodeToText($dom->root->children);
        $dom->clear();

        $content = str_replace('&nbsp;', ' ', $content);

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
