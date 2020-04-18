<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramBot extends Model
{
    //
    protected $fillable = ['chat_id', 'message'];
}
