<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;


class TallaController extends Controller
{

public function calcularTallaIdeal(Request $request)
{
    $request->validate([
        'altura' => 'required|numeric|min:100|max:250', 
        'peso' => 'required|numeric|min:30|max:200',   
        'preferencia' => 'required|in:ajustada,normal,ancha'
    ]);

    $alturaMetros = $request->altura / 100;
    $imc = $request->peso / ($alturaMetros * $alturaMetros);


    if ($imc < 20) {
        $tallaBase = 'S';
    } elseif ($imc >= 20 && $imc < 25) {
        $tallaBase = 'M';
    } elseif ($imc >= 25 && $imc < 30) {
        $tallaBase = 'L';
    } else {
        $tallaBase = 'XL';
    }


    $tallas = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
    $indiceActual = array_search($tallaBase, $tallas);

    if ($request->preferencia === 'ajustada' && $indiceActual > 0) {
        $tallaRecomendada = $tallas[$indiceActual - 1];
    } elseif ($request->preferencia === 'ancha' && $indiceActual < count($tallas) - 1) {
        $tallaRecomendada = $tallas[$indiceActual + 1];
    } else {
        $tallaRecomendada = $tallaBase;
    }

    return response()->json([
        'talla' => $tallaRecomendada,
        'talla_recomendada' => $tallaRecomendada,
        'mensaje' => "Basado en tus medidas y preferencia, te recomendamos la talla $tallaRecomendada."
    ]);
}
}