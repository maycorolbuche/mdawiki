<?php

namespace App\Helpers;

use Drnxloc\LaravelHtmlDom\HtmlDomParser;

class ComponentHelper
{
    public static function list()
    {
        return [
            self::PathfinderHonor(),
            self::AdventurerHonor(),
            self::People(),
            self::Redirect(),
        ];
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
            if (is_array($param)) {

                $dom = HtmlDomParser::str_get_html($html);
                $el = $dom->find("[data-list='" . $key . "']");
                if ($el) {
                    $el = $el[0];
                    $template = $el->outertext;
                    $el->outertext = '';

                    foreach ($param as $gkey => $gparam) {
                        $shtml = $template;
                        $text = "";

                        $arr = array_filter(array_fill_keys(array_keys($output), ''), function ($key) {
                            return strpos($key, 'history.') === 0;
                        }, ARRAY_FILTER_USE_KEY);
                        $arr_m = [];
                        foreach ($arr as $akey => $value) {
                            $e = explode(".", $akey);
                            $akey = $e[1];
                            $arr_m[] = $akey;
                        }
                        $arr_m = array_fill_keys($arr_m, '');
                        $gparam = array_merge($arr_m, $gparam);
                        foreach ($gparam as $skey => $sparam) {
                            $text .= $sparam;
                            $shtml = self::replaceParam($skey, $sparam, $shtml, $data[$key][$gkey], $output["$key.$skey"] ?? null);
                        }
                        if ($text <> "") {
                            $el->outertext .= $shtml;
                        }
                    }
                    $html = $dom->save();
                }
                $dom->clear();
            } else {
                $html = self::replaceParam($key, $param, $html, $data, $output[$key] ?? null);
            }
        }

        $html = self::setParams($html, $data);
        return $html;
    }

    public static function replaceParam($key, $param, $html, $data = [], $output = null)
    {
        if ($output && $output <> '') {
            $expression = $output;
            $expression = str_replace("{}", $param, $expression);
            $param = eval("return $expression;");
        }

        $param = preg_replace_callback('/\[(.*?)\]/', function ($matches) {
            return "<a href='{$matches[1]}'>{$matches[1]}</a>";
        }, $param);

        $html = str_replace("{" . $key . "}", $param, $html);
        if ($param == "") {
            $html = str_replace("{" . $key . ":visible}", "display:none;", $html);
        } else {
            $html = str_replace("{" . $key . ":visible}", "", $html);
        }
        return $html;
    }

    public static function getParams($html)
    {
        $data = [];

        $dom = HtmlDomParser::str_get_html($html);
        foreach ($dom->find("[data-group-name]") as $el_group) {
            $name = $el_group->getAttribute('data-group-name');
            foreach ($el_group->children() as $el) {
                $val = self::getParams($el->innertext);
                if (!isset($data[$name])) {
                    $data[$name] = [];
                }
                $data[$name][] = $val;
            }
            $el_group->outertext = '';
        }
        $html = $dom->save();
        $dom->clear();

        $dom = HtmlDomParser::str_get_html($html);
        foreach ($dom->find("[data-name]") as $el) {
            $attr = $el->getAttribute('data-name');
            $val = "";
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
        foreach ($dom->find("[data-group-name]") as $el_group) {
            $name = $el_group->getAttribute('data-group-name');
            $template = $el_group->innertext;
            $count = count($data[$name] ?? []);

            if ($count > 0) {
                $el_group->innertext = '';

                foreach ($data[$name] as $item) {
                    $el_group->innertext .= self::setParams($template, $item);
                }
            }
        }
        $html = $dom->save();
        $dom->clear();


        $dom = HtmlDomParser::str_get_html($html);
        foreach ($dom->find("[data-name]") as $el) {
            $parent = $el;
            while ($parent && !$parent->hasClass("input-list-component-multi")) {
                $parent = $parent->parent();
            }
            if ($parent) {
                continue;
            }

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

    public static function inputGroup($start = true)
    {
        if ($start) {
            return '<div class="input-list-component">';
        } else {
            return '</div>';
        }
    }
    public static function inputList($start = true, $name = "")
    {
        if ($start) {
            return '<div data-group-name="' . $name . '" class="input-list-component-multi">'
                . '<div class="input-list-component-multi-element input-list-component">';
        } else {
            return '</div>'
                . '</div>';
        }
    }
    public static function input($name, $label, $type = 'text', $data = [])
    {
        $separator = "<div style='border-left: 1px solid #c2e3c2;width: 1px;'></div>";
        $input = "";
        if ($type == "dmy") {
            $input .= self::inputField($name . "_day", "day", $data);
            $input .= $separator;
            $input .= self::inputField($name . "_month", "month", $data);
            $input .= $separator;
            $input .= self::inputField($name . "_year", "year", $data);
        } else {
            $input .= self::inputField($name, $type, $data);
        }

        return "<fieldset>"
            . "<span class='label-component' contenteditable='false'>$label:</span> "
            . $input
            . "</fieldset>";
    }
    public static function inputField($name, $type = 'text', $data = [])
    {
        $params = "";
        $placeholder = "Digite...";
        if ($type == "select") {
            $placeholder = "Selecione...";
        }

        if ($type == "year") {
            $type = "number";
            $params = " min=1900 max=" . date('Y') . " ";
            $placeholder = "Ano";
        } elseif ($type == "month") {
            $type = "select";
            $data = [
                1 => "Janeiro",
                2 => "Fevereiro",
                3 => "Março",
                4 => "Abril",
                5 => "Maio",
                6 => "Junho",
                7 => "Julho",
                8 => "Agosto",
                9 => "Setembro",
                10 => "Outubro",
                11 => "Novembro",
                12 => "Dezembro",
            ];
            $placeholder = "Mês";
        } elseif ($type == "day") {
            $type = "number";
            $params = " min=1 max=31 ";
            $placeholder = "Dia";
        }

        $input = "";
        if ($type == "select") {
            $input = "<select class='input-component' data-name='$name' $params>";
            $input .= "<option value=''>$placeholder</option>";
            foreach ($data as $key => $value) {
                $input .= "<option value='$key'>$value</option>";
            }
            $input .= "</select>";
        } else {
            $input = "<input class='input-component' type='$type' data-name='$name' placeholder='$placeholder' $params>";
        }
        return $input;
    }
    public static function outputDate($key, $data)
    {
        $day = "0" . ($data[$key . "_day"] ?? "");
        $month = "0" . ($data[$key . "_month"] ?? "");
        $year = "0" . ($data[$key . "_year"] ?? "");

        $day = +preg_replace('/\D/', '', $day);
        $month = +preg_replace('/\D/', '', $month);
        $month_desc = [
            1 => "Janeiro",
            2 => "Fevereiro",
            3 => "Março",
            4 => "Abril",
            5 => "Maio",
            6 => "Junho",
            7 => "Julho",
            8 => "Agosto",
            9 => "Setembro",
            10 => "Outubro",
            11 => "Novembro",
            12 => "Dezembro",
        ][+$month] ?? "";
        $year = +preg_replace('/\D/', '', $year);

        $date = ($day == 0 ? "" : $day)
            . "|" . $month_desc
            . "|" . ($year == 0 ? "" : $year);

        $date = preg_replace('/\|+/', '|', $date);
        $date = trim($date, '|');
        $date = str_replace("|", " de ", $date);

        return $date;
    }

    /* *************************************************************** */
    public static function PathfinderHonor()
    {
        $name = 'PathfinderHonor';
        $title = 'Especialidade de Desbravadores';

        $component = '';
        $component .= self::inputGroup();
        $component .= self::input('category', 'Área', 'select', self::PathfinderHonorCategories());
        $component .= self::input('code', 'Código', 'number');
        $component .= self::input('level', 'Nível', 'select', [1 => '1', 2 => '2', 3 => '3']);
        $component .= self::input('year', 'Ano', 'year');
        $component .= self::input('origin', 'Instituição de Origem');
        $component .= self::input('image', 'Imagem');
        $component .= self::inputGroup(false);

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
        $component .= self::inputGroup();
        $component .= self::input('category', 'Área', 'select', self::AdventurerHonorCategories());
        $component .= self::input('code', 'Código', 'number');
        $component .= self::input('image', 'Imagem');
        $component .= self::inputGroup(false);

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
    public static function People()
    {
        $name = 'People';
        $title = 'Pessoa';

        $component = '';
        $component .= self::inputGroup();
        $component .= self::input('image', 'Foto');
        $component .= self::input('birth_date', 'Data de Nascimento', 'dmy');
        $component .= self::input('death_date', 'Data da Morte', 'dmy');
        $component .= self::inputGroup(false);

        $component .= self::inputList(true, "history");
        $component .= self::input('position', 'Cargo');
        $component .= self::input('start_date', 'Data Inicial', 'dmy');
        $component .= self::input('end_date', 'Data Final', 'dmy');
        $component .= self::inputList(false);

        $preview = '';
        $preview .= '<div class="template-people">';
        $preview .= '<img data-slug="{image}" style="width: 100%;">';

        $preview .= '<div style="display:none" data-event data-start-day="{birth_date_day}" data-start-month="{birth_date_month}" data-start-year="{birth_date_year}" data-end-day="{death_date_day}" data-end-month="{death_date_month}" data-end-year="{death_date_year}" data-event-type="lifetime"></div>';

        $preview .= '<div class="template-people-info" style="{birth_death_date:visible}">';
        $preview .= '<div style="text-align:center;{birth_date:visible}"><b>&#9733;</b> {birth_date}</div>';
        $preview .= '<div style="text-align:center;{death_date:visible}"><b>&#10013;</b> {death_date}</div>';
        $preview .= '</div>';

        $preview .= '<table>';
        $preview .= '<tbody data-list="history">';
        $preview .= '<tr style="{position:visible}">';
        $preview .= '<td class="template-people-position">Cargo:</td>';
        $preview .= '<td>{position}</td>';
        $preview .= '</tr>';
        $preview .= '<tr style="{start_end_date:visible}">';
        $preview .= '<td class="template-people-position">Mandato:</td>';
        $preview .= '<td>{start_date}<span style="{start_date:visible}{end_date:visible}">-</span>{end_date}</td>';
        $preview .= '<div style="display:none" data-event data-start-day="{start_date_day}" data-start-month="{start_date_month}" data-start-year="{start_date_year}" data-end-day="{end_date_day}" data-end-month="{end_date_month}" data-end-year="{end_date_year}" data-event-type="role" data-event-info="{position}"></div>';
        $preview .= '</tr>';
        $preview .= '</tbody>';
        $preview .= '</table>';

        $preview .= '</div>';


        return [
            'name' => $name,
            'title' => $title,
            'component' => self::component($name, $title, $component),
            'preview' => $preview,
            'output' => [
                'birth_date' => "self::outputDate('birth_date',\$data)",
                'death_date' => "self::outputDate('death_date',\$data)",
                'birth_death_date' => "self::outputDate('birth_date',\$data).self::outputDate('death_date',\$data)",
                'history.start_date' => "self::outputDate('start_date',\$data)",
                'history.end_date' => "self::outputDate('end_date',\$data)",
                'history.start_end_date' => "self::outputDate('start_date',\$data).self::outputDate('end_date',\$data)",
            ]
        ];
    }
    public static function Redirect()
    {
        $name = 'Redirect';
        $title = 'Redirecionamento';

        $component = '';
        $component .= self::inputGroup();
        $component .= self::input('page', 'Página');
        $component .= self::inputGroup(false);
        $component .= '<span>Os usuários serão redirecionados para a página informada, ao acessarem esta página.</span>';

        $preview = '';
        $preview .= '<div class="template-redirect">';
        $preview .= '<h1>Redirecionar...</h1>';
        $preview .= '<ul>';
        $preview .= '<li style="list-style-type: disc;"><a href="{page}">{page}</a></li>';
        $preview .= '</ul>';
        $preview .= '</div>';


        return [
            'name' => $name,
            'title' => $title,
            'component' => self::component($name, $title, $component),
            'preview' => $preview,
        ];
    }
    /* *************************************************************** */

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
