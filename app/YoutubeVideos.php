<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class YoutubeVideos extends Model
{
    //

    protected $fillable = ['link', 'title', 'description'];
}
