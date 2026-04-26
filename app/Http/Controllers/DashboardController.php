<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $userId = $request->user()->id;

        return view('dashboard', [
            'noteCount' => Note::where('user_id', $userId)->count(),
            'categoryCount' => Category::where('user_id', $userId)->count(),
            'recentNotes' => Note::where('user_id', $userId)
                ->with('category')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
        ]);
    }
}
