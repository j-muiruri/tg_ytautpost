<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\YoutubeVideos;

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
        $result =  YoutubeVideos::paginate(10);

        $link= $result['data'];
        // $link =$link['data']; 

        // $link = $link->link;
        foreach ($result as $video) {

            $link = $video['link'];
            echo $link;

            $t = $video['title'];
            echo $t;
        }

        // var_dump($link);

        // return $link;
    }
}
