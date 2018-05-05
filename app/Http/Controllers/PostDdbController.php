<?php

namespace App\Http\Controllers;

date_default_timezone_set('Asia/Tokyo');

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\DynamoDB\Controllers\DdbController;

use Request;

class PostDdbController extends BaseController
{
    public function postShortText()
    {
        $text   = Request::input('short_text',"");
        if($text == "") {
            redirect()->route('home');
        }
        $result = $this->postShortTextToDynamo($text);
        $post_succeed = $result == 200 ? true : false;

        return redirect()->route('home', ["post_succeed" => $post_succeed]);
    }

    private function postShortTextToDynamo($text) {
        
        // create ddb instance and vars.
        $ddbController = new DdbController();
        $uid        = Auth::user()->id;
        $date       = date(DATE_ATOM);

        // Create params to put Item.
        $contentParam   = $ddbController->createPutParam('contents', $this->createContentJson($uid, $date, 0));
        $shortTextParam = $ddbController->createPutParam('short_text', $this->createShortTextJson($uid, $date, $text, 0));

        $result     = $ddbController->putItem($contentParam);

        if($result["@metadata"]["statusCode"] != 200) {
            return $result["@metadata"]["statusCode"];
        }

        $result     = $ddbController->putItem($shortTextParam);

        if($result["@metadata"]["statusCode"] != 200) {
            $contentParam = $ddbController->createPutParam('contents', $this->createContentJson($uid, $date, 1));
            $delResult = $ddbController->putItem($contentParam);
            if($delResult["@metadata"]["statusCode"] != 200) {
                logger()->critical("Could not delete incorrect content.\n  $uid:$date", $content);
                return 999;
            }
        }

        // Delete cache from redis.
        Redis::del("recent_shortText_" . $uid);
        Redis::del("shortText_" . $uid);
        Redis::del("count_shortText_"  . $uid);
        
        Redis::incr("posts_count_". date("Ymd"). "_" .$uid);

        return $result["@metadata"]["statusCode"];
    }

    private function createContentJson($uid, $date, $deleted_flag) {
        return '{
            "owner_id": ' . $uid . ',
            "created_at": "' . $date . '",
            "content_id": "' . $uid . "-" . $date .  '",
            "content_type": "short_text",
            "deleted_flag": '. $deleted_flag .'
        }';
    }

    private function createShortTextJson($uid, $date, $text, $deleted_flag) {
        return '
            {
                "content_id": "' . $uid . "-" . $date .  '",
                "content": "'. $text .'",
                "deleted_flag": 0
            }
        ';
    }

}
