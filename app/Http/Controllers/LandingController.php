<?php

namespace App\Http\Controllers;

/**
 * Controller untuk mengelola tampilan landing page utama website StockIn.
 */
class LandingController extends Controller
{
    /**
     * Menampilkan landing page publik StockIn.
     */
    public function index()
    {
        return view('landing');
    }
}
