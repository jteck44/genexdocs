<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clients') && ! Schema::hasTable('mandants')) {
            Schema::rename('clients', 'mandants');
        }

        if (Schema::hasTable('reports') && Schema::hasColumn('reports', 'client_id')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->renameColumn('client_id', 'mandant_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reports') && Schema::hasColumn('reports', 'mandant_id')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->renameColumn('mandant_id', 'client_id');
            });
        }

        if (Schema::hasTable('mandants') && ! Schema::hasTable('clients')) {
            Schema::rename('mandants', 'clients');
        }
    }
};