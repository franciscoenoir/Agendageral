<?php

namespace App\Http\Controllers;

use App\Models\Lembrete;
use Illuminate\Http\Request;

class LembreteController extends Controller
{
    public function index()
    {
        $lembretes = Lembrete::orderBy('created_at')->get();
        return view('lembretes.index', compact('lembretes'));
    }

    public function store(Request $request)
    {
        $lembrete = Lembrete::create([
            'texto' => '',
            'cor'   => $request->input('cor', 'yellow'),
            'pos_x' => $request->input('pos_x', rand(80, 400)),
            'pos_y' => $request->input('pos_y', rand(80, 300)),
        ]);

        return response()->json($lembrete);
    }

    public function update(Request $request, Lembrete $lembrete)
    {
        $lembrete->update($request->only(['texto', 'cor', 'pos_x', 'pos_y']));
        return response()->json($lembrete);
    }

    public function destroy(Lembrete $lembrete)
    {
        $lembrete->delete();
        return response()->json(['ok' => true]);
    }
}
