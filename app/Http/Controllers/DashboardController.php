<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isDirector()) {
            // Le directeur voit d'abord ce qui ATTEND une action de sa part —
            // c'est l'information la plus utile pour lui, mise en avant.
            $stats = [
                'a_valider' => Report::where('status', 'submitted')->count(),
                'valides' => Report::where('status', 'validated')->count(),
                'rejetes' => Report::where('status', 'rejected')->count(),
                'total' => Report::count(),
            ];

            $reportsAVerifier = Report::where('status', 'submitted')
                ->with(['author', 'type'])
                ->latest()
                ->take(5)
                ->get();

            return view('dashboard', compact('stats', 'reportsAVerifier'));
        }

        // Un expert ne voit que SES propres rapports.
        $stats = [
            'brouillons' => Report::where('author_id', $user->id)->where('status', 'draft')->count(),
            'soumis' => Report::where('author_id', $user->id)->where('status', 'submitted')->count(),
            'valides' => Report::where('author_id', $user->id)->where('status', 'validated')->count(),
            'rejetes' => Report::where('author_id', $user->id)->where('status', 'rejected')->count(),
        ];

        $mesRapports = Report::where('author_id', $user->id)
            ->with('type')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'mesRapports'));
    }
}