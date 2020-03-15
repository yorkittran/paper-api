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
            $table->datetime('start_at');
            $table->datetime('end_at');
            $table->integer('status');
            $table->string('description_assigned');
            $table->string('description_committed')->nullable();
            $table->string('comment')->nullable();
            $table->float('mark')->nullable();
            $table->datetime('evaluated_at')->nullable();
            $table->datetime('committed_at')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->unsignedBigInteger('old_task')->nullable();
            $table->unsignedBigInteger('assigner_id')->nullable();
            $table->unsignedBigInteger('assignee_id');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->unsignedBigInteger('commenter_id')->nullable();
            $table->unsignedBigInteger('creator_id');
            $table->unsignedBigInteger('updater_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('old_task')->references('id')->on('tasks');
            $table->foreign('assigner_id')->references('id')->on('users');
            $table->foreign('assignee_id')->references('id')->on('users');
            $table->foreign('approver_id')->references('id')->on('users');
            $table->foreign('commenter_id')->references('id')->on('users');
            $table->foreign('creator_id')->references('id')->on('users');
            $table->foreign('updater_id')->references('id')->on('users');

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
