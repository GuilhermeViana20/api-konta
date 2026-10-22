<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoriaResource;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        return CategoriaResource::collection(
            $request->user()->categorias()->orderBy('nome')->get()
        );
    }
}
