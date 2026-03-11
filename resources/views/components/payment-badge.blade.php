@props(['status'])

@php
$config = match($status) {
    'paid'     => ['bg-emerald-100 text-emerald-700', 'Payé'],
    'pending'  => ['bg-amber-100 text-amber-700', 'En attente'],
    'late'     => ['bg-red-100 text-red-700', 'En retard'],
    'disputed' => ['bg-slate-100 text-slate-700', 'Contesté'],
    default    => ['bg-slate-100 text-slate-600', $status],
};
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config[0] }}">
    {{ $config[1] }}
</span>
