<?php

namespace App\Http\Controllers;

use App\Models\LifetimeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LifetimeRequestController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.lifetime', [
            'latestRequest' => LifetimeRequest::where('user_id', $request->user()->id)->latest()->first(),
            'active' => $request->user()->accessLevel() === 'lifetime',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:20', 'max:2000']]);
        $user = $request->user();

        if ($user->accessLevel() === 'lifetime') {
            return back()->with('status', 'Lifetime Pro is already active on your account.');
        }

        DB::transaction(function () use ($user, $data) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if (LifetimeRequest::where('pending_user_id', $user->id)->exists()) {
                throw ValidationException::withMessages(['reason' => 'You already have a request waiting for review.']);
            }
            LifetimeRequest::create(['user_id' => $user->id, 'pending_user_id' => $user->id, 'reason' => $data['reason']]);
        });
        return back()->with('status', 'Your request is in the review queue. We’ll notify you when it’s reviewed.');
    }
}
