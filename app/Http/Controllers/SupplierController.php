<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Payment;
use App\Models\Seller;
use App\Models\SellerAllocation;
use App\Models\User;
use App\Models\WeeklyStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    // ─── Gestion des Vendeurs ───────────────────────────────────────────────

    public function sellers(Request $request): JsonResponse
    {
        $supplier = $request->user('api')->supplier;

        $sellers = $supplier->sellers()
            ->with('user')
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'unit_price' => $s->unit_price,
                'user'       => $s->user->only('id', 'name', 'email', 'phone', 'is_active'),
            ]);

        return response()->json($sellers);
    }

    public function createSeller(Request $request): JsonResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:50',
            'password'   => 'required|string|min:8',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $supplier = $request->user('api')->supplier;

        $seller = DB::transaction(function () use ($request, $supplier) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'role'     => User::ROLE_SELLER,
                'password' => Hash::make($request->password),
            ]);

            return Seller::create([
                'user_id'     => $user->id,
                'supplier_id' => $supplier->id,
                'unit_price'  => $request->unit_price,
            ]);
        });

        return response()->json($seller->load('user'), 201);
    }

    public function updateSeller(Request $request, int $sellerId): JsonResponse
    {
        $supplier = $request->user('api')->supplier;
        $seller = $supplier->sellers()->findOrFail($sellerId);

        $request->validate([
            'name'       => 'sometimes|string|max:255',
            'phone'      => 'nullable|string|max:50',
            'unit_price' => 'sometimes|numeric|min:0',
            'is_active'  => 'sometimes|boolean',
        ]);

        DB::transaction(function () use ($request, $seller) {
            $seller->user->update($request->only('name', 'phone', 'is_active'));
            $seller->update($request->only('unit_price'));
        });

        return response()->json($seller->fresh()->load('user'));
    }

    public function deleteSeller(Request $request, int $sellerId): JsonResponse
    {
        $supplier = $request->user('api')->supplier;
        $seller = $supplier->sellers()->findOrFail($sellerId);
        $seller->user->delete();

        return response()->json(['message' => 'Vendeur supprimé.']);
    }

    // ─── Gestion des Stocks hebdomadaires ───────────────────────────────────

    public function stocks(Request $request): JsonResponse
    {
        $supplier = $request->user('api')->supplier;

        $stocks = $supplier->weeklyStocks()
            ->with('sellerAllocations.seller.user')
            ->orderBy('week_start', 'desc')
            ->get();

        return response()->json($stocks);
    }

    public function createStock(Request $request): JsonResponse
    {
        $request->validate([
            'week_start'  => 'required|date|date_format:Y-m-d',
            'total_qty'   => 'required|integer|min:1',
            'unit_price'  => 'required|numeric|min:0',
        ]);

        $supplier = $request->user('api')->supplier;

        $stock = WeeklyStock::create([
            'supplier_id'   => $supplier->id,
            'week_start'    => $request->week_start,
            'total_qty'     => $request->total_qty,
            'available_qty' => $request->total_qty,
            'unit_price'    => $request->unit_price,
        ]);

        return response()->json($stock, 201);
    }

    public function updateStock(Request $request, int $stockId): JsonResponse
    {
        $supplier = $request->user('api')->supplier;
        $stock = $supplier->weeklyStocks()->findOrFail($stockId);

        $request->validate([
            'total_qty'   => 'sometimes|integer|min:1',
            'unit_price'  => 'sometimes|numeric|min:0',
        ]);

        $stock->update($request->only('total_qty', 'unit_price'));

        return response()->json($stock->fresh());
    }

    // ─── Allocation de stock aux Vendeurs ────────────────────────────────────

    public function allocateSeller(Request $request, int $stockId): JsonResponse
    {
        $request->validate([
            'seller_id'     => 'required|integer|exists:sellers,id',
            'allocated_qty' => 'required|integer|min:1',
        ]);

        $supplier = $request->user('api')->supplier;
        $stock    = $supplier->weeklyStocks()->findOrFail($stockId);

        // Vérifier que le vendeur appartient bien à ce fournisseur
        $seller = $supplier->sellers()->findOrFail($request->seller_id);

        if ($stock->available_qty < $request->allocated_qty) {
            return response()->json([
                'message' => 'Stock insuffisant. Disponible : ' . $stock->available_qty,
            ], 422);
        }

        $allocation = DB::transaction(function () use ($request, $stock, $seller) {
            $existing = SellerAllocation::where('weekly_stock_id', $stock->id)
                ->where('seller_id', $seller->id)
                ->first();

            if ($existing) {
                $diff = $request->allocated_qty - $existing->allocated_qty;
                $stock->decrement('available_qty', $diff);
                $existing->update([
                    'allocated_qty' => $request->allocated_qty,
                    'remaining_qty' => $existing->remaining_qty + $diff,
                ]);
                return $existing;
            }

            $stock->decrement('available_qty', $request->allocated_qty);

            return SellerAllocation::create([
                'weekly_stock_id' => $stock->id,
                'seller_id'       => $seller->id,
                'allocated_qty'   => $request->allocated_qty,
                'remaining_qty'   => $request->allocated_qty,
            ]);
        });

        // Notifier le vendeur
        AppNotification::create([
            'user_id' => $seller->user_id,
            'type'    => 'stock_allocated',
            'message' => "Vous avez reçu {$request->allocated_qty} unité(s) pour la semaine du {$stock->week_start->format('d/m/Y')}.",
        ]);

        return response()->json($allocation->load('seller.user'), 201);
    }

    // ─── Suivi des Paiements ─────────────────────────────────────────────────

    public function payments(Request $request): JsonResponse
    {
        $supplier = $request->user('api')->supplier;

        $payments = Payment::whereHas('order', fn($q) => $q->where('supplier_id', $supplier->id))
            ->with(['order', 'payer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($payments);
    }

    public function updatePaymentStatus(Request $request, int $paymentId): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,paid,late,disputed',
            'notes'  => 'nullable|string',
        ]);

        $supplier = $request->user('api')->supplier;

        $payment = Payment::whereHas('order', fn($q) => $q->where('supplier_id', $supplier->id))
            ->findOrFail($paymentId);

        $payment->update([
            'status'  => $request->status,
            'notes'   => $request->notes,
            'paid_at' => $request->status === 'paid' ? now() : $payment->paid_at,
        ]);

        return response()->json($payment->fresh());
    }

    // ─── Dashboard ───────────────────────────────────────────────────────────

    public function dashboard(Request $request): JsonResponse
    {
        $supplier = $request->user('api')->supplier;

        return response()->json([
            'total_sellers'        => $supplier->sellers()->count(),
            'active_sellers'       => $supplier->sellers()->whereHas('user', fn($q) => $q->where('is_active', true))->count(),
            'payments_pending'     => Payment::whereHas('order', fn($q) => $q->where('supplier_id', $supplier->id))->where('status', 'pending')->count(),
            'payments_late'        => Payment::whereHas('order', fn($q) => $q->where('supplier_id', $supplier->id))->where('status', 'late')->count(),
            'revenue_total'        => Payment::whereHas('order', fn($q) => $q->where('supplier_id', $supplier->id))->where('status', 'paid')->sum('amount'),
            'current_week_stock'   => $supplier->weeklyStocks()->orderBy('week_start', 'desc')->first(),
        ]);
    }
}
