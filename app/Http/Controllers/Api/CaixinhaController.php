<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CaixinhaResource;
use Illuminate\Http\Request;

class CaixinhaController extends Controller
{
    public function index(Request $request)
    {
        return CaixinhaResource::collection(
            $request->user()->caixinhas()->orderBy('nome')->get()
        );
    }
}
