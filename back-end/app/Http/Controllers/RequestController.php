<?php

namespace App\Http\Controllers;
use Carbon;

use Illuminate\Http\Request;

class RequestController extends Controller
{

    public function allOpenRepos14()
    {   
        $date = Carbon::now()->subDays(14)->toDateString();
        $github_token = env('GITHUB_TOKEN');
        $headers = [
            "User-Agent: ali-shehab94",
            "Authorization: token $github_token"
        ];
        $url="https://api.github.com/repos/woocommerce/woocommerce/pulls?state=open&created=<$date";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result);
        return $result;
    }
}
