<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke()
    {
        return inertia('Home'); // buat resources/js/Pages/Home.vue
    }
}
