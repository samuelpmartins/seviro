<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserAdmin;
use Illuminate\Support\Facades\Hash;

class FirstAccessController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    public function show()
    {
        /** @var UserAdmin $admin */
        $admin = Auth::guard('admin')->user();

        if (! $admin->first_access) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.first-access');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = Auth::guard('admin')->user();

        if (! $admin instanceof UserAdmin) {
            abort(403);
        }

        if (! $admin->first_access) {
            return redirect()->route('admin.dashboard');
        }

        $admin->password = Hash::make($request->password);
        $admin->first_access = false;
        $admin->save();

        return redirect()->route('admin.dashboard')->with('success', 'Senha redefinida com sucesso.');
    }
}
