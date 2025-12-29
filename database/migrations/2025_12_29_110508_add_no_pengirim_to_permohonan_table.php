<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('permohonan', function (Blueprint $table) {
        $table->string('no_pengirim')->nullable()->after('no_permohonan');
    });
}

public function down()
{
    Schema::table('permohonan', function (Blueprint $table) {
        $table->dropColumn('no_pengirim');
    });
}
};
