<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generators', function (Blueprint $table) {
            $table->json('table_columns')->nullable()->after('query_conditions');
        });
    }

    public function down(): void
    {
        Schema::table('generators', function (Blueprint $table) {
            $table->dropColumn('table_columns');
        });
    }
};
