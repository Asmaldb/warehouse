namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('user_id', $request->user()->id);

        // جستجو
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // مرتب‌سازی
        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowed   = ['name', 'code', 'quantity', 'price', 'created_at'];

        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $products = $query->paginate(10);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:100|unique:products',
            'quantity'    => 'required|integer|min:0',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        $product = Product::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'کالا با موفقیت افزوده شد',
            'product' => $product,
        ], 201);
    }

    public function show(Request $request, Product $product)
    {
        if ($product->user_id !== $request->user()->id) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== $request->user()->id) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:100|unique:products,code,' . $product->id,
            'quantity'    => 'required|integer|min:0',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'کالا با موفقیت ویرایش شد',
            'product' => $product,
        ]);
    }

    public function destroy(Request $request, Product $product)
    {
        if ($product->user_id !== $request->user()->id) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'کالا با موفقیت حذف شد']);
    }

    public function stats(Request $request)
    {
        $userId = $request->user()->id;

        return response()->json([
            'total_products' => Product::where('user_id', $userId)->count(),
            'total_quantity' => Product::where('user_id', $userId)->sum('quantity'),
            'total_value'    => Product::where('user_id', $userId)->selectRaw('SUM(quantity * price) as total')->value('total') ?? 0,
            'low_stock'      => Product::where('user_id', $userId)->where('quantity', '<', 10)->count(),
        ]);
    }
}