<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('two_factor_enabled')->after('password')->default(false);
            $table->text('two_factor_secret')->after('two_factor_enabled')->nullable();
            $table->timestamp('two_factor_confirmed_at')->after('two_factor_secret')->nullable();
            $table->string('mobile_number', 15)->after('email')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_enabled', 'two_factor_secret', 'two_factor_confirmed_at', 'mobile_number']);
        });
    }
};
