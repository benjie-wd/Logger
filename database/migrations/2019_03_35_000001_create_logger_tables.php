<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoggerTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (config('database.default') !== 'mysql') {
            throw new \InvalidArgumentException("MySQL is the only supported driver for this package.");
        }

        Schema::create('audit_routes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('route');
            $table->string('route_hash', 32)->unique();
        });

        Schema::create('audit_keys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('data');
            $table->string('hash', 32)->unique();
        });

        Schema::create('audit_activity', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('route_id', false, true);
            $table->bigInteger('key_id', false, true);
            $table->integer('user_id', false, true)->nullable(true);

            $table->tinyInteger('type', false, true);
            $table->timestamps();

            $table
                ->foreign('user_id')
                ->references(config('activity-logger.user.foreign_key', 'id'))
                ->on(config('activity-logger.user.table', 'users'))
                ->onDelete('RESTRICT');

            $table
                ->foreign('route_id')
                ->references('id')
                ->on('audit_routes')
                ->onDelete('RESTRICT');
        });

        DB::statement('ALTER TABLE `audit_activity` ADD `ip_address` VARBINARY(16) AFTER `type`');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::drop('audit_routes');
        Schema::drop('audit_keys');
        Schema::drop('audit_activity');
        Schema::enableForeignKeyConstraints();
    }
}