<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Client extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function(Blueprint $table)
        {
            $table->increments('client_id');
            $table->string('surname');
            $table->string('first_name');
            $table->string('email')->unique();
            $table->int('mobile');
            $table->string('address');
            $table->string('state_of_residence');
            $table->string('employer_code');
            $table->string('next_of_kin_surname');
            $table->string('next_of_kin_first_name');
            $table->string('next_of_kin_mobile');
            $table->string('next_of_kin_email');
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
        //
    }
}
