<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeachingInterestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teaching_interests', function (Blueprint $table) {
            $table->string('attribute_id');
            $table->string('title');
            $table->string('short_name')->nullable();
            $table->string('parent_attribute_id')->nullable();
            $table->string('hiearchy')->nullable();
            $table->integer('count')->unsigned()->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('teaching_interests');
    }
}
