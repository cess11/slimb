<?php

use Illuminate\Database\Schema\Blueprint;

class CreateUserFavouriteTable extends BaseMigration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('user_favourite', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id');
            $table->unsignedInteger('bug_id');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('bug_id')
                ->references('id')->on('bugs')
                ->onDelete('cascade');

            $table->unique(['user_id', 'bug_id']);

            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('user_favourite');
    }
}
