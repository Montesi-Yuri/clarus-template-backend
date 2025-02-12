<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Verifichiamo se le colonne esistono prima di aggiungerle
            if (!Schema::hasColumn('roles', 'tenant_id')) {
                $table->foreignId('tenant_id')->after('id')->constrained()->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('roles', 'display_name')) {
                $table->string('display_name')->after('guard_name');
            }
            
            if (!Schema::hasColumn('roles', 'description')) {
                $table->string('description')->nullable()->after('display_name');
            }
            
            if (!Schema::hasColumn('roles', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('description');
            }

            // Modifichiamo l'indice unique
            try {
                $table->dropUnique(['name', 'guard_name']);
            } catch (\Exception $e) {
                // L'indice potrebbe non esistere, ignoriamo l'errore
            }

            // Creiamo il nuovo indice
            if (!Schema::hasTable('roles') || !Schema::getConnection()->getSchemaBuilder()->hasIndex('roles', 'roles_name_guard_name_tenant_id_unique')) {
                $table->unique(['name', 'guard_name', 'tenant_id'], 'roles_name_guard_name_tenant_id_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Verifichiamo se le colonne esistono prima di rimuoverle
            if (Schema::hasColumn('roles', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
            
            if (Schema::hasColumn('roles', 'display_name')) {
                $table->dropColumn('display_name');
            }
            
            if (Schema::hasColumn('roles', 'description')) {
                $table->dropColumn('description');
            }
            
            if (Schema::hasColumn('roles', 'is_default')) {
                $table->dropColumn('is_default');
            }

            // Ripristiniamo l'indice originale
            try {
                $table->dropUnique('roles_name_guard_name_tenant_id_unique');
                $table->unique(['name', 'guard_name']);
            } catch (\Exception $e) {
                // Gli indici potrebbero non esistere, ignoriamo l'errore
            }
        });
    }
}; 