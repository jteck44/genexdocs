<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // nullable() : un rapport peut exister sans client rattaché
            // (cas rare, mais on ne veut pas bloquer la création pour ça).
            // after('report_type_id') : purement esthétique dans la table.
            $table->foreignId('client_id')->nullable()
                  ->after('report_type_id')
                  ->constrained()
                  ->nullOnDelete(); // si un client est supprimé, ses rapports restent, juste "sans client"
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
        });
    }
};