<?php

namespace App\Http\Controllers;

use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Google_Client;
use Google_Service_YouTube;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Arr;
use App\YoutubeVideos;

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

        $client->setApprovalPrompt('force');

        $client->setLoginHint(env('LOGIN_HINT'));

        //Callback URL
        if (env('APP_ENV') === 'local') {
            $redirect_uri = URL::current();
        } else {

            $redirect_uri = env('APP_URL');

            // return   print($redirect_uri);
        }


        $redirect_uri = URL::current();
        //set redirect URL
        $client->setRedirectUri($redirect_uri);

        return $client;
    }

    /**
     * Get User Playlists
     */
    public function getPlaylists(Request $request)
    {
        // $callback = "";
        //check if auth code returned
        $code = $request->input('code');

        $client = $this->getAuthGoogleApi();

        $fileExists = Storage::disk('private')->exists(env('TOKEN_FILE'));

        if (isset($code)) {

            $client->authenticate($code);
            // Google Client Object
            $accessToken = $client->getAccessToken();

            $client->setAccessToken($accessToken);

            //Save refresh Token to file
            Storage::disk('private')->put(env('TOKEN_FILE'),  json_encode($accessToken), 'private');

            //Init Service
            $service =  new Google_Service_YouTube($client);

            $queryParams = [
                'maxResults' => 25,
                'mine' => true
            ];

            $response = $service->playlists->listPlaylists('snippet,contentDetails', $queryParams);

            $response = response()->json($response);

            return $response;
        } else if ($fileExists != false) {

            //check if file xists on the disk
            $file = Storage::disk('private')->get(env('TOKEN_FILE'));

            $client->setAccessToken($file);

            /* Refresh token when expired */
            if ($client->isAccessTokenExpired()) {

                $newAccessToken = $client->getAccessToken();

                //append new refresh token to new accestoken
                $newAccessToken['refresh_token'] = $client->getRefreshToken();

                Storage::disk('private')->put(env('TOKEN_FILE'),  json_encode($newAccessToken), 'private');
            }
            //Init Service
            $service =  new Google_Service_YouTube($client);

            $queryParams = [
                'maxResults' => 25,
                'mine' => true
            ];

            $response = $service->playlists->listPlaylists('snippet,contentDetails', $queryParams);

            $response = response()->json($response);

            return $response;
        }

        // create auth url
        $url = $client->createAuthUrl();

        return Redirect::intended($url);
    }

    /**
     * Get User Liked videos
     */
    public function getMyRated(Request $request)
    {
        // $callback = "";
        //check if auth code returned
        $code = $request->input('code');

        $client = $this->getAuthGoogleApi();

        $fileExists = Storage::disk('private')->exists(env('TOKEN_FILE'));

        if (isset($code)) {

            $client->authenticate($code);
            // Google Client Object
            $accessToken = $client->getAccessToken();

            $client->setAccessToken($accessToken);

            //Save refresh Token to file
            Storage::disk('private')->put(env('TOKEN_FILE'),  json_encode($accessToken), 'private');

            //Init Service
            $service =  new Google_Service_YouTube($client);


            $queryParams = [
                'myRating' => 'like'
            ];

            $response = $service->videos->listVideos('snippet,contentDetails', $queryParams);

            $response = response()->json($request);

            return $response;
        } else if ($fileExists != false) {

            //check if file xists on the disk
            $file = Storage::disk('private')->get(env('TOKEN_FILE'));

            $client->setAccessToken($file);

            /* Refresh token when expired */
            if ($client->isAccessTokenExpired()) {

                // the new access token comes with a refresh token as well
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

                $newAccessToken = $client->getAccessToken();

                //append new refresh token to new accestoken
                $newAccessToken['refresh_token'] = $client->getRefreshToken();

                Storage::disk('private')->put(env('TOKEN_FILE'),  json_encode($newAccessToken), 'private');
            }
            //Init Service
            $service =  new Google_Service_YouTube($client);


            $queryParams = [
                'myRating' => 'like',
                'maxResults' => 25
            ];

            $response = $service->videos->listVideos('snippet,contentDetails', $queryParams);

            $responseArray = (array) $response;
            // $responseJson = response()->json($request);
            $responseCollection = collect($response);

            // access items array/key from Google object reponse
            $items = $response->items;

            //pick only id, title and description


            $ids = json_encode(Arr::pluck($items, ['id']));

            $snippet = Arr::pluck($items, ['snippet']);
            // $title = Arr::collapse($snippet);

            $titles = json_encode(Arr::pluck($snippet, ['title']));
            $descs = json_encode(Arr::pluck($snippet, ['description']));

            foreach ($items as $t) {

                // $idtemp = "Yotube video Link is: https://youtube.com/watch?v=" . $t['id'] . "   Video Title: " . $t['snippet']['title'] . $t['snippet']['description'];
                // $results = print_r($idtemp);

                $link = "https://youtube.com/watch?v=";
                $results = YoutubeVideos::create(
                    [
                        'link' => $link.$t['id'],
                        'title' => $t['snippet']['title'],
                        'description' => $t['snippet']['description'],
                    ]
                );
            }

            // foreach ($descs as $d) {
            //         echo $d;
            // }
            // foreach ($ids as $i) {
            //         echo $i;
            // }


            // $mergeArray = echo $id ;

            return $results;
        }
        // create auth url
        $url = $client->createAuthUrl();

        return Redirect::intended($url);

        // return $client;
    }
}
