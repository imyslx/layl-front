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
    public function getSHortTexts(Request $request)
    {
        $user = Auth::user();
        $ddb  = new DdbController();
        $size = $request->input("size",5);

        $shortTexts = $this->getRecentShortTexts($ddb, $user->id, $size);

        return $shortTexts;
    }

    private function getRecentShortTexts($ddb, $uid, $size) {
        // Get cache from redis.
        $shortTexts = json_decode(Redis::get("recent_shortText_" . $uid), JSON_OBJECT_AS_ARRAY);
        //echo count($shortTexts) . " : " . $size;
        // if(count($shortTexts) < $size) {
        //     $shortTexts = null;
        // }

        // If cache does not exist, get from DynamoDB .
        if(is_null($shortTexts)) {
            //echo "get from DDB.";
            $shortTexts = $ddb->queryContents("short_text", $uid,$size);
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

}