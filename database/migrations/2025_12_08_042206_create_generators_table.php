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
        Schema::create('generators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('model_name');
            $table->string('table_name')->nullable();
            $table->string('primary_key')->default('id');
            $table->string('primary_key_type')->default('int');
            $table->boolean('timestamps')->default(true);
            $table->boolean('soft_deletes')->default(false);
            $table->json('fields');
            $table->json('relationships')->nullable();
            $table->json('query_conditions')->nullable();
            $table->boolean('generate_migration')->default(true);
            $table->boolean('generate_model')->default(true);
            $table->boolean('generate_request')->default(true);
            $table->boolean('generate_seeder')->default(false);
            $table->boolean('generate_resource')->default(true);
            $table->boolean('generate_views')->default(true);
            $table->enum('status', ['pending', 'generated', 'failed'])->default('pending');
            $table->text('generated_files')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generators');
    }
};
