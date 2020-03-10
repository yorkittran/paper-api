<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->string('status', 1)->default('0');
            $table->string('description_assigned');
            $table->string('description_committed')->nullable();
            $table->string('comment')->nullable();
            $table->float('mark')->nullable();
            $table->datetime('evaluated_at')->nullable();
            $table->datetime('committed_at')->nullable();
            $table->datetime('approvied_at')->nullable();
            $table->unsignedBigInteger('old_task')->nullable();
            $table->unsignedBigInteger('user_created_id');
            $table->unsignedBigInteger('user_assigned_id');
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('old_task')->references('id')->on('tasks');
            $table->foreign('user_created_id')->references('id')->on('users');
            $table->foreign('user_assigned_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
