<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PasswordChange;
use App\Http\Requests\PasswordChangeRequest;

class PasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('settings.password');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PasswordChangeRequest $request)
    {
        $user = auth()->user();

        if (Hash::check($request->current_password, $user->password)) {
            $user->forceFill([
                'password' => $request->password,
                'remember_token' => Str::random(60),
            ])->save();

            Notification::send($user, new PasswordChange());

            return redirect()->route('settings.password')->with('success', 'Senha alterada com sucesso!');
        } else {
            return back()->withErrors(['current_password' => 'A senha atual não corresponde.'])->withInput();
        }
    }
}
