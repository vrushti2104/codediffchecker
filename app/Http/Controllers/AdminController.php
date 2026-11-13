<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Entitlement;
use App\Models\LifetimeRequest;
use App\Models\UsageEvent;
use App\Models\User;
use App\Notifications\LifetimeAccessReviewed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'usersCount' => User::count(),
            'pendingCount' => LifetimeRequest::where('status', 'pending')->count(),
            'lifetimeCount' => Entitlement::where('type', 'lifetime')->where('status', 'active')->count(),
            'comparisonsCount' => UsageEvent::where('action', 'compare')->count(),
            'recentRequests' => LifetimeRequest::with('user')->latest()->limit(6)->get(),
            'recentLogs' => AuditLog::with('actor')->latest()->limit(6)->get(),
        ]);
    }

    public function users(Request $request)
    {
        $query = User::with(['activeLifetimeGrant'])->latest();
        if ($search = $request->query('q')) {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'));
        }
        return view('admin.users', ['users' => $query->paginate(15)->withQueryString(), 'search' => $search ?? '']);
    }

    public function grants()
    {
        return view('admin.grants', ['grants' => Entitlement::with(['user', 'grantor', 'revoker'])->where('type', 'lifetime')->latest()->paginate(20)]);
    }

    public function requests(Request $request)
    {
        $status = $request->query('status', 'pending');
        $query = LifetimeRequest::with(['user', 'reviewer'])->latest();
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) $query->where('status', $status);
        $requests = $query->paginate(15)->withQueryString();
        return view('admin.requests', compact('requests', 'status'));
    }

    public function review(Request $request, LifetimeRequest $lifetimeRequest, string $decision)
    {
        $data = $request->validate(['review_note' => ['nullable', 'string', 'max:1000']]);
        $userToNotify = null;
        $outcome = $decision === 'approve' ? 'approved' : 'rejected';

        DB::transaction(function () use ($request, $lifetimeRequest, $decision, $data, &$userToNotify) {
            $record = LifetimeRequest::whereKey($lifetimeRequest->id)->lockForUpdate()->firstOrFail();
            abort_unless($record->status === 'pending', 409, 'This request has already been reviewed.');
            $user = User::whereKey($record->user_id)->lockForUpdate()->firstOrFail();
            $before = ['status' => $record->status];

            if ($decision === 'approve') {
                $active = Entitlement::where('active_lifetime_user_id', $user->id)->exists();
                if (! $active) {
                    Entitlement::create(['user_id' => $user->id, 'active_lifetime_user_id' => $user->id, 'type' => 'lifetime', 'status' => 'active', 'starts_at' => now(), 'granted_by' => $request->user()->id]);
                }
            }

            $record->update([
                'status' => $decision === 'approve' ? 'approved' : 'rejected',
                'pending_user_id' => null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_note' => $data['review_note'] ?? null,
            ]);
            AuditLog::create([
                'actor_id' => $request->user()->id, 'subject_type' => 'lifetime_request', 'subject_id' => $record->id,
                'action' => 'lifetime_request_'.$decision, 'before_data' => $before,
                'after_data' => ['status' => $record->status, 'user_id' => $user->id], 'ip' => $request->ip(),
            ]);
            $userToNotify = $user;
        });

        $userToNotify?->notify(new LifetimeAccessReviewed($outcome, $data['review_note'] ?? null));
        return back()->with('status', 'Request '.$outcome.'.');
    }

    public function grant(Request $request, User $user)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:1000']]);
        DB::transaction(function () use ($request, $user, $data) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $exists = Entitlement::where('active_lifetime_user_id', $locked->id)->exists();
            abort_if($exists, 409, 'This user already has active Lifetime Pro access.');
            $grant = Entitlement::create(['user_id' => $locked->id, 'active_lifetime_user_id' => $locked->id, 'type' => 'lifetime', 'status' => 'active', 'starts_at' => now(), 'granted_by' => $request->user()->id]);
            AuditLog::create([
                'actor_id' => $request->user()->id, 'subject_type' => 'entitlement', 'subject_id' => $grant->id,
                'action' => 'lifetime_access_granted', 'after_data' => ['user_id' => $locked->id, 'reason' => $data['reason']], 'ip' => $request->ip(),
            ]);
        });
        $user->notify(new LifetimeAccessReviewed('approved', 'An administrator granted Lifetime Pro access to your account.'));
        return back()->with('status', 'Lifetime Pro granted to '.$user->name.'.');
    }

    public function revoke(Request $request, Entitlement $entitlement)
    {
        $data = $request->validate(['revoke_reason' => ['required', 'string', 'min:5', 'max:1000']]);
        abort_unless($entitlement->type === 'lifetime' && $entitlement->status === 'active', 404);
        DB::transaction(function () use ($request, $entitlement, $data) {
            $locked = Entitlement::whereKey($entitlement->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === 'active', 409);
            $before = ['status' => $locked->status, 'ends_at' => $locked->ends_at];
            $locked->update(['status' => 'revoked', 'active_lifetime_user_id' => null, 'revoked_by' => $request->user()->id, 'revoked_at' => now(), 'revoke_reason' => $data['revoke_reason']]);
            AuditLog::create([
                'actor_id' => $request->user()->id, 'subject_type' => 'entitlement', 'subject_id' => $locked->id,
                'action' => 'lifetime_access_revoked', 'before_data' => $before,
                'after_data' => ['status' => $locked->status, 'reason' => $data['revoke_reason']], 'ip' => $request->ip(),
            ]);
        });
        $entitlement->user?->notify(new LifetimeAccessReviewed('revoked', $data['revoke_reason']));
        return back()->with('status', 'Lifetime Pro access revoked.');
    }

    public function auditLog()
    {
        return view('admin.audit', ['logs' => AuditLog::with('actor')->latest()->paginate(25)]);
    }
}
