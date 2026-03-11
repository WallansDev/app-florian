@props(['href', 'active' => false])

<a href="{{ $href }}"
   class="flex items-center gap-3 px-4 py-2.5 mx-2 rounded-lg text-sm font-medium transition-colors
          {{ $active ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
    <span class="shrink-0">{{ $icon }}</span>
    {{ $slot }}
</a>
