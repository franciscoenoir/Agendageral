<?php

namespace App\Http\Controllers;

use App\Models\Pasta;
use Illuminate\Http\Request;

class PastaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['nome' => 'required|string|max:100']);
        $pasta = Pasta::create([
            'nome' => $request->nome,
            'cor'  => $request->input('cor', 'gray'),
        ]);
        return response()->json($pasta);
    }

    public function update(Request $request, Pasta $pasta)
    {
        $request->validate(['nome' => 'required|string|max:100']);
        $pasta->update($request->only(['nome', 'cor']));
        return response()->json($pasta);
    }

    public function destroy(Pasta $pasta)
    {
        // demandas ficam sem pasta (nullOnDelete na migration)
        $pasta->delete();
        return response()->json(['ok' => true]);
    }
}
