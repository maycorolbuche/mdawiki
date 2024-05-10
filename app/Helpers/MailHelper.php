<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Notification;
use App\Models\TaskLog;
use App\Models\User;
use App\Models\ImageHistory;
use App\Models\EmailQueue;
use App\Notifications\HtmlEmail;
use Carbon\Carbon;

class MailHelper
{
    public static function processQueue()
    {
        $log = new TaskLog();
        $log->task_name = 'SendMail';
        $log->start_time = now();
        $log->save();

        $email_queue = EmailQueue::where('status', 'pending')->get();
        foreach ($email_queue as $queue) {
            $user = User::find($queue->user_id);
            if ($user) {
                $queue->recipient_email = $user->email;
                $queue->save();

                $user->mail = $queue;
                try {
                    Notification::send($user, new HtmlEmail($queue));
                    $queue->status = 'sent';
                } catch (\Exception $e) {
                    $queue->status = 'failed';
                    $queue->error_message = $e->getMessage();
                }

                $queue->scheduled_at = now();
                $queue->save();
            }
        }

        $log->end_time = now();
        $log->save();
    }

    public static function addToQueue(string $type, string $subject, string $content)
    {
        $users = User::where($type, true)->pluck('id');
        foreach ($users as $user_id) {
            EmailQueue::create([
                'user_id' => $user_id,
                'subject' => $subject,
                'content' => $content,
            ]);
        }
    }

    public static function userData(int $user_id)
    {
        $html = '';

        $user = User::find($user_id);
        if ($user) {
            $age = Carbon::parse($user->birth_date)->age;
            $date = Carbon::parse($user->birth_date);
            $birth_date = $date->format('d/m/Y');

            $image = '';
            if ($user->photo) {
                $path_avatar = storage_path('app/public/' . $user->photo);
                $type_avatar = pathinfo($path_avatar, PATHINFO_EXTENSION);
                $data_avatar = file_get_contents($path_avatar);
                $base64_avatar = 'data:image/' . $type_avatar . ';base64,' . base64_encode($data_avatar);
                $image = '<img src="' . $base64_avatar . '">';
            }

            $html .= '<table style="width: 100%">';
            $html .= '<tr>';
            $html .= '<td>';
            $html .= '<b>Nome:</b> ' . $user->name;
            $html .= '<br><b>E-mail:</b> ' . $user->email;
            $html .= '<br><b>Apelido:</b> ' . $user->nickname;
            $html .= '<br><b>Idade:</b> ' . $age . ($user->birth_date ? ' (' . $birth_date . ')' : '');
            $html .= '<br><b>Cidade:</b> ' . $user->city . '-' . $user->state;
            $html .= '<br><b>Campo:</b> ' . $user->association;
            $html .= '<br><b>Desbravador?</b> ' . ($user->is_pathfinder ? 'Sim' : 'Não') . ' | ' . $user->pathfinder_club;
            $html .= '<br><b>Aventureiro?</b> ' . ($user->is_adventurer ? 'Sim' : 'Não') . ' | ' . $user->adventurer_club;
            $html .= '</td>';
            $html .= '<td style="text-align: right;">';
            $html .= $image;
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td colspan=2>';
            $html .= '<b>Biografia:</b><br>';
            $html .= nl2br($user->biography);
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</table>';
        }

        return $html;
    }

    public static function imageData($page)
    {
        $html = '';

        $image = ImageHistory::where('slug', $page)->orderBy('id', 'desc')->first();
        if ($image) {
            $url = route('image', ['page' => $image->name]);

            if ($image->path) {
                $base64 = 'data:' . $image->mime . ';base64,' . base64_encode(file_get_contents(storage_path('app/public/' . $image->path)));
                $img = '<img src="' . $base64 . '" style="max-width:150px;">';
            } else {
                $img = '<div style="float:right;width:150px;height:150px;background:red;color:#FFF;font-weight:bold;display: flex;align-items: center;justify-content: center;">Imagem apagada</div>';
            }


            $html .= '<table style="width: 100%">';
            $html .= '<tr>';
            $html .= '<td>';
            $html .= '<b>Imagem:</b> ' . $image->title;
            $html .= '<br><b>URL:</b> <a href="' . $url . '" target="_blank">' . $url . '</a>';
            $html .= '<br><b>Width:</b> ' . $image->width;
            $html .= '<br><b>Height:</b> ' . $image->height;
            $html .= '<br><b>Tamanho:</b> ' . $image->size;
            $html .= '<br><b>ID Anterior:</b> ' . $image->restore_id;
            $html .= '</td>';
            $html .= '<td style="text-align: right;">';
            $html .= $img;
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '</table>';

            $html .= '<p><h2>Usuário Responsável</h2>';
            $html .= self::userData($image->updated_by);
        }

        return $html;
    }

    public static function pageData($page)
    {
        $html = '';

        return $html;
    }
}
