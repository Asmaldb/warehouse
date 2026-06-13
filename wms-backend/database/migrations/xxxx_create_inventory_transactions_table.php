<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();

            // ارتباط با کالا
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');

            // ارتباط با کاربر ثبت‌کننده
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            /*
             * نوع رویداد (transaction_type):
             *   receipt    = ورود به انبار (خرید / دریافت از تأمین‌کننده)
             *   issue      = خروج از انبار (تحویل به مشتری / مصرف داخلی)
             *   return_in  = مرجوعی از مشتری (برگشت کالا به انبار)
             *   return_out = مرجوعی به تأمین‌کننده (ارسال کالا به تأمین‌کننده)
             *   transfer   = انتقال بین مکان‌ها (داخلی)
             *   adjustment = تعدیل موجودی (اصلاح بعد از انبارگردانی)
             *   scrap      = ضایعات (کالای فاسد / خراب / منسوخ)
             *   initial    = موجودی اولیه (بار اول ثبت کالا)
             */
            $table->enum('transaction_type', [
                'receipt',
                'issue',
                'return_in',
                'return_out',
                'transfer',
                'adjustment',
                'scrap',
                'initial',
            ]);

            // تعداد این رویداد (همیشه مثبت — جهت را transaction_type مشخص می‌کند)
            $table->decimal('quantity', 12, 2);

            // قیمت واحد در زمان ثبت رویداد
            $table->decimal('unit_price', 14, 2)->default(0);

            // ارزش کل این رویداد (quantity × unit_price)
            $table->decimal('total_value', 16, 2)->default(0);

            // موجودی کالا بعد از این رویداد (مانده کاردکس)
            $table->decimal('balance_after', 12, 2)->default(0);

            // شماره سند / حواله / رسید
            $table->string('reference_number')->nullable();

            // نام طرف مقابل (تأمین‌کننده / مشتری / واحد داخلی)
            $table->string('party_name')->nullable();

            // توضیحات اضافی
            $table->text('description')->nullable();

            $table->timestamps();

            // ایندکس‌ها برای جستجوی سریع
            $table->index(['product_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('transaction_type');
            $table->index('reference_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
