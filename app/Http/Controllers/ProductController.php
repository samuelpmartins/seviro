<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Trata o valor do preço antes da validação
     */
    private function formatPrice($price)
    {
        if (!$price) return null;
        
        // Remove R$, espaços e pontos, e substitui vírgula por ponto
        $price = str_replace(['R$', ' ', '.'], '', $price);
        return str_replace(',', '.', $price);
    }

    public function index()
    {
        $store = auth()->user()->store;
        $categories = $store->categories()->orderBy('order')->get();
        $products = $store->products()->with('category')->orderBy('order')->paginate(10);
        
        return view('store.products.index', compact('categories', 'products'));
    }

    public function create()
    {
        $store = auth()->user()->store;
        $categories = $store->categories()->orderBy('order')->get();
        return view('store.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Tratar o preço antes da validação
        $request->merge([
            'price' => $this->formatPrice($request->price)
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ingredients' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:2048',
        ]);

        $store = auth()->user()->store;
        
        // Verificar se a categoria pertence à loja
        $category = $store->categories()->findOrFail($request->category_id);
        
        $lastOrder = $store->products()
            ->where('category_id', $category->id)
            ->max('order') ?? 0;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $store->products()->create([
            'name' => $request->name,
            'description' => $request->description,
            'ingredients' => $request->ingredients,
            'price' => $request->price,
            'category_id' => $category->id,
            'image' => $imagePath,
            'order' => $lastOrder + 1,
            'is_quick_item' => $request->boolean('is_quick_item')
        ]);

        // Verificar de onde veio a requisição para redirecionar corretamente
        $referer = $request->headers->get('referer');
        if (str_contains($referer, '/store/manage')) {
            return redirect()->route('store.manage')->with('success', 'Produto criado com sucesso!');
        } elseif (str_contains($referer, '/store/dashboard')) {
            return redirect()->route('store.dashboard')->with('success', 'Produto criado com sucesso!');
        }
        
        return redirect()->route('store.products.index')
            ->with('success', 'Produto criado com sucesso!');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        
        $store = auth()->user()->store;
        $categories = $store->categories()->orderBy('order')->get();
        
        return view('store.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        // Tratar o preço antes da validação
        $request->merge([
            'price' => $this->formatPrice($request->price)
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ingredients' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:2048',
            'active' => 'boolean'
        ]);

        $store = auth()->user()->store;
        
        // Verificar se a categoria pertence à loja
        $category = $store->categories()->findOrFail($request->category_id);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'ingredients' => $request->ingredients,
            'price' => $request->price,
            'category_id' => $category->id,
            'active' => $request->boolean('active'),
            'is_quick_item' => $request->boolean('is_quick_item')
        ]);

        // Verificar de onde veio a requisição para redirecionar corretamente
        $referer = $request->headers->get('referer');
        if (str_contains($referer, '/store/manage')) {
            return redirect()->route('store.manage')->with('success', 'Produto atualizado com sucesso!');
        } elseif (str_contains($referer, '/store/dashboard')) {
            return redirect()->route('store.dashboard')->with('success', 'Produto atualizado com sucesso!');
        }
        
        return redirect()->route('store.products.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('store.products.index')
            ->with('success', 'Produto excluído com sucesso!');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->products as $item) {
            $product = Product::find($item['id']);
            $this->authorize('update', $product);
            $product->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Ordem atualizada com sucesso!']);
    }
} 