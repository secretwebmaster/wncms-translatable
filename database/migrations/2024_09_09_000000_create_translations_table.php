<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('translations')) {
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
        }else{
            // check if every column exists, if not create it
            Schema::table('translations', function (Blueprint $table) {
                if (!Schema::hasColumn('translations', 'translatable_type')) {
                    $table->string('translatable_type');
                }

                if (!Schema::hasColumn('translations', 'translatable_id')) {
                    $table->unsignedBigInteger('translatable_id');
                }

                if (!Schema::hasColumn('translations', 'field')) {
                    $table->string('field'); // The field being translated (e.g., title, description)
                }

                if (!Schema::hasColumn('translations', 'locale')) {
                    $table->string('locale'); // Language locale (e.g., en, fr)
                }

                if (!Schema::hasColumn('translations', 'value')) {
                    $table->text('value'); // The translated string
                }

                if (!Schema::hasColumn('translations', 'created_at') || !Schema::hasColumn('translations', 'updated_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('translations');
    }
}
