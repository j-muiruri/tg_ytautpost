<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request; 
use Google_Client; 

class GoogleApiClient extends Controller { 
    
    Google_Client::setApplicationName('API code samples'); 
    
    Google_Client::setScopes(['https://www.googleapis.com/auth/youtube.readonly',
    ]); 


    Google_Client::setAuthConfig('googleapi_keys.json'); 
    
    Google_Client::setAccessType('offline'); 

    // Request authorization from the user.
    $authUrl = Google_Client::createAuthUrl(); 

    printf("Open this link in your browser:\n%s\n", $authUrl); 
    print('Enter verification code: '); 

    $authCode = trim(fgets(STDIN)); 

    // Exchange authorization code for an access token.
    $accessToken = Google_Client::fetchAccessTokenWithAuthCode($authCode); 
    
    Google_Client::setAccessToken($accessToken); 

    // Define service object for making API requests.
    $service = new Google_Service_YouTube($client); 

    $queryParams = [
        'maxResults' => 25, 
    'mine' => true
    ]; 

    $response = $service - > playlists - > listPlaylists('snippet,contentDetails', $queryParams); 
}
