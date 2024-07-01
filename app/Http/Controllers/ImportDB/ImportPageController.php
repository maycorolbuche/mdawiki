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

        $content = preg_replace('/\{\{Categorias:.*?\}\}/i', '', $content);
        $content = preg_replace('/\{\{Categoria:.*?\}\}/i', '', $content);

        $content = preg_replace('/\[\[([^\|\]]+)\|([^\]]+)\]\]/i', '<a href="$1">$2</a>', $content);
        $content = preg_replace('/\[\[([^\|\]]+)\]\]/i', '<a href="$1">$1</a>', $content);

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
}
