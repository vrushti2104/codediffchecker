<?php

namespace App\Http\Controllers;

use App\Models\Comparison;
use App\Models\UsageEvent;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $lifetime = $user->accessLevel() === 'lifetime';
        $count = UsageEvent::where('user_id', $user->id)->where('action', 'compare')
            ->whereBetween('occurred_at', [now()->startOfMonth(), now()->endOfMonth()])->count();

        return view('pages.dashboard', [
            'user' => $user,
            'lifetime' => $lifetime,
            'monthCount' => $count,
            'monthlyLimit' => config('codediff.free.comparisons_per_month'),
            'savedCount' => Comparison::where('user_id', $user->id)->count(),
            'recent' => Comparison::where('user_id', $user->id)->latest()->limit(5)->get(),
            'notifications' => $user->unreadNotifications()->latest()->limit(3)->get(),
        ]);
    }

    public function comparisons(Request $request)
    {
        $comparisons = Comparison::where('user_id', $request->user()->id)->latest()->paginate(10);
        return view('pages.comparisons', compact('comparisons'));
    }

    public function deleteComparison(Request $request, Comparison $comparison)
    {
        abort_unless($comparison->user_id === $request->user()->id, 404);
        $comparison->delete();
        return back()->with('status', 'Saved comparison deleted.');
    }

    public function readNotifications(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return back()->with('status', 'Notifications marked as read.');
    }
}
