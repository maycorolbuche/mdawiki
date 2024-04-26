<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    /**
     * Display login page.
     * 
     * @return Renderable
     */
    public function index(Request $request)
    {
        $request->session()->put('previous_url', url()->previous());
        return view('auth.login');
    }

    /**
     * Handle account login request
     * 
     * @param LoginRequest $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->getCredentials();
        $remember = $request->has('remember');

        $user = User::where('email', $credentials['email'])->first();

        if ($user) {
            if (empty($user->password)) {
                return redirect()
                    ->route('password.request')
                    ->with('email', $user->email)
                    ->withErrors('Você precisa cadastrar uma nova senha de acesso! Siga as instruções abaixo para criar sua senha.');
            }
        }

        if (!Auth::attempt($credentials, $remember)) {
            return redirect()
                ->route('login')
                ->withInput()
                ->withErrors(trans('auth.failed'));
        }

        $user = Auth::user();
        if (!$user->change_data_at) {
            return redirect()->route('settings.account')->with('warning', 'Você está conectado! Aproveite para completar seus dados.');
        } else {
            return redirect()->to($request->session()->pull('previous_url', '/'));
            //return $this->authenticated($request, $user);
        }
    }

    /**
     * Handle response after user authenticated
     * 
     * @param Request $request
     * @param Auth $user
     * 
     * @return \Illuminate\Http\Response
     */
    //protected function authenticated(Request $request, $user)
    //{
    //    return redirect()->intended();
    //}
}
