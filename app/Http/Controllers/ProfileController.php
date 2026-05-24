<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Exibe o formulário de edição do perfil do usuário.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Atualiza as informações do perfil do usuário.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Atualiza nome e email
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Se o email foi alterado, marca como não verificado
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Se a senha atual foi fornecida, atualiza a senha
        if (isset($validated['current_password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'Perfil atualizado com sucesso!');
    }

    /**
     * Exclui a conta do usuário.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
} 