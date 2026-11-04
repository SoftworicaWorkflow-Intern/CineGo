<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Customer directory (read-only).
     */
    public function index(Request $request): View
    {
        $term = $request->query('q');

        $users = User::query()
            ->withCount('bookings')
            ->when($term, function ($query) use ($term) {
                $query->where(function ($builder) use ($term) {
                    $builder->where('name', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%')
                        ->orWhere('phone', 'like', '%'.$term.'%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'term'));
    }
}
