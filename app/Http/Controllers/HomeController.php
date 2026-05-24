<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();
        
        // Se o usuário for admin ou store, redireciona para o dashboard apropriado
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        
        if ($user->hasRole('store')) {
            return redirect()->route('store.manage');
        }
        
        // Busca a mesa atual do usuário no banco de dados
        $table = null;
        if (!$user->hasRole('store')) {
            $table = \App\Models\Table::where('current_user_id', $user->id)
                                    ->where('occupied', true)
                                    ->first();
        }

        // Se o usuário tem uma loja, usa ela, senão tenta pegar a loja da mesa
        $store = $user->store ?? ($table ? $table->store : null);

        return view('home', compact('user', 'table', 'store'));
    }
}
