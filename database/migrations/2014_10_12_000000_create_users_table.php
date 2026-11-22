<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('join_date')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('status')->nullable();
            $table->string('role')->default('employee');
            $table->string('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->string('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->foreignId('branch_id')->nullable();
            $table->foreignId('department_id')->nullable();
            $table->foreignId('designation_id')->nullable();
            $table->timestamps();
        });
        User::create([
            'id' => 1,
            'name' => "Admin",
            'email' => "superadmin@prohrbd.com" ,
            'role' => 'super_admin',
            'password'  => Hash::make("ArifDevProSoft2021"),
        ]);
        User::create([
            'id' => 1000,
            'name' => "Admin",
            'email' => "superadmin@" . str_replace("https://", "", config('app.url')),
            'role' => 'super_admin',
            'password'  => Hash::make("12345678"),
        ]);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
