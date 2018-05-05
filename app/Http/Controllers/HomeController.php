<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\DynamoDB\Controllers\DdbController;

class HomeController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function home(Request $request)
    {
        $user = Auth::user();

        // Get todaysPost count from redis.
        $todaysPosts = Redis::get("posts_count_". date("Ymd"). "_" .$user->id);
        if( is_null($todaysPosts) ) {
            $todaysPosts = 0;
        }

        // Get recent Logs.
        $logs = $this->getLogs($user->id);

        return view('home', [ 
          "username" => $user->name , 
          "post_succeed" => $request->input('post_succeed',false),
          "todays_posts" => $todaysPosts,
          "logs" => $logs
        ]);
    }

    private function getLogs($uid) {
        $resArray  = Array();
        $ddb = new DdbController();

        $shortTexts = $this->getRecentShortTexts($ddb, $uid);
        $resArray = array_merge($resArray, [
            "shortTexts" => $shortTexts["contents"],
            "nShortText" => count($shortTexts["contents"])
        ]);

        return $resArray;
    }

    private function getRecentShortTexts($ddb, $uid) {
        $nLimit    = 5;

        // Get cache from redis.
        $shortTexts["contents"] = json_decode(Redis::get("recent_shortText_" . $uid), JSON_OBJECT_AS_ARRAY);

        // If cache does not exist, get from DynamoDB .
        if(is_null($shortTexts["contents"])) {
            $shortTexts = $ddb->queryContents("short_text", $uid, $nLimit);
            foreach($shortTexts["contents"] as &$st) {
                $key = $st["owner_id"] . "-" . $st["created_at"];
                $item = $ddb->getContentItem("short_text", $key);
                $st["text"] = $item["content"];
            }
            // Set new cache to redis.
            Redis::set("recent_shortText_" . $uid, json_encode($shortTexts["contents"]));
        }
        return $shortTexts;
    }
}
