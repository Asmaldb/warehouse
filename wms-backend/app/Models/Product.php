
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'quantity',   // موجودی فعلی — توسط InventoryTransactionController به‌روز می‌شود
        'price',
        'description',
        'user_id',
    ];

    protected $casts = [
        'price'    => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    // ---------------------------------------------------------------
    // Relations
    // ---------------------------------------------------------------
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inventoryTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InventoryTransaction::class)->latest();
    }

    // ---------------------------------------------------------------
    // Helper: اعمال رویداد روی موجودی
    // ---------------------------------------------------------------
    public function applyTransaction(string $type, float $qty): float
    {
        $current = (float) $this->quantity;

        if (in_array($type, InventoryTransaction::$increaseTypes)) {
            $new = $current + $qty;
        } elseif (in_array($type, InventoryTransaction::$decreaseTypes)) {
            $new = $current - $qty;
        } else {
            // adjustment و transfer: qty می‌تواند مثبت یا منفی باشد
            $new = $current + $qty;
        }

        $this->update(['quantity' => max(0, $new)]);
        return (float) $this->fresh()->quantity;
    }
}
