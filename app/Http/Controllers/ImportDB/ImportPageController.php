<?php

namespace App\Http\Controllers\ImportDB;

use Drnxloc\LaravelHtmlDom\HtmlDomParser;
use App\Http\Controllers\PageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ComponentHelper;
use App\Helpers\StringHelper;

class ImportPageController extends PageController
{

    protected $db2 = 'mysql2';

    public function __construct()
    {
        $connection = [
            'driver' => 'mysql',
            'host' => env('DB_HOST_OLD', '127.0.0.1'),
            'port' => env('DB_PORT_OLD', '3306'),
            'database' => env('DB_DATABASE_OLD', ''),
            'username' => env('DB_USERNAME_OLD', ''),
            'password' => env('DB_PASSWORD_OLD', ''),
            'unix_socket' => env('DB_SOCKET_OLD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];

        config(['database.connections.' . $this->db2 => $connection]);
        DB::purge($this->db2);
    }

    public function run(Request $request)
    {
        $where = '';
        if (request()->query('like')) {
            $where .= " AND NOME_URL LIKE '" . request()->query('like') . "'";
        }
        $result = DB::connection($this->db2)->select("SELECT * FROM paginas WHERE NOME_URL NOT LIKE '%@%' $where");
        foreach ($result as $data) {
            $page = $data->NOME;

            $content = self::adjust($data->CONTEUDO_CODE);
            $categories = self::getCategories($data->CONTEUDO_CODE);

            $this->save($page, [
                'path' => $page,
                'content_editor' => $content,
                'categories' => $categories,
            ]);
        }

        return "Importação realizada com sucesso!";
    }

    public function adjust($content)
    {
        preg_match_all('/\{\{\{Redirecionar:(.*?)\}\}\}/i', $content, $matches);
        if ($matches[1]) {
            $page_redirect = StringHelper::toTitle($matches[1][0]);
            $content = "<input data-name='page' value='$page_redirect' >";
            $content = ComponentHelper::refresh('Redirect', $content);
        }

        $content = preg_replace('/=======([^=]+)=======/', '<h6>$1</h6>', $content);
        $content = preg_replace('/======([^=]+)======/', '<h5>$1</h5>', $content);
        $content = preg_replace('/=====([^=]+)=====/', '<h4>$1</h4>', $content);
        $content = preg_replace('/====([^=]+)====/', '<h3>$1</h3>', $content);
        $content = preg_replace('/===([^=]+)===/', '<h2>$1</h2>', $content);
        $content = preg_replace('/==([^=]+)==/', '<h1>$1</h1>', $content);

        $content = preg_replace('/\{\{Categorias:.*?\}\}/is', '', $content);
        $content = preg_replace('/\{\{Categoria:.*?\}\}/is', '', $content);

        $content = preg_replace('/\[\[([^\|\]]+)\|([^\]]+)\]\]/i', '<a href="$1">$2</a>', $content);
        $content = preg_replace('/\[\[([^\|\]]+)\]\]/i', '<a href="$1">$1</a>', $content);

        $content = preg_replace('/\'\'\'([^\']+)\'\'\'/', '<b>$1</b>', $content);
        $content = preg_replace('/\'\'([^\']+)\'\'/', '<i>$1</i>', $content);

        $content = str_replace('-//-', '<br>', $content);


        preg_match_all('/\{\{Imagem:(.*?)\}\}/is', $content, $matches);
        if ($matches[1]) {
            foreach ($matches[1] as $key => $value) {
                $component = $matches[0][$key];
                $data = self::toArray($component);
                $class = '';
                if (@$data['alinhamento'] == 'right') {
                    $class .= ' fr-fir ';
                } elseif (@$data['alinhamento'] == 'left') {
                    $class .= ' fr-fil ';
                }
                $html = '';
                $html .= '<span class="fr-img-caption fr-fic fr-dii ' . $class . ' fr-draggable" style="" contenteditable="false" draggable="false">';
                $html .= '<span class="fr-img-wrap">';
                $html .= '<img data-slug="' . StringHelper::toSlug($data["imagem"]) . '" alt="' . StringHelper::toTitle($data["imagem"]) . '" style="max-width: 400px;" class="fr-fic fr-dii fr-draggable" data-exists="" data-name="' . StringHelper::toTitle($data["imagem"]) . '">';
                $html .= '<span class="fr-inner" contenteditable="true">' . @$data["legenda"] . '</span>';
                $html .= '</span>';
                $html .= '</span>';

                $content = str_ireplace($component, $html, $content);
            }
        }

        preg_match_all('/\{\{Biografia:(.*?)\}\}/is', $content, $matches);
        if ($matches[1]) {
            foreach ($matches[1] as $key => $value) {
                $component = $matches[0][$key];
                $component_r = $component;
                $component_r = str_ireplace('"', "'", $component);
                $component_r = preg_replace('/<a\s+[^>]*href=\'([^\']*)\'[^>]*>(.*?)<\/a>/i', '[$1]', $component_r);
                $data = self::toArray($component_r);

                $page_redirect = StringHelper::toTitle($matches[1][0]);
                $component_r = ComponentHelper::People();
                $html = $component_r["component"];
                $data["image"] = StringHelper::toSlug(@$data["foto"]);
                $data["history"] = $data["list"] ?? [];
                $data = array_merge($data, self::toDateArray("birth_date", @$data["nascimento"]));
                $data = array_merge($data, self::toDateArray("death_date", @$data["morte"]));
                foreach ($data["history"] as $key => $item) {
                    $data["history"][$key]["position"] = @$item["cargo"];
                    $data["history"][$key]["predecessor"] = @$item["antecessor"];
                    $data["history"][$key]["successor"] = @$item["sucessor"];

                    $data["history"][$key] = array_merge($data["history"][$key], self::toDateArray("start_date,end_date", @$data["history"][$key]["mandato"]));
                }
                $html = ComponentHelper::setParams($html, $data);
                $html = ComponentHelper::refresh('People', $html);

                $content = str_ireplace($component, $html, $content);
            }
        }

        preg_match_all('/\{\{Mais Links:(.*?)\}\}/is', $content, $matches);
        if ($matches[1]) {
            foreach ($matches[1] as $key => $value) {

                //IF DESAMBIGUACAO ---  implantar.....

                $component = $matches[0][$key];
                $component_r = $component;
                $component_r = str_ireplace('"', "'", $component);
                $component_r = preg_replace('/<a\s+[^>]*href=\'([^\']*)\'[^>]*>(.*?)<\/a>/i', '$1~$2', $component_r);
                $data = self::toArray($component_r);
                $e = explode("~", $data["links"]);
                $data["page"] = $e[0];
                $data["title"] = $e[1];

                $component_r = ComponentHelper::SimilarPage();
                $html = $component_r["component"];
                $html = ComponentHelper::setParams($html, $data);
                $html = ComponentHelper::refresh('SimilarPage', $html);

                $content = str_ireplace($component, $html, $content);
            }
        }


        preg_match_all('/\{\{Imagens\}\}(.*?)\{\{\/Imagens\}\}/is', $content, $matches);
        if ($matches[1]) {
            foreach ($matches[1] as $key => $value) {
                $component = $matches[0][$key];

                $html = '';
                $html .= '<table style="width: 100%;" class="dynamic">';
                $html .= '<tbody>';

                $col = 0;
                $dom = HtmlDomParser::str_get_html($matches[1][$key]);
                foreach ($dom->find(".fr-img-caption") as $el) {
                    $col++;

                    $caption = $el->find('.fr-inner')[0]->innertext;
                    $el->find('.fr-inner')[0]->innertext = '';
                    $element = $el->find('img')[0]->outertext;
                    $href = $el->find('a')[0]->getAttribute("href");

                    if ($col == 1) {
                        $html .= '<tr>';
                    }


                    $element = str_replace('max-width: 400px;', 'width:100px;max-width: 400px;', $element);
                    $html .= '<td style="width: 25%;">';
                    $html .= '<div style="text-align: center;">';
                    $html .= '<a href="' . $href . '">';
                    $html .= $element;
                    $html .= '</a>';
                    $html .= '</div>';
                    $html .= '<div style="text-align: center;">';
                    $html .=  $caption;
                    $html .= '</div>';
                    $html .= '</td>';

                    if ($col == 4) {
                        $html .= '</tr>';
                        $col = 0;
                    }
                }
                if ($col > 0) {
                    for ($c = 4; $c > $col; $c--) {
                        $html .= '<td></td>';
                    }
                    $html .= '</tr>';
                }
                $dom->clear();

                $html .= '</tbody>';
                $html .= '</table>';

                $content = str_ireplace($component, $html, $content);
            }
            // dd($matches[1]);
        }

        $content = preg_replace('/\r\n|\r|\n/', PHP_EOL, $content);
        $lines = explode(PHP_EOL, $content);
        foreach ($lines as $key => $line) {
            if (trim($line) <> '') {
                $dom = HtmlDomParser::str_get_html($line);
                if (!$dom->find("h1,h2,h3,h4,h5,h6,.custom-component")) {
                    $lines[$key] = '<p style="text-align: justify;">' . $line . '</p>';
                }
                $dom->clear();
            } else {
                $lines[$key] = '';
            }
        }

        $content = implode(PHP_EOL, $lines);
        //dd($content);

        return $content;
    }

    public function getCategories($content)
    {
        $content = str_ireplace('{{Categoria:', '{{Categorias:', $content);

        preg_match_all('/\{\{Categorias:(.*?)\}\}/i', $content, $matches);
        if ($matches[1]) {
            return array_values($matches[1]);
        }

        return [];
    }

    function toArray($string)
    {
        $string = str_ireplace(PHP_EOL, '', $string);
        $string = trim($string, '{}');
        $string = str_ireplace(':Foto', ':|Foto', $string);
        $string = str_ireplace(':', '=', $string);

        $parts = explode('|', $string);

        $sub = false;
        $indx = -1;

        foreach ($parts as $part) {
            $e = explode('=', $part);
            $key = $e[0];
            array_shift($e);
            $value = implode("=", $e);
            $key = trim(strtolower($key));
            $value = trim($value);
            if ($key == "mandato") {
                $sub = true;
                $indx++;
            }
            if (!$sub) {
                $array[$key] = $value;
            } else {
                if (!isset($array["list"])) {
                    $array["list"] = [];
                }
                $array["list"][$indx][$key] = $value;
            }
        }

        return $array;
    }

    function toDateArray($key, $text)
    {
        $kpart = explode(',', $key);
        if (count($kpart) > 1) {
            $data = [];
            $tpart = explode('-', $text);
            foreach ($kpart as $indx => $key) {
                $data = array_merge($data, self::toDateArray($key, $tpart[$indx] ?? ''));
            }
            return $data;
        }
        $data = [];

        $parts = explode(' ', $text);
        foreach ($parts as $part) {
            if (ctype_digit($part)) {
                $number = intval($part);
                if ($number >= 1 && $number <= 31) {
                    $data[$key . '_day'] = $part;
                } elseif ($number > 1000) {
                    $data[$key . '_year'] = $part;
                }
            } else {
                $month =  [
                    'janeiro' => 1, 'fevereiro' => 2, 'março' => 3, 'abril' => 4,
                    'maio' => 5, 'junho' => 6, 'julho' => 7, 'agosto' => 8,
                    'setembro' => 9, 'outubro' => 10, 'novembro' => 11, 'dezembro' => 12
                ];;
                $lpart = mb_strtolower($part, 'UTF-8');
                if (array_key_exists($lpart, $month)) {
                    $data[$key . '_month'] = $month[$lpart];
                }
            }
        }
        return $data;
    }
}
