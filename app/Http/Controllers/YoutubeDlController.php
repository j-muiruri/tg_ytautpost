<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use YoutubeDl\Options;
use YoutubeDl\YoutubeDl;

/**
 * YoutubeDl Controller to Download Videos and Mp3
 */
class YoutubeDlController extends Controller
{

    /**
     * Download Video in .mp4 format
     * Only logged in user allowed
     */
    public function downloadVideo(Request $request)
    {
        $yt = new YoutubeDl();
        $url = $request->input('url');
        logger('route success');
        // $this->downloadProgress();
        $collection = $yt->download(
            Options::create()
                ->downloadPath(storage_path())
                ->url($url)
        );


        foreach ($collection->getVideos() as $video) {

            if ($video->getError() !== null) {
                logger("Error downloading video: {$video->getError()}.");
            } else {
                logger($video->getTitle()); // Will return Phonebloks
                // $video->getFile(); // \SplFileInfo instance of downloaded file
                return true;
            }
        }
    }

    /**
     * Download Audio in .mp3 format
     */
    public function downloadAudio()
    {
        $yt = new YoutubeDl();
        $url = 'https://youtube.com/watch?v=9Bt-nV-wz3c';
        logger('route success');
        $collection = $yt->download(
            Options::create()
                ->yesPlaylist()
                ->downloadPath(storage_path())
                ->extractAudio(true)
                ->audioFormat('mp3')
                ->audioQuality(0) // best
                ->output('%(title)s.%(ext)s')
                ->url($url)
        );

        $this->downloadProgress($yt);
        foreach ($collection->getVideos() as $video) {
            if ($video->getError() !== null) {
                logger("Error downloading video: {$video->getError()}.");
            } else {
                logger("Audio downloaded successfully at: " . $video->getFile()); // audio file
                return response()->json(
                    [
                        $status = true
                    ]
                );
            }
        }
    }

    /**
     * Get Download Progress of a file
     */
    private function downloadProgress($yt)
    {
        // $yt = new YoutubeDl();
        $yt->onProgress(static function (string $progressTarget, string $percentage, string $size, string $speed, string $eta, ?string $totalTime): void {
            logger("Download file: $progressTarget; Percentage: $percentage; Size: $size");
            if ($speed) {
                logger("; Speed: $speed");
            }
            if ($eta) {
                logger("; ETA: $eta");
            }
            if ($totalTime !== null) {
                logger("; Downloaded in: $totalTime");
            }
        });
    }

    /**Application functions */

    /**
     * Download Video in .mp4 format
     * @param string $url
     */
    public function downloadUserVideo(string $url)
    {
        $yt = new YoutubeDl();
        $this->downloadProgress($yt);
        $collection = $yt->download(
            Options::create()
                ->downloadPath(storage_path())
                ->url($url)
        );


        foreach ($collection->getVideos() as $video) {

            if ($video->getError() !== null) {
                logger("Error downloading video: {$video->getError()}.");
            } else {
                logger($video->getTitle()); // Will return Phonebloks
                // $video->getFile(); // \SplFileInfo instance of downloaded file
                return true;
            }
        }
    }

    /**
     * Download Audio in .mp3 format
     * @param string $url
     * @return Array $fileDetails
     */
    public function downloadUserAudio(string $url)
    {
        try {

            $yt = new YoutubeDl();
            $filePath = public_path() . '/storage/audio';

            $collection = $yt->download(
                Options::create()
                    ->downloadPath($filePath)
                    ->extractAudio(true)
                    ->audioFormat('mp3')
                    ->audioQuality(0) // best
                    ->output('%(title)s.%(ext)s')
                    ->url($url)
            );

            //simulate download, no video download
            // $collection = $yt->download(
            //     Options::create()
            //      ->downloadPath($filePath)
            //     ->skipDownload(true)->url($url)
            // );

            $this->downloadProgress($yt);
            $fileDetails = array();
            foreach ($collection->getVideos() as $video) {
                if ($video->getError() !== null) {

                    logger()->error("Error downloading video: {$video->getError()}.");
                    $fileDetails['data'][] = [
                        'status' => false
                    ];
                } else {

                    $filepath = $video->getFilename();
                    $fileDesc = $video->getDescription();
                    $file = Str::replaceFirst($filePath . '/', '', $filepath);
                    $fileName = Str::of($file)->replace('_', ' ');
                    // // $fileDetails['audio'] = url('public/audio/'.$file); //audio file
                    $fileDetails['data'][] = [
                        'name' => $fileName . "\n" . $fileDesc,
                        'artist' => $video->getUploader(),
                        'description' => $fileDesc,
                        'status' => true,
                        'audio' => storage_path() . '/app/public/audio/' . $file
                    ];
                }
            }
            $fileDetails['status'] = true;

            return $fileDetails;
        } catch (\Throwable $th) {

            $fileDetails['status'] = false;
            logger()->error($th);
            return $fileDetails;
        }
    }
    public function testRoute()
    {
        dd('hello route is working');
    }
}
