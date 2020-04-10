<?php

namespace App\Http\Controllers;

use Telegram;

class TelegramBotController extends Controller
{
    /**
     * Get Updates(Messages from users or input)
     */
    public function updatedActivity()
    {
        $activity = Telegram::getUpdates();
        return response()->json($activity);
    }
    /**
     * Run Commands (eg. /start, /help)
     */
    public function runCommands()
    {
        $update = Telegram::commandsHandler(false, ['timeout' => 30]);
        return response()->json(['status' => 'success']);
    }
}
