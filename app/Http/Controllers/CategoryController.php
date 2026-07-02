<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Return all categories as JSON (for Alpine.js reactive dropdown).
     */
    public function index()
    {
        $categories = Category::orderBy('type')->orderBy('name')->get();
        return response()->json($categories);
    }

    /**
     * Create a new custom category from the manual form.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:income,expense',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max'      => 'Nama kategori maksimal 100 karakter.',
            'type.required' => 'Tipe kategori wajib dipilih.',
            'type.in'       => 'Tipe harus pemasukan atau pengeluaran.',
        ]);

        // Return existing if already there (idempotent)
        $existing = Category::where('name', $request->name)
            ->where('type', $request->type)
            ->first();

        if ($existing) {
            return response()->json([
                'success'  => true,
                'category' => $existing,
                'existed'  => true,
                'message'  => 'Kategori sudah ada.',
            ]);
        }

        $category = Category::create([
            'name' => trim($request->name),
            'type' => $request->type,
        ]);

        return response()->json([
            'success'  => true,
            'category' => $category,
            'existed'  => false,
            'message'  => 'Kategori "' . $category->name . '" berhasil dibuat.',
        ], 201);
    }
}
