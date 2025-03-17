<?php

namespace App\Http\Controllers;

use App\Models\Inscricao;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(){

        //Inscricao::pegarInscrito()

        return view('home.index');
    }
}
