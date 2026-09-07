<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = auth()->user()->quotes()->with('book');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('book', function ($bq) use ($search) {
                        $bq->where('title', 'like', "%{$search}%")
                            ->orWhere('author', 'like', "%{$search}%");
                    });
            });
        }

        $quotes = $query->latest()
            ->paginate(15)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($quotes);
        }

        return Inertia::render('Quotes/Index', [
            'quotes' => $quotes,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
}
