<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatuscoltoTelegramBotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telegram_bots', function (Blueprint $table) {
            $table->string('status')->default('pending');
            //status could be pending, complete, processing, failed
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });
    }
}
