<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('source');
            $table->unsignedSmallInteger('recurring_interval')->nullable()->after('is_recurring');
            $table->date('next_date')->nullable()->after('recurring_interval');
            $table->foreignId('parent_id')->nullable()->constrained('expenses')->nullOnDelete()->after('next_date');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['is_recurring', 'recurring_interval', 'next_date', 'parent_id']);
        });
    }
};