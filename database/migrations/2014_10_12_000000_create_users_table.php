<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
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
            $table->string('fname');
            $table->string('onames')->nullable();
            $table->string('lname');
            $table->string('email')->unique();
            $table->date("dob")->nullable();
            $table->string("phone")->nullable();
            $table->text("address")->nullable();
            $table->foreignId("usertype_id")->constrained("usertypes")->cascadeOnUpdate()->cascadeOnDelete();
            $table->text("profilePic")->nullable();
            $table->boolean("isAdmin")->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string("email_code")->nullable();
            $table->string('password')->nullable();
            $table->boolean("isLogged")->default(false);
            $table->boolean("status")->default(true);
            $table->string("gender")->default("Female");
            $table->string("scn")->nullable();
            $table->string("year_of_call")->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->foreignId("title_id")->nullable()->constrained("titles")->cascadeOnUpdate();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
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
};
