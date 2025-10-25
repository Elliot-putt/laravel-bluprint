<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SubmittedPRViewController extends Controller
{
    public function __invoke(Request $request)
    {
        return Inertia::render('SubmittedPR', [
            'prLink' => $request->get('prLink'),
            'repositoryName' => $request->get('repositoryName'),
        ]);
    }

}
