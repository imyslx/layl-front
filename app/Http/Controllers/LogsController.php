<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\DynamoDB\Controllers\DdbController;

class LogsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function logs(Request $request)
    {
        $user = Auth::user();
        $ddb = new DdbController();

        $logs = [
            "shortTexts" => Array()
        ];
        $mode = $request->input("mode","");
        $windowSize = $request->input("winSize",5);
        $viewSize = $request->input("viewSize",5);
        $nextSize = $viewSize + $windowSize;

        if($mode == "shortText") { 
            $logs["shortTexts"] = $this->getShortTexts($ddb, $user->id, $windowSize, $viewSize);
        }
        $noMore = count($logs["shortTexts"]) < $viewSize ? true : false;

        return view('logs', [ 
            "logs"     => $logs,
            "mode"     => $mode,
            "winSize"  => $windowSize,
            "viewSize" => $viewSize,
            "nextSize" => $nextSize,
            "noMore"   => $noMore
          ]);
    }

    // TODO: 今はレスポンスとして必要な数だけ返さざるを得ないけど、JSから呼ぶ時は追加で5つのデータを得る仕組みを別で組む。
    private function getShortTexts($ddb, $uid, $windowSize, $viewSize) {

        // Get cache from redis.
        $shortTexts = json_decode(Redis::get("shortText_" . $uid), JSON_OBJECT_AS_ARRAY);

        $exclusiveStartKey = null;
        if(is_null($shortTexts)) {
            $shortTexts = Array();
        } else {
            $idx = count($shortTexts) - 1;
            $exclusiveStartKey["created_at"]["S"] = $shortTexts[$idx]["created_at"];
            $exclusiveStartKey["owner_id"]["N"] = (string)$shortTexts[$idx]["owner_id"];
        }
        logger()->debug("RedisDocs: ".count($shortTexts));

        // Compare number of RedisDocs and $size.
        $nDiff = $viewSize - count($shortTexts);

        if($nDiff < 0) {
            // Cut RedisDoc to $viewSize.
            $shortTexts = array_slice($shortTexts, 0, $viewSize);
            return $shortTexts;
        } elseif($nDiff === 0) {
            return $shortTexts;
        }

        // Get more Docs from DynamoDB.
        logger()->debug("Get the Doc from DDB.", [ $exclusiveStartKey ]);
        do {
            $tmpShortTexts = $ddb->queryContents("short_text", $uid, $windowSize, $exclusiveStartKey);
            foreach($tmpShortTexts["contents"] as &$st) {
                $key = $st["owner_id"] . "-" . $st["created_at"];
                $item = $ddb->getContentItem("short_text", $key);
                $st["text"] = $item["content"];
            }
            $shortTexts = array_merge($shortTexts, $tmpShortTexts["contents"]);
            $exclusiveStartKey = $tmpShortTexts["LastEvaluatedKey"];
            if(is_null($exclusiveStartKey)) {
                break;
            }
            $nDiff -= count($tmpShortTexts["contents"]);
        } while($nDiff > 0);
        // Set new cache to redis.
        Redis::set("shortText_" . $uid, json_encode($shortTexts));

        return $shortTexts;
    }
}
