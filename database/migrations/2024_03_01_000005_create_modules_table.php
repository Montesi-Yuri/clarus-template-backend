<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();  // es: 'customers', 'invoices'
            $table->string('display_name');    // es: 'Gestione Clienti', 'Fatturazione'
            $table->text('description')->nullable();
            $table->boolean('is_core')->default(false);  // moduli base non disattivabili
            $table->string('icon')->nullable();
            $table->boolean('enabled')->default(true);
            $table->integer('order')->default(0);
            $table->string('requires_permission')->nullable(); // permesso richiesto per accedere al modulo
            $table->timestamps();
        });

        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->date('valid_until')->nullable();  // per gestire le scadenze dei moduli
            $table->timestamps();

            $table->unique(['tenant_id', 'module_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenant_modules');
        Schema::dropIfExists('modules');
    }
}; 