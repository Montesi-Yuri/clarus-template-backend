<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade'); // customer è colui che riceve la fattura
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade'); // tenant è colui che emette la fattura e utilizza il software
            $table->string('number');
            $table->date('date');
            $table->date('due_date');
            $table->decimal('total', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue']);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indici per ottimizzare le query comuni
            $table->index(['tenant_id', 'date']);
            $table->index(['status']);
            $table->unique(['tenant_id', 'number']); // Numero fattura univoco per tenant
        });
    }
}; 