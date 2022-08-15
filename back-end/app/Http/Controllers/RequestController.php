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


    //main function that will store data in files
    public function runWatchdog()
    {   
        $headers = $this->headers;
        $page = 1;        
        //while loop to go through all pages available and break when result is empty
        while ($page != 0)
        {
            //curl setup to fetch all open pull requests one page at a time
            $url = env('GITHUB_API')."/pulls?state=open&sort=desc&page=".$page;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result, true);
            //increment page variable to go to next page on next iteration
            $page++;

            //the result variable is a page of open pull requests, if it exists the info is stored and the while loop continues
            if ($result)
            {
                //loop through the page return by GitHub Pulls API to get needed info of each pull request
                foreach ($result as $pull_request)
                {
                    //number variable will be used in the requested reviews API
                    $PR_number = $pull_request["number"];
                    $PR_date = $pull_request["created_at"];
                    $PR_ref = $pull_request["head"]["sha"];
                    
                    //construct a string that is easy to read to be stored in a text file
                    $record = "Title: ". $pull_request["title"]. " | URL: ".$pull_request["url"]. " | PR Number: ". $pull_request["number"]. " | PR ID: ". $pull_request["id"]. " | State: ". $pull_request["state"]. " | Date: ".  $pull_request["created_at"];
                    
                    //check if pull request was created more than 14 days ago
                    if ($this->checkDate($PR_date))
                    {
                        Storage::disk('local')->append('1-old-pull-requests.txt', $record);
                    }
                    else {
                        Storage::disk('local')->append('1-new-pull-requests.txt', $record);
                    }                    
                    
                    //check if pull request has required reviews
                    if ($this->checkReviews($PR_number))
                    {
                        Storage::disk('local')->append('2-review-required-pull-requests.txt', $record); 
                    }
                    else
                    {
                        Storage::disk('local')->append('3-no-reviews-requested-pull-requests.txt', $record);
                    }

                    //check if pull request review status is success
                    if ($this->checkReviewStatus($PR_ref))
                    {
                        Storage::disk('local')->append('4-review-status-success-pull-requests.txt', $record. " | PR_REF: ". $PR_ref);
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

    //the rest of the functions are condition checkers to help runWatchdog function sort the data in corresponding txt files

    //checks if the open pull request was created more than 14 days ago
    public function checkDate($PR_date)
    {
        $current_date = Carbon::now()->toDateString();
        //check if the pull request date matches the condition by subtracting timestamps of current date and pull request date
        if ((Carbon::parse($current_date)->timestamp - Carbon::parse($PR_date)->timestamp) > 1209600)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    //checks if the open pull request has required reviews or no
    public function checkReviews($PR_number)
    {
        $headers = $this->headers;
        $url = env('GITHUB_API')."/pulls/".$PR_number."/requested_reviewers";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $result = json_decode($result);
        curl_close($ch);

        if ($result->users == false && $result->teams == false)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    //check for open pull request where review status is 'success'
    public function checkReviewStatus($PR_ref)
    {
        $headers = $this->headers;
        $url = env('GITHUB_API')."/commits/".$PR_ref."/status";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $result = json_decode($result);
        curl_close($ch);

        if ($result->state == "success")
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}