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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('verification_code')->nullable()->after('email_verified_at');
            $table->timestamp('verification_code_generated_at')->nullable()->after('verification_code');

            $table->integer('level')->default(1)->comment('0 = Limitado | 1 = Usuário | 2 = Revisor | 3 = Administrador | 4 = Super Administrador')->after('remember_token');
            $table->string('nickname')->after('level');
            $table->string('photo')->nullable()->after('nickname');
            $table->date('birth_date')->nullable()->after('photo');
            $table->longText('biography')->nullable()->after('birth_date');
            $table->string('city')->nullable()->after('biography');
            $table->string('state')->nullable()->after('city');
            $table->string('association')->nullable()->after('state');
            $table->boolean('is_pathfinder')->nullable()->after('association');
            $table->string('pathfinder_club')->nullable()->after('is_pathfinder');
            $table->boolean('is_adventurer')->nullable()->after('pathfinder_club');
            $table->string('adventurer_club')->nullable()->after('is_adventurer');
            $table->datetime('change_data_at')->nullable()->after('adventurer_club');
            $table->datetime('last_login_at')->nullable()->after('change_data_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('verification_code');
            $table->dropColumn('verification_code_generated_at');

            $table->dropColumn('level');
            $table->dropColumn('nickname');
            $table->dropColumn('photo');
            $table->dropColumn('birth_date');
            $table->dropColumn('biography');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('association');
            $table->dropColumn('is_pathfinder');
            $table->dropColumn('pathfinder_club');
            $table->dropColumn('is_adventurer');
            $table->dropColumn('adventurer_club');
            $table->dropColumn('change_data_at');
            $table->dropColumn('last_login_at');
        });
    }
};
