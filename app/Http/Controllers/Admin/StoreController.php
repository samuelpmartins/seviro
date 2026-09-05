<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    public function edit(Store $store)
    {
        $store->load('user');

        return view('admin.stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'document' => 'required|string|max:20|unique:stores,document,' . $store->id,
            'logo' => 'nullable|image|max:10240',
            'cover_image' => 'nullable|image|max:10240',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($request->hasFile('logo')) {
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            $store->logo = $request->file('logo')->store('logos', 'public');
        }

        if ($request->hasFile('cover_image')) {
            if ($store->cover_image) {
                Storage::disk('public')->delete($store->cover_image);
            }
            $store->cover_image = $request->file('cover_image')->store('covers', 'public');
        }

        $store->name = $validated['name'];
        $store->phone = $validated['phone'];
        $store->address = $validated['address'];
        $store->document = $validated['document'];
        $store->save();

        if (!empty($validated['password']) && $store->user) {
            $store->user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Restaurante atualizado com sucesso.');
    }
}
