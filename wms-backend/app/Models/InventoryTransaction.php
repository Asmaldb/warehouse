<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'transaction_type',
        'quantity',
        'unit_price',
        'total_value',
        'balance_after',
        'reference_number',
        'party_name',
        'description',
    ];

    protected $casts = [
        'quantity'      => 'decimal:2',
        'unit_price'    => 'decimal:2',
        'total_value'   => 'decimal:2',
        'balance_after' => 'decimal:2',
        'created_at'    => 'datetime',
    ];

    // رویدادهایی که موجودی را افزایش می‌دهند (+)
    public static array $increaseTypes = ['receipt', 'return_in', 'initial'];

    // رویدادهایی که موجودی را کاهش می‌دهند (−)
    public static array $decreaseTypes = ['issue', 'return_out', 'scrap'];

    public static array $typeLabels = [
        'receipt'    => 'ورود به انبار',
        'issue'      => 'خروج از انبار',
        'return_in'  => 'مرجوعی از مشتری',
        'return_out' => 'مرجوعی به تأمین‌کننده',
        'transfer'   => 'انتقال داخلی',
        'adjustment' => 'تعدیل موجودی',
        'scrap'      => 'ضایعات',
        'initial'    => 'موجودی اولیه',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
