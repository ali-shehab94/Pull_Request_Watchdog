<?php

namespace App\Http\Controllers;
use Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    //Global values including personal access token and headers
    public function __construct()
    {
        $github_token = env('GITHUB_TOKEN');
        $this->headers = [
            "User-Agent: ali-shehab94",
            "Accept: application/vnd.github+json",
            "Authorization: token $github_token"
        ];
    }


    public function runWatchdog()
    {   
        $current_date = Carbon::now()->toDateString();
        $headers = $this->headers;
        $page = 1;        
        //while loop to go through all pages available and break when result is empty
        while ($page != 0)
        {
            $url = env('PULLS').$page;
            //curl setup
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result, true);
            //increment page variable to go to next page on next iteration
            $page++;
            //if the result exists the information is stored and the while loop continues
            if ($result)
            {
                //loop through the page return by GitHub Pulls API to get needed info of each pull request
                foreach ($result as $pull_request)
                {
                    //number variable will be used in the requested reviews API
                    $number = $pull_request["number"];
                    //construct a string that is easy to read to be stored in a text file
                    $record = "Title: ". $pull_request["title"]. " | URL: ".$pull_request["url"]. " | PR Number: ". $pull_request["number"]. " | PR ID: ". $pull_request["id"]. " | State: ". $pull_request["state"]. " | Date: ".  $pull_request["created_at"];
                    //curl setup
                    $url = env('REVIEWS').$number."/requested_reviewers";
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $result = curl_exec($ch);
                    $result = json_decode($result);
                    curl_close($ch);
                    //check if pull request has requested reviews
                    if ($result->users == true or $result->teams == true)
                    {
                        Storage::disk('local')->append('2-review-required-pull-requests.txt', $record); 
                    }
                    else if ($result->users == false && $result->teams == false)
                    {
                        Storage::disk('local')->append('3-no-reviews-requested-pull-requests.txt', $record);
                    }
                    //check if the open pull request was created more than 14 days ago by subtracting timestamps of current date and pull request date
                    if ((Carbon::parse($current_date)->timestamp - Carbon::parse($pull_request["created_at"])->timestamp) > 1209600)
                    {
                        Storage::disk('local')->append('1-old-pull-requests.txt', $record);
                    }else {
                        Storage::disk('local')->append('1-new-pull-requests.txt', $record);
                    }
                }
            //if the result variable is empty we will change page value to 0 which is the condition to quit the while loop
            }else
            {
                $page = 0;
            }
        }
        
        return "Files stored in storage/app/public";
    }
}