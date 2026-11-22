<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up()
    {
        Schema::create('sms_histories', function (Blueprint $table) {
            $table->id();
            $table->string('recived_numbers', 15);
            $table->string('sender_id', 15);
            $table->string('text_body' );
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('sms_histories');
    }
};
