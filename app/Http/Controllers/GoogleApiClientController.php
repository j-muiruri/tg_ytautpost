<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google_Client;
use Google_Service_YouTube;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

class GoogleApiClientController extends Controller
{
    /**
     * Perform Authentication
     */
    public function getAuthGoogleApi()
    {

        $client = new Google_Client();


        $client->setApplicationName('Autopost Telegram Bot');

        $client->setScopes(['https://www.googleapis.com/auth/youtube.readonly',]);

        //$client->setAuthConfig(env('CLIENT_SECRET')); 

        $client->setClientId(env('CLIENT_ID'));

        $client->setClientSecret(env('CLIENT_SECRET_PASS'));

        $client->setDeveloperKey(env('YOUTUBE_API_KEY', 'YOUR_API_KEY'));

        $client->setAccessType('offline');

            //Redirect PAth or URL
            $redirect_uri = URL::current();

        $client->setRedirectUri($redirect_uri);

        // $url = $client->createAuthUrl();

        // // Exchange authorization code for an access token.

        // if (isset($_GET['code'])) {

        //     $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        //     $client->setAccessToken($accessToken);

        //     return response()->json($accessToken);
        // }
        // return Redirect::intended($url);
    }

    /**
     * Get Playlists
     */
    public function getPlaylists()
    {
        // Define service object for making API requests.

        $this->getAuthGoogleApi();
        // $client = new Google_Client();
        $this->getAuthGoogleApi()->client;

        if (isset($_GET['code'])) {

            $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

            $client->setAccessToken($accessToken);

            return $accessToken;
        }
        return Redirect::intended($client->createAuthUrl());


        //$client = new Google_Client();

        $service = new Google_Service_YouTube($accessToken);

        $queryParams = [
            'maxResults' => 25,
            'mine' => true
        ];

        $response = $service->playlists->listPlaylists('snippet,contentDetails', $queryParams);

        return response()->json($response);
    }
}
