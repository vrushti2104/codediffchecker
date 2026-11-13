<?php

namespace App\Http\Controllers;

use App\Models\Comparison;
use App\Models\Entitlement;
use App\Models\UsageEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompareController extends Controller
{
    public function index(Request $request)
    {
        $saved = null;
        if ($request->filled('saved') && $request->user()) {
            $saved = Comparison::where('user_id', $request->user()->id)->find($request->integer('saved'));
        }
        return view('pages.compare', [
            'left' => $saved?->original ?? '', 'right' => $saved?->changed ?? '',
            'hasCompared' => $saved !== null, 'settings' => $saved?->settings ?? [],
            'notice' => $saved ? 'Loaded from your private saved comparisons.' : null,
        ]);
    }

    public function run(Request $request)
    {
        $user = $request->user();
        $characterLimit = $user ? config('codediff.lifetime.characters_per_version') : config('codediff.guest.characters_per_version');

        $data = $request->validate([
            'original' => ['present', 'nullable', 'string', 'max:'.$characterLimit],
            'changed' => ['present', 'nullable', 'string', 'max:'.$characterLimit],
            'language' => ['nullable', 'string', 'max:32'],
            'view' => ['nullable', 'in:side,unified'],
            'ignore_whitespace' => ['nullable', 'boolean'],
            'ignore_case' => ['nullable', 'boolean'],
            'save_comparison' => ['nullable', 'boolean'],
            'title' => ['nullable', 'string', 'max:120'],
        ]);
        $data['original'] = $data['original'] ?? '';
        $data['changed'] = $data['changed'] ?? '';

        if ($user && $request->boolean('save_comparison') && ! $user->hasVerifiedEmail()) {
            throw ValidationException::withMessages(['save_comparison' => 'Verify your email before saving comparisons.']);
        }

        $leftLines = preg_split('/\R/u', $data['original']) ?: [];
        $rightLines = preg_split('/\R/u', $data['changed']) ?: [];
        $lineLimit = config('codediff.max_lines_per_version');
        if (max(count($leftLines), count($rightLines)) > $lineLimit) {
            return back()->withErrors(['compare' => "Each version can contain up to {$lineLimit} lines for a safe, fast comparison."]);
        }

        $saveNotice = null;
        DB::transaction(function () use ($request, $user, $data, &$saveNotice) {
            $lifetime = false;
            if ($user) {
                $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                $lifetime = Entitlement::where('active_lifetime_user_id', $lockedUser->id)
                    ->where('type', 'lifetime')->where('status', 'active')->where('starts_at', '<=', now())->exists();
                $monthlyCount = UsageEvent::where('user_id', $lockedUser->id)->where('action', 'compare')
                    ->whereBetween('occurred_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
                $monthlyLimit = config('codediff.free.comparisons_per_month');
                if (! $lifetime && $monthlyCount >= $monthlyLimit) {
                    throw ValidationException::withMessages(['quota' => "You have reached the {$monthlyLimit} free comparisons for this month. Lifetime Pro has no monthly comparison limit."]);
                }
                $maxCharacters = $lifetime ? config('codediff.lifetime.characters_per_version') : config('codediff.free.characters_per_version');
                if (mb_strlen($data['original']) > $maxCharacters || mb_strlen($data['changed']) > $maxCharacters) {
                    throw ValidationException::withMessages(['compare' => 'Your plan character limit was exceeded.']);
                }
            }

            UsageEvent::create([
                'user_id' => $user?->id,
                'action' => 'compare',
                'units' => 1,
                'metadata' => ['language' => $data['language'] ?? 'plaintext'],
                'occurred_at' => now(),
            ]);

            if ($user && $request->boolean('save_comparison')) {
                $savedCount = Comparison::where('user_id', $user->id)->count();
                if (! $lifetime && $savedCount >= config('codediff.free.saved_comparisons')) {
                    $saveNotice = 'The comparison ran, but Free accounts can keep up to 5 saved comparisons. Delete one or request Lifetime Pro to save more.';
                    return;
                }
                Comparison::create([
                    'user_id' => $user->id,
                    'title' => ($data['title'] ?? '') ?: 'Comparison · '.now()->format('M j, Y g:i a'),
                    'language' => $data['language'] ?? 'plaintext',
                    'original' => $data['original'],
                    'changed' => $data['changed'],
                    'settings' => [
                        'view' => $data['view'] ?? 'side',
                        'ignore_whitespace' => $request->boolean('ignore_whitespace'),
                        'ignore_case' => $request->boolean('ignore_case'),
                    ],
                ]);
            }
        });

        return view('pages.compare', [
            'left' => $data['original'], 'right' => $data['changed'], 'hasCompared' => true,
            'notice' => $saveNotice, 'settings' => $data,
        ]);
    }
}
