<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Seller;
use App\Models\SellerAllocation;
use App\Models\User;
use App\Models\WeeklyStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SupplierWebController extends Controller
{
    private function supplier()
    {
        return Auth::user()->supplier;
    }

    public function dashboard(): View
    {
        $supplier = $this->supplier();

        $stats = [
            'total_sellers'   => $supplier->sellers()->count(),
            'active_sellers'  => $supplier->sellers()->whereHas('user', fn($q) => $q->where('is_active', true))->count(),
            'pending_payments'=> Payment::whereHas('order', fn($q) => $q->where('supplier_id', $supplier->id))->where('status', 'pending')->count(),
            'late_payments'   => Payment::whereHas('order', fn($q) => $q->where('supplier_id', $supplier->id))->where('status', 'late')->count(),
            'total_revenue'   => Payment::whereHas('order', fn($q) => $q->where('supplier_id', $supplier->id))->where('status', 'paid')->sum('amount'),
            'current_stock'   => $supplier->weeklyStocks()->orderBy('week_start', 'desc')->first(),
        ];

        $recentPayments = Payment::whereHas('order', fn($q) => $q->where('supplier_id', $supplier->id))
            ->with(['order', 'payer'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('supplier.dashboard', compact('stats', 'recentPayments'));
    }

    // ─── Vendeurs ────────────────────────────────────────────────────────────

    public function sellers(): View
    {
        $sellers = $this->supplier()->sellers()->with('user')->get();
        return view('supplier.sellers', compact('sellers'));
    }

    public function createSeller(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:50',
            'password'   => 'required|string|min:8|confirmed',
            'unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'role'     => 'seller',
                'password' => Hash::make($request->password),
            ]);
            Seller::create([
                'user_id'     => $user->id,
                'supplier_id' => $this->supplier()->id,
                'unit_price'  => $request->unit_price,
            ]);
        });

        return redirect()->route('supplier.sellers')->with('success', 'Vendeur créé avec succès.');
    }

    public function updateSeller(Request $request, int $sellerId): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => 'nullable|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'is_active'  => 'nullable|boolean',
        ]);

        $seller = $this->supplier()->sellers()->findOrFail($sellerId);

        DB::transaction(function () use ($request, $seller) {
            $seller->user->update([
                'name'      => $request->name,
                'phone'     => $request->phone,
                'is_active' => $request->has('is_active'),
            ]);
            $seller->update(['unit_price' => $request->unit_price]);
        });

        return redirect()->route('supplier.sellers')->with('success', 'Vendeur mis à jour.');
    }

    public function deleteSeller(int $sellerId): RedirectResponse
    {
        $seller = $this->supplier()->sellers()->findOrFail($sellerId);
        $seller->user->delete();

        return redirect()->route('supplier.sellers')->with('success', 'Vendeur supprimé.');
    }

    // ─── Stocks ──────────────────────────────────────────────────────────────

    public function stocks(): View
    {
        $stocks = $this->supplier()->weeklyStocks()
            ->with('sellerAllocations.seller.user')
            ->orderBy('week_start', 'desc')
            ->get();

        $sellers = $this->supplier()->sellers()->with('user')->where(function($q) {
            $q->whereHas('user', fn($u) => $u->where('is_active', true));
        })->get();

        return view('supplier.stocks', compact('stocks', 'sellers'));
    }

    public function createStock(Request $request): RedirectResponse
    {
        $request->validate([
            'week_start'  => 'required|date',
            'total_qty'   => 'required|integer|min:1',
            'unit_price'  => 'required|numeric|min:0',
        ]);

        WeeklyStock::create([
            'supplier_id'   => $this->supplier()->id,
            'week_start'    => $request->week_start,
            'total_qty'     => $request->total_qty,
            'available_qty' => $request->total_qty,
            'unit_price'    => $request->unit_price,
        ]);

        return redirect()->route('supplier.stocks')->with('success', 'Stock créé avec succès.');
    }

    public function allocateSeller(Request $request, int $stockId): RedirectResponse
    {
        $request->validate([
            'seller_id'     => 'required|integer|exists:sellers,id',
            'allocated_qty' => 'required|integer|min:1',
        ]);

        $stock  = $this->supplier()->weeklyStocks()->findOrFail($stockId);
        $seller = $this->supplier()->sellers()->findOrFail($request->seller_id);

        if ($stock->available_qty < $request->allocated_qty) {
            return back()->withErrors(['allocated_qty' => 'Stock insuffisant. Disponible : ' . $stock->available_qty]);
        }

        DB::transaction(function () use ($request, $stock, $seller) {
            $existing = SellerAllocation::where('weekly_stock_id', $stock->id)
                ->where('seller_id', $seller->id)->first();

            if ($existing) {
                $diff = $request->allocated_qty - $existing->allocated_qty;
                $stock->decrement('available_qty', $diff);
                $existing->update([
                    'allocated_qty' => $request->allocated_qty,
                    'remaining_qty' => $existing->remaining_qty + $diff,
                ]);
            } else {
                $stock->decrement('available_qty', $request->allocated_qty);
                SellerAllocation::create([
                    'weekly_stock_id' => $stock->id,
                    'seller_id'       => $seller->id,
                    'allocated_qty'   => $request->allocated_qty,
                    'remaining_qty'   => $request->allocated_qty,
                ]);
            }
        });

        return redirect()->route('supplier.stocks')->with('success', 'Stock alloué avec succès.');
    }

    // ─── Paiements ───────────────────────────────────────────────────────────

    public function payments(): View
    {
        $supplier = $this->supplier();
        $payments = Payment::whereHas('order', fn($q) => $q->where('supplier_id', $supplier->id))
            ->with(['order', 'payer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('supplier.payments', compact('payments'));
    }

    public function updatePayment(Request $request, int $paymentId): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,paid,late,disputed',
            'notes'  => 'nullable|string',
        ]);

        $supplier = $this->supplier();
        $payment  = Payment::whereHas('order', fn($q) => $q->where('supplier_id', $supplier->id))
            ->findOrFail($paymentId);

        $payment->update([
            'status'  => $request->status,
            'notes'   => $request->notes,
            'paid_at' => $request->status === 'paid' ? now() : $payment->paid_at,
        ]);

        return redirect()->route('supplier.payments')->with('success', 'Paiement mis à jour.');
    }
}
