<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BookController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        
        $stats = [
            'totalBooks' => $user->books()->count(),
            'totalPages' => $user->books()->sum('pages'),
            'booksByYear' => $user->books()
                ->whereNotNull('finished_at')
                ->selectRaw('strftime("%Y", finished_at) as year, count(*) as count')
                ->groupBy('year')
                ->orderBy('year', 'desc')
                ->get(),
            'avgTimePerBook' => round($user->books()
                ->whereNotNull('finished_at')
                ->whereNotNull('started_at')
                ->get()
                ->map(fn($book) => Carbon::parse($book->started_at)->diffInDays(Carbon::parse($book->finished_at)))
                ->avg() ?? 0, 1),
            'currentBooks' => $user->books()->where('status', 'Am lesen')->get(),
            'recentBooks' => $user->books()->latest()->take(3)->get(),
            'booksThisMonth' => $user->books()
                ->whereNotNull('finished_at')
                ->whereRaw("strftime('%Y-%m', finished_at) = ?", [Carbon::now()->format('Y-m')])
                ->count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats
        ]);
    }

    public function index(Request $request)
    {
        $sortBy = $request->query('sort_by', 'finished_at');
        $sortOrder = $request->query('sort_order', 'desc');

        // Allow sorting by these fields
        $allowedSortFields = ['title', 'author', 'status', 'finished_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'finished_at';
        }

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $books = auth()->user()->books()
            ->orderBy($sortBy, $sortOrder)
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Books/Index', [
            'books' => $books,
            'filters' => [
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ]
        ]);
    }

    public function create()
    {
        return Inertia::render('Books/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'pages' => 'nullable|integer',
            'format' => 'nullable|string|max:255',
            'status' => 'required|string',
            'started_at' => 'nullable|date',
            'finished_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'cover_file' => 'nullable|image|max:2048',
            'published_year' => 'nullable|max:255',
            'genre' => 'nullable|string',
        ]);

        if ($request->hasFile('cover_file')) {
            $path = $request->file('cover_file')->store('covers', 'public');
            $validated['cover_image'] = Storage::url($path);
        } elseif ($request->filled('cover_image')) {
            $validated['cover_image'] = $this->downloadCover($request->cover_image);
        }

        auth()->user()->books()->create($validated);

        return redirect()->route('books.index')->with('success', 'Buch erfolgreich hinzugefügt.');
    }

    public function edit(Book $book)
    {
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Books/Edit', [
            'book' => $book
        ]);
    }

    public function update(Request $request, Book $book)
    {
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'pages' => 'nullable|integer',
            'format' => 'nullable|string|max:255',
            'status' => 'required|string',
            'started_at' => 'nullable|date',
            'finished_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'cover_file' => 'nullable|image|max:2048',
            'published_year' => 'nullable|max:255',
            'genre' => 'nullable|string',
        ]);

        if ($request->hasFile('cover_file')) {
            $path = $request->file('cover_file')->store('covers', 'public');
            $validated['cover_image'] = Storage::url($path);
        } elseif ($request->filled('cover_image') && $request->cover_image !== $book->cover_image) {
            $validated['cover_image'] = $this->downloadCover($request->cover_image);
        }

        $book->update($validated);

        return redirect()->route('books.index')->with('success', 'Buch erfolgreich aktualisiert.');
    }

    public function destroy(Book $book)
    {
        if ($book->user_id !== auth()->id()) {
            abort(403);
        }

        // Delete local cover if exists
        if ($book->cover_image && !Str::startsWith($book->cover_image, 'http')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $book->cover_image));
        }

        $book->delete();

        return redirect()->route('books.index')->with('success', 'Buch wurde gelöscht.');
    }

    private function downloadCover($url)
    {
        if (!$url || !Str::startsWith($url, 'http')) {
            return $url;
        }

        try {
            $response = Http::get($url);
            if ($response->successful()) {
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $filename = 'covers/' . Str::random(40) . '.' . $extension;
                
                Storage::disk('public')->put($filename, $response->body());
                
                return Storage::url($filename);
            }
        } catch (\Exception $e) {
            \Log::error('Cover download failed: ' . $e->getMessage());
        }

        return $url;
    }

    public function search(Request $request)
    {
        $title = $request->query('title');
        $response = Http::get("https://openlibrary.org/search.json", [
            'title' => $title,
            'limit' => 5
        ]);
        return $response->json();
    }

    public function searchByIsbn(Request $request)
    {
        $isbn = $request->query('isbn');
        $response = Http::get("https://openlibrary.org/api/books", [
            'bibkeys' => "ISBN:$isbn",
            'format' => 'json',
            'jscmd' => 'data'
        ]);
        return $response->json();
    }
}
