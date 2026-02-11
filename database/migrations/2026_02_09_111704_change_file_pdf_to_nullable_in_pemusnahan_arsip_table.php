<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('pemusnahan_arsip', function (Blueprint $table) {
            $table->string('file_pdf')->nullable()->change();
        });
    }
    public function down(): void
    {
        Schema::table('pemusnahan_arsip', function (Blueprint $table) {
            //
        });
    }
};
