<?php

namespace App\Http\Controllers;
use Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function __construct()
    {
        $github_token = env('GITHUB_TOKEN');
        $this->headers = [
            "User-Agent: ali-shehab94",
            "Accept: application/vnd.github+json",
            "Authorization: token $github_token"
        ];
    }

    public function allOpenRepos14()
    {   
        $current_date = Carbon::now()->toDateString();
        $past_date = "2021-10-09";
        $interval = abs(Carbon::parse($current_date)->timestamp - Carbon::parse($past_date)->timestamp);
        $headers = $this->headers;
        // $url="https://api.github.com/repos/woocommerce/woocommerce/pulls?state=open&created=<$date&per_page=3";
        $x = 1;
        $PR_number = [];
        while ($x != 0)
        {
            $url = "https://api.github.com/repos/woocommerce/woocommerce/pulls?state=open&page=$x&sort=desc";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result, true);
            $x++;
            if ($result)
            {
                foreach ($result as $pull_request)
                {
                    $number = $pull_request["number"];
                    $record = "Title: ". $pull_request["title"]. " | URL: ".$pull_request["url"]. " | PR Number: ". $pull_request["number"]. " | PR ID: ". $pull_request["id"]. " | State: ". $pull_request["state"]. " | Date: ".  $pull_request["created_at"];
                    $url = "https://api.github.com/repos/woocommerce/woocommerce/pulls/$number/reviews";
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    print_r($result);
                    // array_push($PR_number, $pull_request["number"]);
                    if ((Carbon::parse($current_date)->timestamp - Carbon::parse($pull_request["created_at"])->timestamp) > 1209600)
                    {
                        // array_push($myArray, $record);
                        Storage::disk('local')->append('1-old-pull-requests.txt', $record);
                    }else {
                        Storage::disk('local')->append('1-new-pull-requests.txt', $record);
                    }
                }
               
            }else
            {
                $x = 0;
            }
        }
        
        return "done";

    }

    public function reviews($PR_numbers)
    {   
        foreach ($PR_numbers as $number)
        {
            $url = "https://api.github.com/repos/woocommerce/woocommerce/pulls/$number/reviews";
            $headers = $this->headers;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            foreach ($result as $review)
                {
                    $record = "Title: ". $pull_request["title"]. " | URL: ".$pull_request["url"]. " | PR Number: ". $pull_request["number"]. " | PR ID: ". $pull_request["id"]. " | State: ". $pull_request["state"]. " | Date: ".  $pull_request["created_at"];
                    array_push($PR_number, $pull_request["number"]);
                    if ((Carbon::parse($current_date)->timestamp - Carbon::parse($pull_request["created_at"])->timestamp) > 1209600)
                    {
                        // array_push($myArray, $record);
                        Storage::disk('local')->append('1-old-pull-requests.txt', $record);
                    }else {
                        Storage::disk('local')->append('1-new-pull-requests.txt', $record);
                    }
                }
        }
        return;
    }

    public function isRequired($review)
    {

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
    //a day is 86400 seconds
    //14 days is 1,209,600
}


// &per_page=all&page=3