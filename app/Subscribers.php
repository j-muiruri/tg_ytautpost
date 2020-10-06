<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscribers extends Model
{
    //

    protected $fillable = ['chat_id', 'username', 'firstname'];
}
