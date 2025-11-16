<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translatable_type');
            $table->unsignedBigInteger('translatable_id');
            $table->string('field'); // The field being translated (e.g., title, description)
            $table->string('locale'); // Language locale (e.g., en, fr)
            $table->text('value'); // The translated string
            $table->timestamps();

            $table->index(['translatable_type', 'translatable_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('translations');
    }
}
