<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PalindromoController extends Controller
{
    public function index(Request $request)
    {
        return $this->page($request, 'palindromo');
    }

    public function verificar(Request $request)
    {
        $texto = $request->input('texto', '');
        if (!is_string($texto) || trim($texto) === '') {
            $request->session()->flash('form_errors', ['Escribe una palabra o frase para comprobarla.']);
            return redirect('/palindromo');
        }
        $textoLimpio = $this->limpiarTexto($texto);
        if ($textoLimpio === '') {
            $request->session()->flash('form_errors', ['Escribe al menos una letra o un número.']);
            return redirect('/palindromo');
        }
        $esPalindromo = $this->esPalindromo($textoLimpio);

        return $this->page($request, 'palindromo', [
            'textoOriginal' => $texto,
            'esPalindromo' => $esPalindromo
        ]);
    }

    private function limpiarTexto($texto)
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        return preg_replace('/[^\p{L}\p{N}]/u', '', $texto) ?? '';
    }

    private function esPalindromo($texto)
    {
        $characters = preg_split('//u', $texto, -1, PREG_SPLIT_NO_EMPTY);
        return $characters === array_reverse($characters);
    }
}
