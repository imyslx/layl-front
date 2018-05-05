<?php

namespace App\DynamoDB\Controllers;

date_default_timezone_set('Asia/Tokyo');

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Aws\Sdk;

class DdbController
{

    private $ddbClient;

    public function __construct() {
        $sdk = new Sdk([
            'endpoint' => Config::get('database.connections.dynamodb.endpoint'),
            'region'   => 'ap-northeast-1',
            'version'  => 'latest'
        ]);
        $this->ddbClient = $sdk->createDynamoDb();
    }

    public function createPutParam($table, $json) {
        $marshaler = new Marshaler();
        $item = $marshaler->marshalJson($json);

        return [
            'TableName' => $table,
            'Item' => $item
        ];
    }

    public function createGetParam($table, $keyJson) {
        $marshaler = new Marshaler();
        $key = $marshaler->marshalJson($keyJson);

        return [
            'TableName' => $table,
            'Key' => $key
        ];
    }

    public function createQueryParam($table, $eavJson) {
        $marshaler = new Marshaler();
        $eav = $marshaler->marshalJson($eavJson);

        return [
            'TableName' => $table,
            'ScanIndexForward' => false,
            'Key' => $key
        ];
    }
    public function putItem($param) {
        $result = null;
        try {
            $result = $this->ddbClient->putItem($param);
            logger()->notice("Added successfully.", $param);
        } catch (DynamoDbException $e) {
            logger()->critical("Unable to add item.\nMessage: ".$e->getMessage(), $param);
        }
        return $result->toArray();
    }

    public function getItems($param) {
        $result = null;
        try {
            $result = $this->ddbClient->getItem($param);
            logger()->notice("Added successfully.", $param);
        } catch (DynamoDbException $e) {
            logger()->critical("Unable to add item.\nMessage: ".$e->getMessage(), $param);
        }
        return $result->toArray();
    }

    public function queryContents($type, $uid, $nLimit, $exclusiveStartKey = null) {
        $result = Array();
        $resArray["contents"] = Array();

        $marshaler = new Marshaler();
        $eav = $marshaler->marshalJson('
            {
                ":uid": '.$uid.',
                ":type": "'.$type.'",
                ":deleted_flag": 0
            }
        ');

        $params = [
            'TableName' => 'contents',
            'KeyConditionExpression' => 'owner_id = :uid',
            'FilterExpression' => '(content_type = :type) AND (deleted_flag = :deleted_flag)',
            'ExpressionAttributeValues'=> $eav,
            'ScanIndexForward' => false,
            'Limit' => (int)$nLimit
        ];

        if(! is_null($exclusiveStartKey)) {
            $params["ExclusiveStartKey"] = $exclusiveStartKey;
        }

        try {
            $result = $this->ddbClient->query($params);

            foreach ($result["Items"] as $item) {
                $resArray["contents"][] = $marshaler->unmarshalItem($item);
            }
            $resArray["LastEvaluatedKey"] = $result["LastEvaluatedKey"];
        } catch (DynamoDbException $e) {
            echo "Unable to query:\n";
            echo $e->getMessage() . "\n";
            var_dump($params);            
        }

        $test = $result->toArray();
        logger()->debug("Count: ". $test["Count"]);
        logger()->debug("ScannedCount: ". $test["ScannedCount"]);
        logger()->debug("LastEvaluatedKey", [ $resArray["LastEvaluatedKey"] ]);
        return $resArray;
    }

    public function getContentItem($table, $key) {
        $marshaler = new Marshaler();
        $keyJson = $marshaler->marshalJson('
            {
                "content_id": "'.$key.'"    
            }
        ');

        $params = [
            'TableName' => $table,
            'Key' => $keyJson
        ];

        try {
            $item = $this->ddbClient->getItem($params);
        } catch (DynamoDbException $e) {
            echo "Unable to query:\n";
            echo $e->getMessage() . "\n";
        }

        return $marshaler->unmarshalItem($item["Item"]);
    }

    public function countContents($type, $uid) {
        $resArray = Array();

        $marshaler = new Marshaler();
        $eav = $marshaler->marshalJson('
            {
                ":uid": '.$uid.',
                ":type": "'.$type.'",
                ":deleted_flag": 0
            }
        ');

        $count = 0;
        $exclusiveStartKey = null;
        do {
            $params = [
                'TableName' => 'contents',
                'KeyConditionExpression' => 'owner_id = :uid',
                'FilterExpression' => '(content_type = :type) AND (deleted_flag = :deleted_flag)',
                'ExpressionAttributeValues'=> $eav,
                'ScanIndexForward' => false,
                'Select' => 'COUNT',
                'Limit' => 5
            ];

            if(! is_null($exclusiveStartKey)) {
                $params["ExclusiveStartKey"] = $exclusiveStartKey;
            }

            try {
                $result = $this->ddbClient->query($params);
            } catch (DynamoDbException $e) {
                echo "Unable to query:\n";
                echo $e->getMessage() . "\n";
                var_dump($params);            
            }

            $resArray = $result->toArray();
            $count += $result["Count"];
            if(array_key_exists('LastEvaluatedKey',$resArray)) {
                $exclusiveStartKey = $result["LastEvaluatedKey"];
            } else {
                break;
            }
        } while (! is_null($exclusiveStartKey));

        return $count;
    }
}
