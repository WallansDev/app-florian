<x-layouts.app title="Stocks Hebdomadaires">
<div x-data="{ showCreate: false, showAllocate: null }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Stocks Hebdomadaires</h2>
            <p class="text-sm text-slate-500 mt-0.5">Gérez vos stocks par semaine et allouez-les à vos vendeurs</p>
        </div>
        <button @click="showCreate = true"
                class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nouveau stock
        </button>
    </div>

    <div class="space-y-4">
        @forelse($stocks as $stock)
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <div class="flex items-center gap-4">
                        <div class="text-sm font-semibold text-slate-800">
                            Semaine du {{ \Carbon\Carbon::parse($stock->week_start)->format('d/m/Y') }}
                        </div>
                        <span class="text-xs text-slate-500 bg-white border border-slate-200 px-2 py-0.5 rounded-full">
                            {{ number_format($stock->unit_price, 2, ',', ' ') }} €/unité
                        </span>
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <div class="text-center">
                            <div class="font-bold text-slate-800">{{ $stock->total_qty }}</div>
                            <div class="text-xs text-slate-400">Total</div>
                        </div>
                        <div class="w-px h-8 bg-slate-200"></div>
                        <div class="text-center">
                            <div class="font-bold text-indigo-600">{{ $stock->available_qty }}</div>
                            <div class="text-xs text-slate-400">Disponible</div>
                        </div>
                        <div class="w-px h-8 bg-slate-200"></div>
                        <div class="text-center">
                            <div class="font-bold text-emerald-600">{{ $stock->total_qty - $stock->available_qty }}</div>
                            <div class="text-xs text-slate-400">Alloué</div>
                        </div>
                        <button @click="showAllocate = {{ $stock->id }}"
                                class="ml-4 flex items-center gap-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                            Allouer
                        </button>
                    </div>
                </div>

                {{-- Barre de progression --}}
                <div class="px-6 pt-3 pb-1">
                    @php $pct = $stock->total_qty > 0 ? round(($stock->total_qty - $stock->available_qty) / $stock->total_qty * 100) : 0; @endphp
                    <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                        <span>{{ $pct }}% alloué</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-indigo-500 h-1.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                </div>

                {{-- Allocations --}}
                @if($stock->sellerAllocations->count())
                    <div class="px-6 py-3">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-slate-400 uppercase tracking-wide">
                                    <th class="text-left py-1 font-medium">Vendeur</th>
                                    <th class="text-right py-1 font-medium">Alloué</th>
                                    <th class="text-right py-1 font-medium">Restant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($stock->sellerAllocations as $alloc)
                                    <tr>
                                        <td class="py-2 font-medium text-slate-700">{{ $alloc->seller->user->name }}</td>
                                        <td class="py-2 text-right text-slate-600">{{ $alloc->allocated_qty }}</td>
                                        <td class="py-2 text-right font-semibold text-indigo-600">{{ $alloc->remaining_qty }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Modal Allocation pour ce stock --}}
            <div x-show="showAllocate === {{ $stock->id }}" x-transition
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div @click.outside="showAllocate = null" class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-800">Allouer du stock</h3>
                        <button @click="showAllocate = null" class="text-slate-400 hover:text-slate-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('supplier.stocks.allocate', $stock->id) }}" class="px-6 py-5 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Vendeur</label>
                            <select name="seller_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="">Sélectionner un vendeur...</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}">{{ $seller->user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Quantité (disponible : {{ $stock->available_qty }})
                            </label>
                            <input type="number" name="allocated_qty" min="1" max="{{ $stock->available_qty }}" required
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">Allouer</button>
                            <button type="button" @click="showAllocate = null" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-lg text-sm font-medium transition-colors">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>

        @empty
            <div class="bg-white rounded-xl border border-slate-200 py-16 text-center text-slate-400">
                Aucun stock créé. Cliquez sur "Nouveau stock" pour commencer.
            </div>
        @endforelse
    </div>

    {{-- Modal Créer stock --}}
    <div x-show="showCreate" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.outside="showCreate = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-800">Créer un stock hebdomadaire</h3>
                <button @click="showCreate = false" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('supplier.stocks.create') }}" class="px-6 py-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Semaine (lundi)</label>
                    <input type="date" name="week_start" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Quantité totale</label>
                    <input type="number" name="total_qty" min="1" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Prix unitaire (€)</label>
                    <input type="number" name="unit_price" step="0.01" min="0" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">Créer</button>
                    <button type="button" @click="showCreate = false" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-lg text-sm font-medium transition-colors">Annuler</button>
                </div>
            </form>
        </div>
    </div>

</div>
</x-layouts.app>
