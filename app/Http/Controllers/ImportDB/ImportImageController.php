<?php

namespace App\Http\Controllers\ImportDB;

use App\Http\Controllers\ImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ImageHelper;

class ImportImageController extends ImageController
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
        $result = DB::connection($this->db2)->select("SELECT * FROM imagens WHERE IFNULL(CONTEUDO_CODE,'') <> ''");
        foreach ($result as $data) {
            $page = $data->NOME;

            $image = ImageHelper::saveBase64($data->CONTEUDO_CODE, 'images/pages');
            if (!$image) {
                echo 'Erro ao enviar esta imagem <b>' . $page . '</b><br>';
                continue;
            }

            $image_info = ImageHelper::info($image);

            $this->save($page, [
                'path' => $image,
                'width' => $image_info["width"],
                'height' => $image_info["height"],
                'mime' => $image_info["mime"],
                'size' => $image_info["size"],
            ]);
        }

        return "Importação realizada com sucesso!";
    }
}
