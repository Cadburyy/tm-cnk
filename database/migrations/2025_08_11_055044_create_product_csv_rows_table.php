<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_csv_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('order')->nullable();
            $table->string('supplier')->nullable();
            $table->string('internal_reference')->nullable();
            $table->string('item_number')->nullable();
            $table->string('description')->nullable();
            $table->string('description2')->nullable();
            $table->integer('quantity_ordered')->nullable();
            $table->string('unit_of_measure')->nullable();
            $table->decimal('po_cost', 10, 2)->nullable();
            $table->string('currency')->nullable();
            $table->boolean('taxable')->nullable();
            $table->string('tax_class')->nullable();
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->date('receipt_date')->nullable();
            $table->string('external_reference')->nullable();
            $table->date('transaction_date')->nullable();
            $table->integer('receipt_quantity')->nullable();
            $table->decimal('receipt_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_csv_rows');
    }
};
