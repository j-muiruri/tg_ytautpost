<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\YoutubeVideos;
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
        $result =  YoutubeVideos::simplePaginate(10);

        $link= $result['data'];
        // $link =$link['data']; 

        // $link = $link->link;
        foreach ($result as $video) {

            $link = $video['link'];
            // echo $link;

            $t = $video['title'];
            // echo $t;
        }

        $jsonResult = json_encode($result['data']);
        $arrResult = $result->toArray();
        // var_dump($arrResult['next_page_url']);

        // return $link;
        print_r($arrResult);
    }
}
