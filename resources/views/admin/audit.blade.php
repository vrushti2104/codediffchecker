@extends('admin.layout')
@section('title', 'Audit log')
@section('admin-content')
<div class="dashboard-heading"><div><span class="eyebrow">Accountability</span><h1>Audit Log</h1><p>Recorded lifetime access and review actions.</p></div></div>
<div class="content-card"><div class="table-wrap"><table><thead><tr><th>Action</th><th>Actor</th><th>Subject</th><th>Date</th><th>Details</th></tr></thead><tbody>@forelse($logs as $log)<tr><td><span class="tag">{{ str_replace('_', ' ', $log->action) }}</span></td><td>{{ $log->actor?->name ?? 'System' }}</td><td>{{ $log->subject_type }} #{{ $log->subject_id }}</td><td>{{ $log->created_at->format('M j, Y H:i') }}</td><td>{{ data_get($log->after_data, 'reason', data_get($log->after_data, 'status', '—')) }}</td></tr>@empty<tr><td colspan="5" class="empty-cell">No audit entries yet.</td></tr>@endforelse</tbody></table></div><div class="pagination-wrap">{{ $logs->links() }}</div></div>
@endsection
