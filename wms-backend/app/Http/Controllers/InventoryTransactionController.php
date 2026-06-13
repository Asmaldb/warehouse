<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryTransactionController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $query = InventoryTransaction::with(['product', 'user'])
            ->whereHas('product', fn($q) => $q->where('user_id', $userId));

        if ($request->filled('product_id'))
            $query->where('product_id', $request->product_id);

        if ($request->filled('transaction_type'))
            $query->where('transaction_type', $request->transaction_type);

        if ($request->filled('from_date'))
            $query->whereDate('created_at', '>=', $request->from_date);

        if ($request->filled('to_date'))
            $query->whereDate('created_at', '<=', $request->to_date);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reference_number', 'like', "%{$s}%")
                  ->orWhere('party_name', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhereHas('product', fn($p) =>
                        $p->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"));
            });
        }

        $transactions = $query->latest()->paginate($request->get('per_page', 15));

        $summaryQuery = InventoryTransaction::with(['product'])
            ->whereHas('product', fn($q) => $q->where('user_id', $userId));

        $summary = $summaryQuery->selectRaw('
            COUNT(*) as total_count,
            SUM(CASE WHEN transaction_type IN ("receipt","return_in","initial") THEN quantity ELSE 0 END) as total_in,
            SUM(CASE WHEN transaction_type IN ("issue","return_out","scrap") THEN quantity ELSE 0 END) as total_out,
            SUM(total_value) as total_value
        ')->first();

        return response()->json([
            'success'      => true,
            'transactions' => $transactions,
            'summary'      => $summary,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'       => 'required|integer|exists:products,id',
            'transaction_type' => 'required|in:receipt,issue,return_in,return_out,transfer,adjustment,scrap,initial',
            'quantity'         => 'required|numeric|min:0.01',
            'unit_price'       => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:100',
            'party_name'       => 'nullable|string|max:255',
            'description'      => 'nullable|string|max:1000',
        ], [
            'product_id.required'       => 'کالا الزامی است.',
            'product_id.exists'         => 'کالای انتخاب‌شده یافت نشد.',
            'transaction_type.required' => 'نوع رویداد الزامی است.',
            'transaction_type.in'       => 'نوع رویداد معتبر نیست.',
            'quantity.required'         => 'مقدار الزامی است.',
            'quantity.min'              => 'مقدار باید بزرگ‌تر از صفر باشد.',
        ]);

        $product = Product::where('id', $validated['product_id'])
                          ->where('user_id', $request->user()->id)
                          ->firstOrFail();

        $decreaseTypes = ['issue', 'return_out', 'scrap'];
        if (in_array($validated['transaction_type'], $decreaseTypes)) {
            if ((float)$product->quantity < (float)$validated['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "موجودی کافی نیست. موجودی فعلی: {$product->quantity}",
                ], 422);
            }
        }

        $transaction = DB::transaction(function () use ($validated, $product, $request) {
            $qty        = (float) $validated['quantity'];
            $unitPrice  = (float) ($validated['unit_price'] ?? $product->price);
            $totalValue = $qty * $unitPrice;
            $type       = $validated['transaction_type'];
            $newBalance = $product->applyTransaction($type, $qty);

            return InventoryTransaction::create([
                'product_id'       => $product->id,
                'user_id'          => $request->user()->id,
                'transaction_type' => $type,
                'quantity'         => $qty,
                'unit_price'       => $unitPrice,
                'total_value'      => $totalValue,
                'balance_after'    => $newBalance,
                'reference_number' => $validated['reference_number'] ?? null,
                'party_name'       => $validated['party_name'] ?? null,
                'description'      => $validated['description'] ?? null,
            ]);
        });

        $transaction->load(['product', 'user']);

        return response()->json([
            'success'     => true,
            'message'     => 'رویداد با موفقیت ثبت شد.',
            'transaction' => $transaction,
            'new_balance' => $transaction->balance_after,
        ], 201);
    }

    public function show(Request $request, InventoryTransaction $inventoryTransaction)
    {
        if ($inventoryTransaction->product->user_id !== $request->user()->id)
            return response()->json(['success' => false, 'message' => 'دسترسی غیرمجاز.'], 403);

        $inventoryTransaction->load(['product', 'user']);

        return response()->json(['success' => true, 'transaction' => $inventoryTransaction]);
    }

    public function destroy(Request $request, InventoryTransaction $inventoryTransaction)
    {
        if ($inventoryTransaction->product->user_id !== $request->user()->id)
            return response()->json(['success' => false, 'message' => 'دسترسی غیرمجاز.'], 403);

        DB::transaction(function () use ($inventoryTransaction) {
            $product = $inventoryTransaction->product;
            $type    = $inventoryTransaction->transaction_type;
            $qty     = (float) $inventoryTransaction->quantity;

            $reverseType = match(true) {
                in_array($type, InventoryTransaction::$increaseTypes) => 'issue',
                in_array($type, InventoryTransaction::$decreaseTypes) => 'receipt',
                default => 'adjustment',
            };

            $product->applyTransaction($reverseType, $qty);
            $inventoryTransaction->delete();
        });

        return response()->json(['success' => true, 'message' => 'رویداد حذف شد و موجودی اصلاح گردید.']);
    }

    public function kardex(Request $request, $productId)
    {
        $product = Product::where('id', $productId)
                          ->where('user_id', $request->user()->id)
                          ->firstOrFail();

        $query = InventoryTransaction::where('product_id', $productId)->with('user');

        if ($request->filled('from_date'))
            $query->whereDate('created_at', '>=', $request->from_date);

        if ($request->filled('to_date'))
            $query->whereDate('created_at', '<=', $request->to_date);

        if ($request->filled('transaction_type'))
            $query->where('transaction_type', $request->transaction_type);

        $rows = $query->oldest()->get();

        $openingBalance = 0;
        if ($request->filled('from_date')) {
            $prevTx = InventoryTransaction::where('product_id', $productId)
                ->whereDate('created_at', '<', $request->from_date)
                ->latest()->first();
            $openingBalance = $prevTx ? (float)$prevTx->balance_after : 0;
        }

        $summary = [
            'opening_balance' => $openingBalance,
            'total_in'        => $rows->filter(fn($r) => in_array($r->transaction_type, InventoryTransaction::$increaseTypes))->sum('quantity'),
            'total_out'       => $rows->filter(fn($r) => in_array($r->transaction_type, InventoryTransaction::$decreaseTypes))->sum('quantity'),
            'total_in_value'  => $rows->filter(fn($r) => in_array($r->transaction_type, InventoryTransaction::$increaseTypes))->sum('total_value'),
            'total_out_value' => $rows->filter(fn($r) => in_array($r->transaction_type, InventoryTransaction::$decreaseTypes))->sum('total_value'),
            'closing_balance' => (float) $product->quantity,
        ];

        return response()->json([
            'success'         => true,
            'product'         => $product,
            'opening_balance' => $openingBalance,
            'rows'            => $rows,
            'summary'         => $summary,
        ]);
    }

    public function dashboardStats(Request $request)
    {
        $userId = $request->user()->id;

        $stats = InventoryTransaction::whereHas('product', fn($q) => $q->where('user_id', $userId))
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('transaction_type, COUNT(*) as count, SUM(quantity) as total_qty, SUM(total_value) as total_value')
            ->groupBy('transaction_type')
            ->get()
            ->keyBy('transaction_type');

        $lowStock = Product::where('user_id', $userId)
            ->where('quantity', '<', 5)
            ->orderBy('quantity')
            ->take(5)
            ->get(['id', 'name', 'code', 'quantity']);

        return response()->json([
            'success'   => true,
            'stats'     => $stats,
            'low_stock' => $lowStock,
        ]);
    }
}
