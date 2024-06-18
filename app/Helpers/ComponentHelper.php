<?php

namespace App\Helpers;

use Drnxloc\LaravelHtmlDom\HtmlDomParser;

class ComponentHelper
{
    public static function list()
    {
        $list = [];

        $list[] = self::PathfinderHonor();
        $list[] = self::AdventurerHonor();

        return $list;
    }
    public static function component($name, $title, $html)
    {
        return "<div class='custom-component' data-component-name='$name'>"
            . "<span class='title-component' contenteditable='false'>$title</span>"
            . $html
            . "</div>";
    }
    public static function refresh($name, $html)
    {
        if ($name == '') {
            return '';
        }

        if (!method_exists(self::class, $name)) {
            return '';
        }

        $data = self::getParams($html);
        $component = self::$name();
        $html = $component["component"];
        $html = self::setParams($html, $data);
        return $html;
    }
    public static function componentToPreview($name, $html)
    {
        if ($name == '') {
            return '';
        }

        if (!method_exists(self::class, $name)) {
            return '';
        }

        $data = self::getParams($html);
        $component = self::$name();
        $html = $component["preview"];
        foreach ($data as $key => $param) {
            $html = str_replace("{" . $key . "}", $param, $html);
        }

        $html = self::setParams($html, $data);
        return $html;
    }


    public static function getParams($html)
    {
        $data = [];

        $dom = HtmlDomParser::str_get_html($html);
        foreach ($dom->find("[data-name]") as $el) {
            $attr = $el->getAttribute('data-name');
            $val = $el->getAttribute('value') ?: '';
            $data[$attr] = $val;
        }
        $dom->save();
        $dom->clear();

        return $data;
    }

    public static function setParams($html, $data)
    {
        $dom = HtmlDomParser::str_get_html($html);
        foreach ($dom->find("[data-name]") as $el) {
            $attr = $el->getAttribute('data-name');
            $el->setAttribute('value', $data[$attr] ?: '');
        }
        $html = $dom->save();
        $dom->clear();

        return $html;
    }

    public static function input($name, $label)
    {
        return "<span  contenteditable='false'>$label:</span> "
            . "<input type='text' data-name='$name'>";
    }

    public static function PathfinderHonor()
    {
        $name = 'PathfinderHonor';
        $title = 'Especialidade de Desbravadores';

        $component = '';
        $component .= '!!!!!!!!!!!!!!!!!!!!!!!';
        $component .= self::input('code', 'Código da Especialidade');
        $component .= self::input('title', 'Título da Especialidade');

        $preview = '';
        $preview .= '<b>!!{code}!! TITLE = {title}';

        return [
            'name' => $name,
            'title' => $title,
            'component' => self::component($name, $title, $component),
            'preview' => $preview,
        ];
    }
    public static function AdventurerHonor()
    {
        $name = 'AdventurerHonor';
        $title = 'Especialidade de Aventureiros';

        $component = '';
        $component .= '<b>Aventureiros</b><i>fff</i>';

        $preview = '';
        $preview .= '....';

        return [
            'name' => $name,
            'title' => $title,
            'component' => self::component($name, $title, $component),
            'preview' => $preview,
        ];
    }
}
