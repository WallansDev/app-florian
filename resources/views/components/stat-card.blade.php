@props(['label', 'value', 'color' => 'indigo', 'icon'])

<div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-start gap-4">
    <div class="shrink-0 w-10 h-10 rounded-lg flex items-center justify-center
        @if($color === 'indigo') bg-indigo-50 text-indigo-600
        @elseif($color === 'emerald') bg-emerald-50 text-emerald-600
        @elseif($color === 'amber') bg-amber-50 text-amber-600
        @elseif($color === 'red') bg-red-50 text-red-600
        @elseif($color === 'violet') bg-violet-50 text-violet-600
        @elseif($color === 'sky') bg-sky-50 text-sky-600
        @else bg-slate-100 text-slate-600 @endif">
        {{ $icon }}
    </div>
    <div>
        <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">{{ $label }}</p>
        <p class="text-2xl font-bold text-slate-800 mt-0.5">{{ $value }}</p>
    </div>
</div>
