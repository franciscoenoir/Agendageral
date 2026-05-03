<?php

namespace App\Http\Controllers;

use App\Models\ChecklistItem;
use App\Models\Demanda;
use Illuminate\Http\Request;

class ChecklistItemController extends Controller
{
    public function store(Request $request, Demanda $demanda)
    {
        $request->validate(['texto' => 'required|string|max:255']);

        $item = $demanda->checklistItems()->create([
            'texto' => trim($request->texto),
            'ordem' => $demanda->checklistItems()->count(),
        ]);

        return response()->json($item);
    }

    public function update(Request $request, ChecklistItem $item)
    {
        $item->update($request->only(['texto', 'concluido']));

        return response()->json($item);
    }

    public function destroy(ChecklistItem $item)
    {
        $item->delete();

        return response()->json(['ok' => true]);
    }
}
