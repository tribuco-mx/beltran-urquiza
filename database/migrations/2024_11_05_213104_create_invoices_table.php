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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');  // Foreign Key to Customer
            $table->foreignId('company_id')->nullable();  // Foreign Key to Company
            $table->date('transaction_date');  // Transaction Date
            $table->string('transaction_ref')->unique();  // Transaction Ref. #
            $table->string('order_id')->unique();  // Order ID
            $table->string('service_purchased');  // Service(s) Purchased
            $table->integer('quantity');  // Quantity
            $table->decimal('amount_without_gct', 10, 2);  // Amount w/out GCT
            $table->decimal('gct', 10, 2);  // GCT
            $table->decimal('amount_with_gct', 10, 2);  // Amount w/ GCT
            $table->string('currency', 3);  // Currency
            $table->text('pdf_file')->nullable();  // PDF File
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
