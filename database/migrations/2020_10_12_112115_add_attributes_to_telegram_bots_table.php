<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttributesToTelegramBotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telegram_bots', function (Blueprint $table) {
            $table->string('update_id');
            $table->string('user_id');
            $table->string('username');
            $table->string('chat_type');
            $table->string('message_id');
            $table->string('message_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('telegram_bots', function (Blueprint $table) {
            $table->dropColumn(['update_id']);
            $table->dropColumn(['user_id']);
            $table->dropColumn(['username']);
            $table->dropColumn(['chat_type']);
            $table->dropColumn(['message_id']);
            $table->dropColumn(['message_type']);
        });
    }
}
