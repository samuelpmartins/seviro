<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FirstAccessController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        if (!auth()->user()->first_access) {
            return redirect()->route('store.manage');
        }

        return view('auth.first-access');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
            'first_access' => false,
        ]);

        return redirect()->route('store.manage')
            ->with('success', 'Senha redefinida com sucesso.');
    }
}
