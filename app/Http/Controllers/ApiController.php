<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\DynamoDB\Controllers\DdbController;
use App\Http\Controllers\Controller as BaseController;

class ApiController extends BaseController
{
    /**
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function getShortTexts(Request $request)
    {
        $user = Auth::user();
        $ddb  = new DdbController();

        $resp = null;
        $size = $request->input("size",5);
        $mode = $request->input("mode","none");

        switch($mode) {
        case "none":
            $resp = $this->getRecentShortTexts($ddb, $user->id, $size);
            break;
        case "count":
            $resp = $this->getCountOfShortText($ddb, $user->id);
            break;
        }

        return $resp;
    }

    private function getRecentShortTexts($ddb, $uid, $size) {
        // Get cache from redis.
        $shortTexts = json_decode(Redis::get("recent_shortText_" . $uid), JSON_OBJECT_AS_ARRAY);

        // If cache does not exist, get from DynamoDB .
        if(is_null($shortTexts)) {
            $shortTexts = $ddb->queryContents("short_text", $uid, $size);
            foreach($shortTexts as &$st) {
                $key = $st["owner_id"] . "-" . $st["created_at"];
                $item = $ddb->getContentItem("short_text", $key);
                $st["text"] = $item["content"];
            }
            // Set new cache to redis.
            Redis::set("recent_shortText_" . $uid, json_encode($shortTexts));
        }
        return $shortTexts;
    }

    private function getCountOfShortText($ddb, $uid) {
        // Get cache from redis.
        $count = json_decode(Redis::get("count_shortText_" . $uid), JSON_OBJECT_AS_ARRAY);

        // If cache does not exist, get from DynamoDB .
        if(is_null($count)) {
            echo "Connect to DDB !";
            $count = $ddb->countContents("short_text", $uid);
            // Set new cache to redis.
            Redis::set("count_shortText_" . $uid, json_encode($count));
        }
        return $count;
    }

}