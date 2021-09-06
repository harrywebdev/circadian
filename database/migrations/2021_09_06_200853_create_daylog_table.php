<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDaylogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daylog', function (Blueprint $table) {
            $table->id();
            $table->date('log_date');
            $table->string('feels')->nullable();
            $table->dateTime('wake_at')->nullable();
            $table->dateTime('first_meal_at')->nullable();
            $table->dateTime('last_meal_at')->nullable();
            $table->dateTime('sleep_at')->nullable();
            $table->boolean('has_alcohol')->nullable();
            $table->boolean('has_alcohol_in_evening')->nullable();
            $table->boolean('has_smoked')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daylog');
    }
}
