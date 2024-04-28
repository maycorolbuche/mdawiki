<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerificationCodeEmail;
use App\Helpers\CryptHelper;
use Carbon\Carbon;
use App\Helpers\MailHelper;

class VerificationCodeController extends Controller
{
    public function index($id_user_hash = "", $id_code_hash = "")
    {
        if ($id_code_hash <> "") {
            $id_user = CryptHelper::decryptNumber($id_user_hash);
            $user = User::find($id_user);
            if (!$user) {
                return redirect()->route('validate')->with('error', 'Usuário não encontrado!');
            }

            if ($user->email_verified_at != null) {
                return redirect()->route('validate', ['id_user_hash' => $id_user_hash])->with('error', 'Este e-mail já está validado!')->withInput();
            }

            $verification_code = CryptHelper::decryptNumber($id_code_hash);

            if ($verification_code <> $user->verification_code) {
                return redirect()->route('validate', ['id_user_hash' => $id_user_hash])->with('error', 'Este link expirou! Solicite um novo código.')->withInput();
            }

            $verification_code_generated_at = Carbon::parse($user->verification_code_generated_at);
            $minutes = $verification_code_generated_at->diffInMinutes(now());

            if ($minutes > 30) {
                return redirect()->route('validate')->with('error', 'Este link expirou! Solicite um novo código.');
            };

            $user->update([
                'verification_code' => null,
                'verification_code_generated_at' => null,
                'email_verified_at' => now(),
            ]);

            MailHelper::addToQueue('notify_active_user', '[active_user] ' . $user->name . ' | ' . $user->email, 'Usuário ativado!' . MailHelper::userData($user->id));

            return redirect()->route('login')->with('success', 'Seu e-mail foi validado com sucesso! Agora você já pode se logar em nosso site.');
        }


        return view('auth.verification_code', compact('id_user_hash'));
    }

    public function validate(Request $request, $id_user_hash = "", $id_code_hash = "")
    {
        if ($id_user_hash == "") {
            $request->validate([
                'email' => 'required|string|email|max:255',
            ]);

            $email = $request->input('email');
            $user = User::where('email', $email)->first();
            if (!$user) {
                return redirect()->back()->with('error', 'Não foi encontrado nenhum usuário com este e-mail!')->withInput();
            }

            if ($user->email_verified_at != null) {
                return redirect()->back()->with('error', 'Este e-mail já está validado!')->withInput();
            }

            $verification_code_generated_at = Carbon::parse($user->verification_code_generated_at);
            $minutes = $verification_code_generated_at->diffInMinutes(now());

            if ($user->verification_code_generated_at && $minutes <= 2) {
                return redirect()->back()->with('error', 'Foi enviado um código as ' . date("H:i", strtotime($verification_code_generated_at)) . ' e-mail. É necessário aguardar 3 minutos para enviar novamente.')->withInput();
            }

            $verificationCode = random_int(100000, 999999);

            $user->update([
                'verification_code' => $verificationCode,
                'verification_code_generated_at' => now()
            ]);

            $this->sendVerificationCodeEmail($user->id);

            return redirect()->route('validate', ['id_user_hash' => CryptHelper::encryptNumber($user->id)]);
        } else {
            $request->validate([
                'verification_code' => 'required|integer',
            ]);

            $id_user = CryptHelper::decryptNumber($id_user_hash);
            $user = User::find($id_user);
            if (!$user) {
                return redirect()->route('validate')->with('error', 'Usuário não encontrado!');
            }

            $verification_code = $request->input('verification_code');

            if ($verification_code <> $user->verification_code) {
                return redirect()->back()->with('error', 'Código incorreto!')->withInput();
            }

            $verification_code_generated_at = Carbon::parse($user->verification_code_generated_at);
            $minutes = $verification_code_generated_at->diffInMinutes(now());

            if ($minutes > 30) {
                return redirect()->route('validate')->with('error', 'Este código expirou! Solicite um novo código.');
            };

            $user->update([
                'verification_code' => null,
                'verification_code_generated_at' => null,
                'email_verified_at' => now(),
            ]);

            MailHelper::addToQueue('notify_active_user', '[active_user] ' . $user->name . ' | ' . $user->email, 'Usuário ativado!' . MailHelper::userData($user->id));

            return redirect()->route('login')->with('success', 'Seu e-mail foi validado com sucesso! Agora você já pode se logar em nosso site.');
        }
    }

    public function sendVerificationCodeEmail($id_user)
    {
        $user = User::find($id_user);
        Notification::send($user, new VerificationCodeEmail());
    }
}
