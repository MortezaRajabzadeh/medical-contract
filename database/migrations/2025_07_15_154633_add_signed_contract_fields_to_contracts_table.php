<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // اضافه کردن فیلدهای مربوط به قرارداد امضا شده
            $table->string('signed_file_path')->nullable()->comment('مسیر فایل امضا شده');
            $table->timestamp('signed_date')->nullable()->comment('تاریخ امضای قرارداد');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // حذف فیلدهای مربوط به قرارداد امضا شده
            $table->dropColumn([
                'signed_file_path',
                'signed_date'
            ]);
        });
    }
};
