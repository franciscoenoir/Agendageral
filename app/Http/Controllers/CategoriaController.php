<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['nome' => 'required|string|max:60|unique:categorias,nome']);

        $categoria = Categoria::create(['nome' => trim($request->nome)]);

        return response()->json($categoria);
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->delete();

        return response()->json(['ok' => true]);
    }
}
