<?php

namespace App\Http\Controllers;
use Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class RequestController extends Controller
{

    public function allOpenRepos14()
    {   
        $date = Carbon::now()->subDays(14)->toDateString();
        $github_token = env('GITHUB_TOKEN');
        $headers = [
            "User-Agent: ali-shehab94",
            "Accept: application/vnd.github+json",
            "Authorization: token $github_token"
        ];
        // $url="https://api.github.com/repos/woocommerce/woocommerce/pulls?state=open&created=<$date&per_page=3";

        $url = "https://api.github.com/repos/woocommerce/woocommerce/pulls?state=open&per_page=100";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
    
        foreach ($result as $pull_request)
        {
            $file_content = $pull_request["url"]. " ". $pull_request["id"]. " ". $pull_request["state"]. " ";
            // array_push($myArray, $file_content);
            Storage::disk('local')->append('1-old-pull-requests.txt', $file_content);
        }
        return;

    }

    // public function paginate()
    // {
    //     $github_token = env('GITHUB_TOKEN');
    //     $headers = [
    //         "User-Agent: ali-shehab94",
    //         "Accept: application/vnd.github+json",
    //         "Authorization: token $github_token"
    //     ];
    //     // $url="https://api.github.com/repos/woocommerce/woocommerce/pulls?state=open&created=<$date&per_page=2";
    //     $x = 1;

  
    //     $url = "https://api.github.com/repos/woocommerce/woocommerce/pulls?state=open&page=$x";
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     $result = curl_exec($ch);
    //     curl_close($ch);
    //     $result = json_decode($result, true);
        
    //     return $result;
    // }
}


// &per_page=all&page=3