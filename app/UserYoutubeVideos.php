<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserYoutubeVideos extends Model
{
    use HasFactory;

    protected $fillable = ['link', 'title', 'description', 'username'];
}
