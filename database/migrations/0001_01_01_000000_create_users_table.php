<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->integer('verification_code')->nullable();
            $table->timestamp('verification_code_generated_at')->nullable();
            $table->integer('level')->default(1)->comment('0 = Limitado | 1 = Usuário | 2 = Revisor | 3 = Administrador | 4 = Super Administrador');
            $table->string('nickname', 20);
            $table->string('photo')->nullable();
            $table->date('birth_date')->nullable();
            $table->longText('biography')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('association')->nullable();
            $table->boolean('is_pathfinder')->nullable();
            $table->string('pathfinder_club')->nullable();
            $table->boolean('is_adventurer')->nullable();
            $table->string('adventurer_club')->nullable();

            $table->boolean('notify_new_user')->default(false);
            $table->boolean('notify_active_user')->default(false);
            $table->boolean('notify_update_user')->default(false);
            $table->boolean('notify_update_page')->default(false);

            $table->datetime('change_data_at')->nullable();
            $table->datetime('last_login_at')->nullable();
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
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
