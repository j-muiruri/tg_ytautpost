<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Alaouy\Youtube\Facades\Youtube;

class YoutubeApiController extends Controller
{
    /**
     * Get Channel ID
     * @return void
     */
    public function getChannelById() {

        $response = Youtube::getChannelById('UCM4u1oRyJGsMNiR-mMwl3_A');
        return response()->json($response);
    }

    /**
     * Playlist By ID
     */
    public function getPlaylistById() {

        $response= Youtube::getPlaylistById('PL590L5WQmH8fJ54F369BLDSqIwcs-TCfs');

        return response()->json($response);
    }

    /**
     * Playlists by Channel ID
     */
    public function getPlaylistByChannelId() {

        $response = Youtube::getPlaylistsByChannelId('UCM4u1oRyJGsMNiR-mMwl3_A');
        
        return response()->json($response);
    }

}
