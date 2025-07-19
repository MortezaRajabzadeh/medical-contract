<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // افزودن ایندکس برای کلیدهای خارجی و فیلدهای پرکاربرد - با بررسی وجود قبلی
            
            // بررسی وجود ایندکس medical_center_id
            $indexExists = collect(DB::select("SHOW INDEX FROM contracts WHERE Column_name='medical_center_id' AND Key_name='contracts_medical_center_id_index'"))->isNotEmpty();
            if (!$indexExists) {
                $table->index('medical_center_id');
            }
            
            // بررسی وجود ایندکس created_by
            $indexExists = collect(DB::select("SHOW INDEX FROM contracts WHERE Column_name='created_by' AND Key_name='contracts_created_by_index'"))->isNotEmpty();
            if (!$indexExists) {
                $table->index('created_by');
            }
            
            // بررسی وجود ایندکس approved_by
            $indexExists = collect(DB::select("SHOW INDEX FROM contracts WHERE Column_name='approved_by' AND Key_name='contracts_approved_by_index'"))->isNotEmpty();
            if (!$indexExists) {
                $table->index('approved_by');
            }
            
            // بررسی وجود ایندکس status
            $indexExists = collect(DB::select("SHOW INDEX FROM contracts WHERE Column_name='status' AND Key_name='contracts_status_index'"))->isNotEmpty();
            if (!$indexExists) {
                $table->index('status');
            }
            
            // بررسی وجود ایندکس contract_type
            $indexExists = collect(DB::select("SHOW INDEX FROM contracts WHERE Column_name='contract_type' AND Key_name='contracts_contract_type_index'"))->isNotEmpty();
            if (!$indexExists) {
                $table->index('contract_type');
            }
            
            // بررسی وجود ایندکس ترکیبی medical_center_id و status
            $indexExists = collect(DB::select("SHOW INDEX FROM contracts WHERE Key_name='contracts_medical_center_id_status_index'"))->isNotEmpty();
            if (!$indexExists) {
                $table->index(['medical_center_id', 'status']);
            }
            
            // بررسی وجود ایندکس ترکیبی start_date و end_date
            $indexExists = collect(DB::select("SHOW INDEX FROM contracts WHERE Key_name='contracts_start_date_end_date_index'"))->isNotEmpty();
            if (!$indexExists) {
                $table->index(['start_date', 'end_date']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            //
        });
    }
};
