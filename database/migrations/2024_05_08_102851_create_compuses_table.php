<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->longText('telegram')->nullable();
            $table->string('facebook_name')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('google_map_url')->nullable();
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->string('admission_image')->nullable();
            $table->integer('status')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compuses');
    }
}
