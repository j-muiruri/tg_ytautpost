<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscribers extends Model
{
    //

    protected $fillable = ['user_id', 'chat_id', 'username', 'firstname', 'access_tokens'];
}
