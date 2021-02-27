<?php

namespace App\Http\Controllers;

use App\MySubscriptions;
use App\Subscribers;
use App\YoutubeVideos;
use Facade\FlareClient\Http\Response;
use Google_Client;
use Google_Service_YouTube;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteUri;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Arr;

/**
 * The Google API  Class
 * @author John Muiruri  <jontedev@gmail.com>
 *
 */
class GoogleApiClientController extends Controller
{
    /**
     * Perform Authentication with OAuth 2.0
     */
    public function getAuthGoogleApi()
    {
        $client = new Google_Client();

        $client->setApplicationName('Selecta Youtube Autopost Telegram Bot');

        $client->setScopes(['https://www.googleapis.com/auth/youtube.readonly']);

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
            $client->fetchAccessTokenWithAuthCode($code);
            // Google Client Object
            $accessToken = $client->getAccessToken();

            $client->setAccessToken($accessToken);

            //Save refresh Token to file
            Storage::disk('private')->put(env('TOKEN_FILE'), json_encode($accessToken), 'private');

            //Init Service
            $service = new Google_Service_YouTube($client);

            $queryParams = [
                'maxResults' => 25,
                'mine'       => true,
            ];

            $response = $service->playlists->listPlaylists('snippet,contentDetails', $queryParams);

            $response = response()->json($response);

            return $response;
        } elseif ($fileExists != false) {

            //check if file exists on the disk
            $file = Storage::disk('private')->get(env('TOKEN_FILE'));

            $client->setAccessToken($file);

            /* Refresh token when expired */
            if ($client->isAccessTokenExpired()) {
                $newAccessToken = $client->getAccessToken();

                //append new refresh token to new accestoken
                $newAccessToken['refresh_token'] = $client->getRefreshToken();

                Storage::disk('private')->put(env('TOKEN_FILE'), json_encode($newAccessToken), 'private');
            }
            //Init Service
            $service = new Google_Service_YouTube($client);

            $queryParams = [
                'maxResults' => 25,
                'mine'       => true,
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

        $pageToken = $request->input('next');

        $client = $this->getAuthGoogleApi();

        $fileExists = Storage::disk('private')->exists(env('TOKEN_FILE'));

        if (isset($code)) {
            $client->fetchAccessTokenWithAuthCode($code);
            // Google Client Object
            $accessToken = $client->getAccessToken();

            $client->setAccessToken($accessToken);

            //Save refresh Token to file
            Storage::disk('private')->put(env('TOKEN_FILE'), json_encode($accessToken), 'private');

            //Init Service
            $service = new Google_Service_YouTube($client);

            $queryParams = [
                'myRating' => 'like',
            ];

            $response = $service->videos->listVideos('snippet,contentDetails', $queryParams);

            $response = response()->json($request);


            echo $response;
            sleep(10);
            return redirect('my-rated');
        } elseif ($fileExists != false) {

            //check if file xists on the disk
            $file = Storage::disk('private')->get(env('TOKEN_FILE'));

            //Get Our access token
            $client->setAccessToken($file);

            /* Refresh token when expired */
            if ($client->isAccessTokenExpired()) {

                // the new access token comes with a refresh token as well
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

                $newAccessToken = $client->getAccessToken();

                //append new refresh token to new accestoken
                $newAccessToken['refresh_token'] = $client->getRefreshToken();

                Storage::disk('private')->put(env('TOKEN_FILE'), json_encode($newAccessToken), 'private');
            }
            //Init Service
            $service = new Google_Service_YouTube($client);

            $queryParams = [
                'myRating'   => 'like',
                'maxResults' => 50,
            ];

            if (isset($pageToken)) {
                //nextpagetoken set
                $queryParams['pageToken'] = $pageToken;
            }
            $response = $service->videos->listVideos('snippet,contentDetails', $queryParams);

            // $responseArray = (array) $response;
            // $responseJson = response()->json($request);
            // $responseCollection = collect($response);

            // access items array/key from Google object reponse
            $items = $response->items;

            //pick only id, title and description
            // $ids = json_encode(Arr::pluck($items, ['id']));

            // $snippet = Arr::pluck($items, ['snippet']);

            // $titles = json_encode(Arr::pluck($snippet, ['title']));
            // $descs = json_encode(Arr::pluck($snippet, ['description']));

            $entries = 0;
            $exists  = 0;

            foreach ($items as $t) {

                // $idtemp = "Yotube video Link is: https://youtube.com/watch?v=" . $t['id'] . "   Video Title: " . $t['snippet']['title'] . $t['snippet']['description'];
                // $results = print_r($idtemp);

                $link = "https://youtube.com/watch?v=";

                $id = $link . $t['id'];

                // Check if the video link exists on the server
                $idExists = YoutubeVideos::where('link', '=', $id)->first();

                if ($idExists === null) {
                    // video link doesn't exist in db

                    //insert video to db
                    YoutubeVideos::create(
                        [
                            'link'        => $link . $t['id'],
                            'title'       => $t['snippet']['title'],
                            'description' => $t['snippet']['description'],
                        ]
                    );

                    $results = $id . "  Inserted\n";

                    $entries++;

                    print_r(json_encode($results));
                } else {
                    $results = $id . "  Exists!\n";

                    $exists++;

                    print_r(json_encode($results));
                }
            }

            //Check no of inserted entries, if less than 2, redirect automatically and fetch more videos
            if ($entries < 2) {
                $Url = URL::current();

                $tokenInput = $response->nextPageToken;

                echo "Records Inserted: " . $entries . "  Entries Skipped: " . $exists;

                echo "Redirect to Next Page in 5 seconds......... ";
                sleep(10);
                return Redirect::intended($Url . "?next=" . $tokenInput);
            } else {
                // $mergeArray = echo $id ;
                // $response = $response->nextPageToken;
                // $response = response()->json($response);

                return var_dump("Records Inserted: " . $entries . "  Entries Skipped: " . $exists);

                // return $results;
            }
        }
        // create auth url
        $url = $client->createAuthUrl();

        return Redirect::intended($url);

        // return $client;
    }

    /**
     * Get User Subscribed Channels
     */
    public function getMySubscriptions(Request $request)
    {
        // $callback = "";
        //check if auth code returned
        $code = $request->input('code');

        $pageToken = $request->input('next');

        $client = $this->getAuthGoogleApi();

        $fileExists = Storage::disk('private')->exists(env('TOKEN_FILE'));

        if (isset($code)) {
            $client->fetchAccessTokenWithAuthCode($code);
            // Google Client Object
            $accessToken = $client->getAccessToken();

            $client->setAccessToken($accessToken);

            //Save refresh Token to file
            Storage::disk('private')->put(env('TOKEN_FILE'), json_encode($accessToken), 'private');

            //Init Service
            $service = new Google_Service_YouTube($client);

            $queryParams = [
                'mine'       => 'like',
                'maxResults' => 50,
            ];

            $response = $service->subscriptions->listSubscriptions('snippet,contentDetails', $queryParams);

            $response = response()->json($request);

            return $response;
        } elseif ($fileExists != false) {

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

                Storage::disk('private')->put(env('TOKEN_FILE'), json_encode($newAccessToken), 'private');
            }
            //Init Service
            $service = new Google_Service_YouTube($client);

            $queryParams = [
                'mine'       => 'true',
                'maxResults' => 50,
            ];

            if (isset($pageToken)) {
                //nextpagetoken set
                $queryParams['pageToken'] = $pageToken;
            }
            $response = $service->subscriptions->listSubscriptions('snippet,contentDetails', $queryParams);

            // $responseArray = (array) $response;
            // $responseJson = response()->json($request);
            // $responseCollection = collect($response);

            // access items array/key from Google object reponse
            $items = $response->items;

            //pick only id, title and description
            // $ids = json_encode(Arr::pluck($items, ['id']));

            // $snippet = Arr::pluck($items, ['snippet']);

            // $titles = json_encode(Arr::pluck($snippet, ['title']));
            // $descs = json_encode(Arr::pluck($snippet, ['description']));

            $entries = 0;
            $exists  = 0;

            foreach ($items as $t) {

                // $idtemp = "Yotube Channel Link is: https://youtube.com/channel/" . $t['snippet']['resourceId']['channelId'] . "   Channel Title: " . $t['snippet']['title'] . '    ' . $t['snippet']['description'];
                // $results = print_r($idtemp);

                $link = 'https://youtube.com/channel/';

                $id = $t['snippet']['resourceId']['channelId'];

                $idExists = MySubscriptions::where('link', '=', $id)->first();

                if ($idExists === null) {
                    // video link doesn't exist in db

                    //insert video to db
                    MySubscriptions::create(
                        [
                            'link'        => $t['snippet']['resourceId']['channelId'],
                            'title'       => $t['snippet']['title'],
                            'description' => $t['snippet']['description'],
                        ]
                    );

                    $results = $id . "  Inserted";

                    $entries++;

                    print_r(json_encode($results));
                } else {
                    $results = $id . "  Exists!";

                    $exists++;

                    print_r(json_encode($results));
                }
            }

            if ($entries < 2) {
                $Url = URL::current();

                $tokenInput = $response->nextPageToken;

                echo "Records Inserted: " . $entries . "  Entries Skipped: " . $exists;

                return Redirect::intended($Url . "?next=" . $tokenInput);
            } else {
                // $mergeArray = echo $id ;
                // $response = $response->nextPageToken;
                // $response = response()->json($response);

                return var_dump("Records Inserted: " . $entries . "  Entries Skipped: " . $exists);

                // return response()->json($results);
            }
        }
        // create auth url
        $url = $client->createAuthUrl();

        return Redirect::intended($url);

        // return $client;
    }
    /**
     * Prepare for Google Auth and generating Auth URL, uses OAuth 2.0
     * @return object Google Client
     */
    public function authGoogleApi()
    {
        $client = new Google_Client();

        $client->setApplicationName('Autopost Telegram Bot');

        $client->setScopes(['https://www.googleapis.com/auth/youtube.readonly']);

        //$client->setAuthConfig(env('CLIENT_SECRET'));

        $client->setClientId(env('CLIENT_ID'));

        $client->setClientSecret(env('CLIENT_SECRET_PASS'));

        $client->setDeveloperKey(env('YOUTUBE_API_KEY', 'YOUR_API_KEY'));

        $client->setAccessType('offline');

        $client->setApprovalPrompt('force');

        // $client->setLoginHint(env('LOGIN_HINT'));

        //Callback URL
        // if (env('APP_ENV') === 'local') {
        //     $redirect_uri = URL::current();
        // } else {
        //     $redirect_uri = env('APP_URL');

        //     // return   print($redirect_uri);
        // }

        $redirect_uri = env('APP_URL') . '/auth';
        //set redirect URL
        // Route
        $client->setRedirectUri($redirect_uri);

        return $client;
    }

    /**
     * Prepare for Google Auth through the API Console
     * @return object Google Client
     */
    public function getGoogleApiAuth()
    {
        $client = new Google_Client();

        $client->setApplicationName('Autopost Telegram Bot');

        $client->setScopes(['https://www.googleapis.com/auth/youtube.readonly']);

        //$client->setAuthConfig(env('CLIENT_SECRET'));

        // $client->setClientId(env('CLIENT_ID'));

        // $client->setClientSecret(env('CLIENT_SECRET_PASS'));

        $client->setDeveloperKey(env('YOUTUBE_API_KEY', 'YOUR_API_KEY'));

        // $client->setAccessType('offline');

        // $client->setApprovalPrompt('force');

        $redirect_uri = env('APP_URL') . '/auth';
        //set redirect URL
        // Route
        $client->setRedirectUri($redirect_uri);

        return $client;
    }
    /**
     * Complete auth, return code to User
     *@return var code and view 
     */
    public function authComplete(Request $request)
    {
        $code = $request->input('code');

        $data['code'] = $code;
        logger($data['code']);
        return view('auth-success', $data);
    }
    /**
     * Save User access tokens to db
     * @return true/false
     */
    public function authSave($data, $userDetails)
    {
        // $code = $request->input('code');

        $code = $data;
        // logger("Code submitted by USER_ID: " . $userDetails['user_id'] . ", = " . $data);
        // $pageToken = $request->input('next');

        $client = $this->authGoogleApi();


        //Use submitted code
        try {
            $fetchToken = $client->fetchAccessTokenWithAuthCode($code);
        } catch (Exception $e) {
            // report($e);

            return false;
        }


        //Get Access Tokens from Google OAuth
        $accessToken = $client->getAccessToken();

        //Set Our access token when calling Google APIs
        $client->setAccessToken($accessToken);

        //Save Token to DB
        Subscribers::where('user_id', $userDetails['user_id'])
            ->update(['access_tokens' => $accessToken]);

        // log access tokens
        // $data = response()->json($accessToken);
        // logger($data);
        return true;
    }
    /**
     * Refresh Tokens if access is already granted
     * @return true/false
     */
    public function refreshTokens($userDetails)
    {
        $client = $this->authGoogleApi();

        $tokenExists = Subscribers::where(
            'user_id',
            '=',
            $userDetails
        )
            ->whereNotNull('access_tokens')
            ->exists();

        //check if tokens exxist in db
        if ($tokenExists === true) {

            //get tokens from db
            $tokens =  Subscribers::select('access_tokens')
                ->where(
                    'user_id',
                    '=',
                    $userDetails
                )
                ->first();

            logger($tokens);
            $client->setAccessToken($tokens->access_tokens);

            /* Refresh token when expired */
            if ($client->isAccessTokenExpired()) {

                // the new access token comes with a refresh token as well
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

                $newAccessToken = $client->getAccessToken();

                //append new refresh token to new accestoken
                $newAccessToken['refresh_token'] = $client->getRefreshToken();


                $data = response()->json($newAccessToken);

                // log access tokens
                logger($data);

                Subscribers::where('user_id', $userDetails)
                    ->update(['access_tokens' => $newAccessToken]);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Revoke Access to Users, Youtube Account and delete access tokens
     * @return true/false 
     * 
     * Returns true if the revocation was successful, otherwise false
     */
    public function revokeAccess(array $userId)
    {
        Subscribers::where('user_id', '=', $userId['user_id'])
            ->orWhere('chat_id', '=', $userId['chat_id'])->delete();

        $client = $this->authGoogleApi();

        try {
            $client->revokeToken();
        } catch (Exception $e) {
            // report($e);

            return false;
        }
    }

    /**
     * Get User Liked videos
     * @return array $data 
     * 
     * Return liked videos
     */
    public function getLikedVideos(array $userDetails)
    {
        $userId = $userDetails['user_id'];

        $client = $this->authGoogleApi();

        $tokenExists = Subscribers::where(
            'user_id',
            '=',
            $userId
        )
            ->whereNotNull('access_tokens')
            ->exists();


        if ($tokenExists != false) {

            //check if file xists on the disk
            $tokens =  Subscribers::select('access_tokens')
                ->where(
                    'user_id',
                    '=',
                    $userId
                )
                ->first();

            //Get Our access token
            $client->setAccessToken($tokens->access_tokens);

            /* Refresh token when expired */
            if ($client->isAccessTokenExpired()) {
                $data = array(
                    "status" => false,
                    "results" => array()
                );

                logger("error, requires auth");
                return $data;
            }


            //Init Service
            $service = new Google_Service_YouTube($client);

            $queryParams = [
                'myRating'   => 'like',
                'maxResults' => 10,
            ];

            if (isset($userDetails['next'])) {
                //next page token set
                // logger($userDetails['next']);
                $queryParams['pageToken'] =  $userDetails['next'];
            } else if (isset($userDetails['prev'])) {
                //prevpage token set
                $queryParams['pageToken'] =  $userDetails['prev'];
            }
            $response = $service->videos->listVideos('snippet,contentDetails', $queryParams);


            // access items array/key from Google object reponse
            $items = $response->items;
            // $dataJson = response()->json($items);

            // logger($dataJson);
            $data = array();

            foreach ($items as $t  => $v) {

                $url = "https://youtube.com/watch?v=";
                $data['videos'][] = array(
                    'title' => $v['snippet']['title'],
                    'link' => $url . $v['id']
                );
            }

            $data['status'] = true;
            $data['next']  = $response->nextPageToken;
            $data['prev'] = $response->prevPageToken;
            // logger($data);
            return $data;
        } else {

            $data = array(
                "status" => false,
                "results" => array()
            );

            logger("error, no tokens");
            return $data;
        }
    }

    /**
     * Get Youtube Supported Regions
     * @return array data 
     * 
     * Return Youtube supported Countries/Regions
     */
    public function getRegions()
    {
        $client = $this->getGoogleApiAuth();

        //Init Service
        $service = new Google_Service_YouTube($client);

        $response = $service->i18nRegions->listI18nRegions('snippet');

        // access items array/key from Google object reponse
        $items = $response->items;
        // $dataJson = response()->json($items);

        // logger($dataJson);
        $data = array();

        foreach ($items as $t  => $v) {

            $url = "https://youtube.com/watch?v=";
            $data['regions'][] = array(
                'region' => $v['snippet']['gl'],
                'name' =>  $v['snippet']['name']
            );
        }

        $data['status'] = true;
        // logger($data);
        return $data;
    }
    /**
     * Get Trending Videos by Region code
     * @return array $data Return list of trending videos
     */
    public function getTrendingVideos(array $userData)
    {
        $client = $this->authGoogleApi();

        //Init Service
        $service = new Google_Service_YouTube($client);

        $queryParams = [
            'chart' => 'mostPopular',
            'regionCode' => $userData['region']
        ];

        if (isset($userData['next'])) {
            //next page token set
            // logger($userDetails['next']);
            $queryParams['pageToken'] =  $userData['next'];
        }

        $response = $service->videos->listVideos('snippet', $queryParams);


        // access items array/key from Google object reponse
        $items = $response->items;
        // $dataJson = response()->json($items);

        // logger($dataJson);
        $data = array();

        foreach ($items as $t  => $v) {

            $url = "https://youtube.com/watch?v=";
            $data['videos'][] = array(
                'title' => $v['snippet']['title'],
                'link' => $url . $v['id']
            );
        }

        $data['status'] = true;
        $data['next']  = $response->nextPageToken;
        // $data['prev'] = $response->prevPageToken;
        // logger($data);
        return $data;
    }

    /**
     * Get User Subscriptions
     * @return array $data 
     * 
     * Return Subscription List
     */
    public function getUserSubscriptions(array $userDetails)
    {
        $userId = $userDetails['user_id'];

        $client = $this->authGoogleApi();

        $tokenExists = Subscribers::where(
            'user_id',
            '=',
            $userId
        )
            ->whereNotNull('access_tokens')
            ->exists();


        if ($tokenExists != false) {

            //check if User-Token exists on the database
            $tokens =  Subscribers::select('access_tokens')
                ->where(
                    'user_id',
                    '=',
                    $userId
                )
                ->first();

            //Get Our access token
            $client->setAccessToken($tokens->access_tokens);

            /* Refresh token when expired */
            if ($client->isAccessTokenExpired()) {
                $data = array(
                    "status" => false,
                    "results" => array()
                );

                logger("error, requires auth");
                return $data;
            }


            //Init Service
            $service = new Google_Service_YouTube($client);

            $queryParams = [
                'mine'       => 'true',
                'maxResults' => 10,
            ];

            if (isset($userDetails['next'])) {
                //next page token set

                // logger($userDetails['next']);
                $queryParams['pageToken'] =  $userDetails['next'];
            } else if (isset($userDetails['prev'])) {
                //prevpage token set

                $queryParams['pageToken'] =  $userDetails['prev'];
            }
            $response = $service->subscriptions->listSubscriptions('snippet,contentDetails', $queryParams);


            // access items array/key from Google object reponse
            $items = $response->items;
            // $dataJson = response()->json($items);

            // logger($dataJson);
            $data = array();

            foreach ($items as $t  => $v) {

                $url = "https://youtube.com/channel/";
                $data['subscriptions'][] = array(
                    'title' => $v['snippet']['title'],
                    'link' => $url . $v['snippet']['resourceId']['channelId']
                );
            }

            $data['status'] = true;
            $data['next']  = $response->nextPageToken;
            $data['prev'] = $response->prevPageToken;
            // logger($data);
            return $data;
        } else {

            $data = array(
                "status" => false,
                "results" => array()
            );

            logger("error, no tokens");
            return $data;
        }
    }
}
