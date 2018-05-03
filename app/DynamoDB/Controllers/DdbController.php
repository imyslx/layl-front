<?php

namespace App\DynamoDB\Controllers;

date_default_timezone_set('Asia/Tokyo');

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Aws\Sdk;

class DdbController
{

    private $ddbClient;

    public function __construct() {
        $sdk = new Sdk([
            'endpoint'   => 'http://dynamodb.ap-northeast-1.amazonaws.com',
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
            logger()->critical("Unable to add item.\nMessage: $e->getMessage()", $param);
        }
        return $result->toArray();
    }

    public function getItems($param) {
        $result = null;
        try {
            $result = $this->ddbClient->getItem($param);
            logger()->notice("Added successfully.", $param);
        } catch (DynamoDbException $e) {
            logger()->critical("Unable to add item.\nMessage: $e->getMessage()", $param);
        }
        return $result->toArray();
    }

    public function queryContents($type, $uid, $nLimit) {
        $resArray = Array();

        $marshaler = new Marshaler();
        $eav = $marshaler->marshalJson('
            {
                ":uid": '.$uid.',
                ":type": "'.$type.'"
            }
        ');

        $params = [
            'TableName' => 'contents',
            'KeyConditionExpression' => 'owner_id = :uid',
            'FilterExpression' => 'content_type = :type',
            'ExpressionAttributeValues'=> $eav,
            'ScanIndexForward' => false,
            'Limit' => $nLimit
        ];

        try {
            $result = $this->ddbClient->query($params);

            foreach ($result['Items'] as $item) {
                $resArray[] = $marshaler->unmarshalItem($item);
            }
        } catch (DynamoDbException $e) {
            echo "Unable to query:\n";
            echo $e->getMessage() . "\n";
        }
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
}
