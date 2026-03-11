<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\SellerAllocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    // ─── Catalogue : stock disponible ────────────────────────────────────────

    public function availableStock(Request $request): JsonResponse
    {
        $client = $request->user('api')->client;
        $seller = $client->seller;

        $allocations = $seller->allocations()
            ->with('weeklyStock')
            ->where('remaining_qty', '>', 0)
            ->get()
            ->map(fn($a) => [
                'allocation_id' => $a->id,
                'week_start'    => $a->weeklyStock->week_start,
                'available_qty' => $a->remaining_qty,
                'unit_price'    => $client->unit_price, // prix du vendeur vers client
            ]);

        return response()->json($allocations);
    }

    // ─── Passer une commande ─────────────────────────────────────────────────

    public function placeOrder(Request $request): JsonResponse
    {
        $request->validate([
            'allocation_id' => 'required|integer|exists:seller_allocations,id',
            'quantity'      => 'required|integer|min:1',
        ]);

        $client = $request->user('api')->client;
        $seller = $client->seller;

        $allocation = SellerAllocation::where('seller_id', $seller->id)
            ->with('weeklyStock')
            ->findOrFail($request->allocation_id);

        if ($allocation->remaining_qty < $request->quantity) {
            return response()->json([
                'message' => 'Quantité demandée supérieure au stock disponible (' . $allocation->remaining_qty . ').',
            ], 422);
        }

        $order = DB::transaction(function () use ($request, $client, $seller, $allocation) {
            $allocation->decrement('remaining_qty', $request->quantity);

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'buyer_id'     => $client->user_id,
                'seller_id'    => $seller->id,
                'week_start'   => $allocation->weeklyStock->week_start,
                'quantity'     => $request->quantity,
                'unit_price'   => $client->unit_price,
                'status'       => Order::STATUS_CONFIRMED,
            ]);

            Payment::create([
                'order_id'  => $order->id,
                'payer_id'  => $client->user_id,
                'amount'    => $order->total_amount,
                'status'    => Payment::STATUS_PENDING,
                'due_date'  => now()->addDays(7),
            ]);

            return $order;
        });

        return response()->json($order->load('payment'), 201);
    }

    // ─── Mes commandes ───────────────────────────────────────────────────────

    public function myOrders(Request $request): JsonResponse
    {
        $client = $request->user('api')->client;

        $orders = Order::where('buyer_id', $client->user_id)
            ->with(['payment', 'seller.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    // ─── Mes paiements ───────────────────────────────────────────────────────

    public function myPayments(Request $request): JsonResponse
    {
        $client = $request->user('api')->client;

        $payments = Payment::where('payer_id', $client->user_id)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($payments);
    }

    // ─── Dashboard ───────────────────────────────────────────────────────────

    public function dashboard(Request $request): JsonResponse
    {
        $client = $request->user('api')->client;

        return response()->json([
            'total_orders'    => Order::where('buyer_id', $client->user_id)->count(),
            'pending_payment' => Payment::where('payer_id', $client->user_id)->where('status', 'pending')->sum('amount'),
            'my_seller'       => $client->seller?->user?->only('name', 'email', 'phone'),
            'my_unit_price'   => $client->unit_price,
        ]);
    }
}
