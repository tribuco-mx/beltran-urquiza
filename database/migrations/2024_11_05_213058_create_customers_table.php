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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // Billed To
            $table->string('tax_id')->nullable();  // Tax ID
            $table->string('billing_address')->nullable();  // Billing Address
            $table->string('province')->nullable();  // Province
            $table->string('email');  // Client Email
            $table->string('payment_method')->nullable();  // Payment Method
            $table->string('cardholder')->nullable();  // Cardholder
            $table->string('card_type')->nullable();  // Card Type
            $table->string('card_last4')->nullable();  // Last 4 Digits
            $table->string('card_exp_date')->nullable();  // Exp. Date of Card
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
