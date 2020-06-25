<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCategoriaArticleToArticle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_articles', function (Blueprint $table) {
            $table->dropForeign('pw_articles_articlesetup_id_foreign');
            $table->dropColumn('articlesetup_id');
            $table->unsignedInteger('articlecategory_id')->nullable()->after('imagen');
            $table->foreign('articlecategory_id')->references('id')->on('pw_articlecategories')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_articles', function (Blueprint $table) {
            $table->dropColumn('articlecategorie_id');
        });
    }
}
