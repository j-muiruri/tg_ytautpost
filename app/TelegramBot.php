<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelegramBot extends Model
{
    //
    protected $fillable = [ 'update_id','user_id','username','chat_id', 'chat_type', 'message_id','message','message_type',];
}
