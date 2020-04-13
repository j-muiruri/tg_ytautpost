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

        $client->setLoginHint(env('LOGIN_HINT'));

        session_start();

        //Redirect PAth or URL

        //$redirect_uri = $request->input('return');

        // $client->setRedirectUri($redirect_uri);


        // $url = $client->createAuthUrl();

        // Exchange authorization code for an access token.
        // if (isset($_SESSION['access_token'])) {

        //     echo $_SESSION['access_token'];
        // }
        return $client;
    }

    /**
     * Get User Playlists
     */
    public function getPlaylists(Request $request)
    {
        //Callback URL
        $redirect_uri = URL::current();
        // Google Client Object
        $client = $this->getAuthGoogleApi();
        //rset Callback
        $client->setRedirectUri($redirect_uri);
        //Init Service
        $service = new Google_Service_YouTube($client);
        // create auth url
        $url = $client->createAuthUrl();


        //check if auth code returned
        $code = $request->input('code');

        if (isset($code)) {
            // authenticate then  get access and Refresh token
            $client->authenticate($code);

            $accessToken = $client->getAccessToken();

            $client->setAccessToken($accessToken);

            $refreshToken = $client->getRefreshToken();

            //Save refresh Token

            session_start();
            $_SESSION['refresh_token'] = $refreshToken;

            $queryParams = [
                'maxResults' => 25,
                'mine' => true
            ];

            $response = $service->playlists->listPlaylists('snippet,contentDetails', $queryParams);

            return response()->json($response);
        } else if (isset($_SESSION['refresh_token'])) {
            $client = new Google_Client();

            // when the session Exists containing refresh tokens for offline use
            $client->fetchAccessTokenWithRefreshToken($_SESSION['refresh_token']);

            $queryParams = [
                'maxResults' => 25,
                'mine' => true
            ];

            $response = $service->playlists->listPlaylists('snippet,contentDetails', $queryParams);

            return response()->json($response);
        } else {

            return Redirect::intended($url);
        }
    }
}
