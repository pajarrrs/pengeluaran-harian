<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_inputs', function (Blueprint $table) {
            $table->id();
            $table->string('wa_id');
            $table->integer('amount')->nullable();
            $table->string('category_name')->nullable();
            $table->string('step'); // 'awaiting_category' | 'awaiting_amount'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_inputs');
    }
};
