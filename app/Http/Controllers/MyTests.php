<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\YoutubeVideos;
use App\TelegramBot;
use Illuminate\Contracts\Support\Jsonable;

/**
 * MyTests  Class                                         
 * 
 * @author John Muiruri  <jontedev@gmail.com>
 * 
 */
class MyTests extends Controller
{
    //

    // public function index()
    // {
    //     return YoutubeVideos::paginate(10);
    // }


    public function index()
    {
        $result = YoutubeVideos::select('link')
        ->orderBy('id', 'desc')
        ->limit(1);

        // $link= $result['data'];
        // // $link =$link['data']; 

        // // $link = $link->link;
        // foreach ($result as $video) {

        //     $link = $video['link'];
        //     // echo $link;

        //     $t = $video['title'];
        //     // echo $t;
        // }

        $jsonResult = json_encode($result, JSON_PRETTY_PRINT);
        print_r($result);

        // return $link;
        // print_r($arrResult);
    }
}
