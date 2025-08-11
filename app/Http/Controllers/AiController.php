<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AiController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:root,headteacher');
    }

    /**
     * Display the AI reporting dashboard.
     */
    public function index(): View
    {
        // This will be a placeholder for now.
        // In the future, this could pass data from Python services.
        return view('advanced.ai.index');
    }
}
