<?php

use Illuminate\Database\Schema\Blueprint;

class CreateBugsTable extends BaseMigration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('bugs', function (Blueprint $table) {
            $table->increments('id');

            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description');
            $table->text('body');
            $table->unsignedInteger('user_id');

            $table->foreign('user_id')
                ->references('id')->on('users');

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
        $this->schema->dropIfExists('bugs');
    }
}
