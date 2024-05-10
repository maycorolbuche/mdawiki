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
        Schema::create('page_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id')->nullable();
            $table->unsignedBigInteger('restore_id')->nullable();
            $table->string('title');
            $table->string('name');
            $table->string('slug');
            $table->longText('content_editor')->nullable();
            $table->longText('content_preview')->nullable();
            $table->longText('content_text')->nullable();
            $table->longText('content_markdown')->nullable();
            $table->longText('content_pdf')->nullable();
            $table->boolean('pending_update')->default(0);

            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('page_id')->references('id')->on('pages')->onDelete('set null');
            $table->foreign('restore_id')->references('id')->on('page_histories')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_histories');
    }
};
