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
        $output = $component["output"] ?? [];

        $data = array_merge(array_fill_keys(array_keys($output), ''), $data);
        foreach ($data as $key => $param) {
            if (isset($output[$key])) {
                $expression = $output[$key];
                $expression = str_replace("{}", $param, $expression);
                $param = eval("return $expression;");
            }
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
            if ($el->tag == 'select') {
                $selectedOption = $el->find('option[selected]', 0);
                $val = $selectedOption ? $selectedOption->value : '';
            } else {
                $val = $el->getAttribute('value') ?: '';
            }
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
            $value = isset($data[$attr]) ? $data[$attr] : '';

            $el->setAttribute('value', $value);

            if ($el->tag == 'select') {
                foreach ($el->find('option') as $option) {
                    if ($option->value == $value) {
                        $option->setAttribute('selected', 'selected');
                    } else {
                        $option->removeAttribute('selected');
                    }
                }
            }
        }
        $html = $dom->save();
        $dom->clear();

        return $html;
    }

    public static function input($name, $label, $type = 'text', $data = [])
    {
        $params = "";
        if ($type == "year") {
            $type = "number";
            $params = " min=1900 max=" . date('Y') . " ";
        }

        $input = "";
        if ($type == "select") {
            $input = "<select class='input-component' data-name='$name' $params>";
            $input .= "<option value=''>Selecione...</option>";
            foreach ($data as $key => $value) {
                $input .= "<option value='$key'>$value</option>";
            }
            $input .= "</select>";
        } else {
            $input = "<input class='input-component' type='$type' data-name='$name' placeholder='Digite...' $params>";
        }

        return "<fieldset>"
            . "<span class='label-component' contenteditable='false'>$label:</span> "
            . $input
            . "</fieldset>";
    }

    public static function PathfinderHonor()
    {
        $name = 'PathfinderHonor';
        $title = 'Especialidade de Desbravadores';

        $component = '';
        $component .= '<div class="input-list-component">';
        $component .= self::input('category', 'Área', 'select', self::PathfinderHonorCategories());
        $component .= self::input('code', 'Código', 'number');
        $component .= self::input('level', 'Nível', 'select', [1 => '1', 2 => '2', 3 => '3']);
        $component .= self::input('year', 'Ano', 'year');
        $component .= self::input('origin', 'Instituição de Origem');
        $component .= self::input('image', 'Imagem');
        $component .= '</div>';

        $preview = '';
        $preview .= '<table width="100%" class="template-pathfinder-honor" data-category="{category}">';
        $preview .= '<tbody>';
        $preview .= '<tr class="bg-1" style="font-weight:bold;">';
        $preview .= '<td class="bg-2" rowspan="2" width="50"><img data-slug="D1" style="height: 75px;"></td>';
        $preview .= '<td style="text-align:center">Área</td>';
        $preview .= '<td style="text-align:center">Código</td>';
        $preview .= '<td style="text-align:center">Nível</td>';
        $preview .= '<td style="text-align:center">Ano</td>';
        $preview .= '<td style="text-align:center">Instituição de Origem</td>';
        $preview .= '<td rowspan="2" width="50"><img data-slug="{image}" style="height: 75px;"></td>';
        $preview .= '</tr>';
        $preview .= '<tr class="bg-2">';
        $preview .= '<td style="text-align:center">{category}</td>';
        $preview .= '<td style="text-align:center">{code}</td>';
        $preview .= '<td style="text-align:center">{level}</td>';
        $preview .= '<td style="text-align:center">{year}</td>';
        $preview .= '<td style="text-align:center">{origin}</td>';
        $preview .= '</tr>';
        $preview .= '</tbody>';
        $preview .= '</table>';

        return [
            'name' => $name,
            'title' => $title,
            'component' => self::component($name, $title, $component),
            'preview' => $preview,
            'output' => [
                'code' => "str_pad('{}', 3, '0', STR_PAD_LEFT)",
                'category_desc' => "self::AdventurerHonorCategories(\$data['category'])",
            ]
        ];
    }
    public static function AdventurerHonor()
    {
        $name = 'AdventurerHonor';
        $title = 'Especialidade de Aventureiros';

        $component = '';
        $component .= '<div class="input-list-component">';
        $component .= self::input('category', 'Área', 'select', self::AdventurerHonorCategories());
        $component .= self::input('code', 'Código', 'number');
        $component .= self::input('image', 'Imagem');
        $component .= '</div>';

        $preview = '';
        $preview .= '<table width="100%" class="template-adventurer-honor" data-category="{category}">';
        $preview .= '<tbody>';
        $preview .= '<tr class="bg-1" style="font-weight:bold;">';
        $preview .= '<td class="bg-2" rowspan="2" width="50"><img data-slug="A1" style="height: 75px;"></td>';
        $preview .= '<td style="text-align:center">Área</td>';
        $preview .= '<td style="text-align:center">Código</td>';
        $preview .= '<td rowspan="2" width="50"><img data-slug="{image}" style="height: 75px;"></td>';
        $preview .= '</tr>';
        $preview .= '<tr class="bg-2">';
        $preview .= '<td style="text-align:center">{category_desc}</td>';
        $preview .= '<td style="text-align:center">{code}</td>';
        $preview .= '</tr>';
        $preview .= '</tbody>';
        $preview .= '</table>';

        return [
            'name' => $name,
            'title' => $title,
            'component' => self::component($name, $title, $component),
            'preview' => $preview,
            'output' => [
                'code' => "str_pad('{}', 3, '0', STR_PAD_LEFT)",
                'category_desc' => "self::AdventurerHonorCategories(\$data['category'])",
            ]
        ];
    }


    public static function PathfinderHonorCategories($key = null)
    {
        $list = [
            'AD' => 'ADRA (AD)',
            'HM' => 'Artes e Habilidades Manuais (HM)',
            'AA' => 'Atividades Agrícolas (AA)',
            'AM' => 'Atividades Missionárias e Comunitárias (AM)',
            'AP' => 'Atividades Profissionais (AP)',
            'AR' => 'Atividades Recreativas (AR)',
            'CS' => 'Ciência e Saúde (CS)',
            'EN' => 'Estudos da Natureza (EN)',
            'HD' => 'Habilidades Domésticas (HD)',
            'ME' => 'Mestrados de Especialiades (ME)',
        ];
        if ($key) {
            return $list[$key] ?? '';
        }
        return $list;
    }

    public static function AdventurerHonorCategories($key = null)
    {
        $list = [
            'AM' => 'Artes Manuais (AM)',
            'AE' => 'Atividades Espirituais (AE)',
            'AR' => 'Atividades Recreativas (AR)',
            'EN' => 'Estudos da Natureza (EN)',
            'HD' => 'Habilidades Domésticas (HD)',
        ];
        if ($key) {
            return $list[$key] ?? '';
        }
        return $list;
    }
}
