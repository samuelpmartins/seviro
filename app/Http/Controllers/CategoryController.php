<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $store = auth()->user()->store;
        $categories = $store->categories()->orderBy('order')->paginate(10);
        return view('store.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('store.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        $store = auth()->user()->store;

        $lastOrder = $store->categories()->max('order') ?? 0;

        $store->categories()->create([
            'name' => $request->name,
            'icon' => $request->icon,
            'description' => $request->description,
            'order' => $lastOrder + 1
        ]);

        // Verificar de onde veio a requisição para redirecionar corretamente
        $referer = $request->headers->get('referer');
        if (str_contains($referer, '/store/manage')) {
            return redirect()->route('store.manage')->with('success', 'Categoria criada com sucesso!');
        } elseif (str_contains($referer, '/store/dashboard')) {
            return redirect()->route('store.dashboard')->with('success', 'Categoria criada com sucesso!');
        }

        return redirect()->route('store.categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);
        return view('store.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);

        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        $category->update([
            'name' => $request->name,
            'icon' => $request->icon,
            'description' => $request->description,
        ]);

        // Verificar de onde veio a requisição para redirecionar corretamente
        $referer = $request->headers->get('referer');
        if (str_contains($referer, '/store/manage')) {
            return redirect()->route('store.manage')->with('success', 'Categoria atualizada com sucesso!');
        } elseif (str_contains($referer, '/store/dashboard')) {
            return redirect()->route('store.dashboard')->with('success', 'Categoria atualizada com sucesso!');
        }

        return redirect()->route('store.categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        $category->delete();

        return redirect()->route('store.categories.index')
            ->with('success', 'Categoria excluída com sucesso!');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => ['required', Rule::exists('categories', 'id')->whereNull('DeletionDate')],
            'categories.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->categories as $item) {
            $category = Category::find($item['id']);
            $this->authorize('update', $category);
            $category->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Ordem atualizada com sucesso!']);
    }
}
