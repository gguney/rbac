<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('roles')){
            // Create table for storing roles
            Schema::create('roles', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('priority')->unsigned()->nullable();
                $table->string('name')->unique();
                $table->string('display_name')->nullable();
                $table->string('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if(!Schema::hasTable('user_role')){
            // Create table for associating roles to users (Many-to-Many)
            Schema::create('user_role', function (Blueprint $table) {
                $table->integer('user_id')->unsigned();
                $table->integer('role_id')->unsigned();

                $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');

                $table->primary(['user_id', 'role_id']);
            });
        }

        if(!Schema::hasTable('permissions')){
            // Create table for storing permissions
            Schema::create('permissions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('action')->unique();
                $table->string('name')->nullable();
                $table->string('display_name')->nullable();
                $table->string('description')->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
        }

        if(!Schema::hasTable('role_permission')){
            // Create table for associating permissions to roles (Many-to-Many)
            Schema::create('role_permission', function (Blueprint $table) {
                $table->integer('permission_id')->unsigned();
                $table->integer('role_id')->unsigned();

                $table->foreign('permission_id')->references('id')->on('permissions')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');

                $table->primary(['role_id', 'permission_id']);
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('roles');
    }
}
