<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerificationCodeEmail;
use App\Helpers\CryptHelper;

class RegisterController extends Controller
{
    public function index()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        $verificationCode = random_int(100000, 999999);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'verification_code' => $verificationCode,
            'verification_code_generated_at' => now()
        ]);

        $this->sendVerificationCodeEmail($user->id);

        return redirect()->route('validate', ['id_user_hash' => CryptHelper::encryptNumber($user->id)]);
    }

    public function sendVerificationCodeEmail($id_user)
    {
        $user = User::find($id_user);
        Notification::send($user, new VerificationCodeEmail());
    }
}
