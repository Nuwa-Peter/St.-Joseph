<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:root,headteacher,librarian');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Book::query();

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('author', 'like', "%{$searchTerm}%")
                  ->orWhere('isbn', 'like', "%{$searchTerm}%");
        }

        $books = $query->latest()->paginate(15);

        return view('library.books.index', compact('books'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('library.books.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn',
            'quantity' => 'required|integer|min:1',
            'shelf_location' => 'nullable|string|max:255',
            'published_date' => 'nullable|date',
        ]);

        Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'quantity' => $request->quantity,
            'available_quantity' => $request->quantity, // Initially, all are available
            'shelf_location' => $request->shelf_location,
            'published_date' => $request->published_date,
        ]);

        return redirect()->route('books.index')->with('success', 'Book added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book): View
    {
        return view('library.books.edit', compact('book'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn,' . $book->id,
            'quantity' => 'required|integer|min:1',
            'shelf_location' => 'nullable|string|max:255',
            'published_date' => 'nullable|date',
        ]);

        // This is a simplified logic for updating available quantity.
        // A more robust system would consider the number of checked-out books.
        $checkedOutCount = $book->quantity - $book->available_quantity;
        $newAvailable = $request->quantity - $checkedOutCount;

        $book->update([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'quantity' => $request->quantity,
            'available_quantity' => max(0, $newAvailable), // Ensure it doesn't go below zero
            'shelf_location' => $request->shelf_location,
            'published_date' => $request->published_date,
        ]);

        return redirect()->route('books.index')->with('success', 'Book updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book): RedirectResponse
    {
        // Add a check to prevent deletion if books are checked out
        if (($book->quantity - $book->available_quantity) > 0) {
            return redirect()->route('books.index')->with('error', 'Cannot delete book. Some copies are currently checked out.');
        }

        $book->delete();

        return redirect()->route('books.index')->with('success', 'Book deleted successfully.');
    }
}
