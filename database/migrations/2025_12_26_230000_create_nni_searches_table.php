<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('nni_searches', function (Blueprint $table) {
            $table->id();
            $table->string('nni', 20);
            $table->string('nom_fr')->nullable();
            $table->string('prenom_fr')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance_fr')->nullable();
            $table->string('ip')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('nni_searches');
    }
};
