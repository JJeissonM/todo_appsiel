<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToArticlesetup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_articlesetups', function (Blueprint $table) {
            $table->unsignedInteger('article_id')->nullable()->after('widget_id');
            $table->foreign('article_id')->references('id')->on('pw_articles')->onDelete('CASCADE');
            $table->unsignedInteger('articlecategory_id')->nullable()->after('article_id');
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
        Schema::table('pw_articlesetups', function (Blueprint $table) {
            $table->dropColumn('articlecategory_id');
            $table->dropColumn('article_id');
        });
    }
}
