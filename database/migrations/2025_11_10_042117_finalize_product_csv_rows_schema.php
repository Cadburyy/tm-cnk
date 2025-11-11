<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('item_number');
            $table->string('item_description');
            $table->date('effective_date');
            $table->integer('bulan');
            $table->decimal('loc_qty_change', 10, 2)->nullable();
            $table->string('unit_of_measure')->nullable();
            $table->string('remarks')->nullable();
            $table->string('item_group')->nullable();
            $table->string('dept')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
