<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('medical_center_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('employee_id')->nullable()->unique();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->date('hire_date')->nullable();
            $table->enum('user_type', ['admin', 'medical_staff', 'administrative_staff'])->default('medical_staff');
            $table->json('contact_details')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->index(['medical_center_id', 'is_active']);
            $table->index(['user_type', 'is_active']);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['medical_center_id']);
            $table->dropColumn([
                'medical_center_id',
                'employee_id',
                'department',
                'position',
                'hire_date',
                'user_type',
                'contact_details',
                'last_login_at',
                'is_active'
            ]);
            $table->dropIndex(['medical_center_id_is_active']);
            $table->dropIndex(['user_type_is_active']);
        });
    }
};
