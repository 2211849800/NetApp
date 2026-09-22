<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('supervisor.dashboard', [
            'roleLabel' => 'Supervisor',
        ]);
    }
}
