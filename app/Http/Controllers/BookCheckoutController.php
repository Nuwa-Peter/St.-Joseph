<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\BookCheckout;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class BookCheckoutController extends Controller
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
        $query = BookCheckout::with(['book', 'user']);

        if ($request->get('status') === 'overdue') {
            $query->where('status', 'checked_out')->where('due_date', '<', now());
        } elseif ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $checkouts = $query->latest('checkout_date')->paginate(20);

        return view('library.checkouts.index', compact('checkouts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('library.checkouts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $book = Book::findOrFail($request->book_id);
        $user = User::findOrFail($request->user_id);

        if ($book->available_quantity <= 0) {
            return back()->with('error', 'No available copies of this book to check out.');
        }

        // Decrement available quantity
        $book->decrement('available_quantity');

        // Create checkout record
        BookCheckout::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'checkout_date' => now(),
            'due_date' => now()->addWeeks(2), // Default 2-week loan period
            'status' => 'checked_out',
        ]);

        return redirect()->route('checkouts.index')->with('success', 'Book checked out successfully.');
    }

    /**
     * Mark a book as returned.
     */
    public function update(Request $request, BookCheckout $checkout): RedirectResponse
    {
        if ($checkout->status !== 'returned') {
            $checkout->update([
                'returned_date' => now(),
                'status' => 'returned',
            ]);

            // Increment available quantity
            $checkout->book->increment('available_quantity');

            return redirect()->route('checkouts.index')->with('success', 'Book marked as returned.');
        }

        return redirect()->route('checkouts.index')->with('info', 'This book has already been returned.');
    }
}
