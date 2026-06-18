<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('case_type_id')->nullable()->constrained('case_types')->onDelete('set null');
            $table->foreignId('case_status_id')->nullable()->constrained('case_statuses')->onDelete('set null');
            $table->string('title');
            $table->longText('description');
            $table->foreignId('lawyer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_date')->nullable();
            $table->timestamp('closed_date')->nullable();
            $table->string('court_name')->nullable();
            $table->string('judge_name')->nullable();
            $table->string('opponent_name')->nullable();
            $table->longText('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
