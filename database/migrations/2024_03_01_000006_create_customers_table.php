<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            // Prima verifichiamo che la tabella tenants esista
            if (!Schema::hasTable('tenants')) {
                Schema::create('tenants', function (Blueprint $table) {
                    $table->id();
                    $table->string('company_name');
                    $table->string('vat_number')->unique();
                    $table->string('address');
                    $table->string('city');
                    $table->string('postal_code');
                    $table->string('country');
                    $table->timestamps();
                });
            }

            // Poi creiamo la tabella customers
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['M', 'F', 'O'])->nullable();
            $table->boolean('is_individual')->default(false);
            $table->boolean('is_company')->default(false);
            $table->string('company_name')->nullable();
            $table->string('vat_number')->nullable(); // Partita IVA
            $table->string('fiscal_code')->nullable(); // Codice fiscale
            $table->string('sdi_code')->nullable(); // Codice SDI
            $table->boolean('is_private')->default(true);
            $table->boolean('is_public')->default(false);
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('pec')->nullable();
            $table->string('website')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            // Aggiungiamo un indice invece del vincolo unique
            $table->index(['tenant_id', 'vat_number', 'deleted_at']);
        });
    }
}; 