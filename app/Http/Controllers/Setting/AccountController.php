<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountRequest;
use App\Helpers\ImageHelper;

class AccountController extends Controller
{

    protected $fillable = [
        'name',
        'nickname',
        'photo',
        'birth_date',
        'biography',
        'city',
        'state',
        'association',
        'is_pathfinder',
        'pathfinder_club',
        'is_adventurer',
        'adventurer_club',
        'change_data_at',
        'last_login_at',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        return view('settings.account', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountRequest $request)
    {
        $user = auth()->user();
        $data = $request->only($this->fillable);

        $data["photo"] = ImageHelper::saveBase64Image($data["photo"]);
        if ($user->photo && $user->photo <> $data["photo"]) {
            ImageHelper::delete($user->photo);
        }

        $user->update($data);

        return redirect()->route('settings.account')->with('success', 'Dados salvos com sucesso');
    }
}
