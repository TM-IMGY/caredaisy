<?php
namespace App\Utility;

use Log;
use Exception;

class SlackUtility
{
    /**
     * Slackに通知投げます
     * @param string $message 通知させたい文字列
     * @param mix $channel チャンネルを指定したいときは文字列でもらう想定(ex: #kaigo_dev)
     * @return bool
     */
    public static function notification($message, $channel=false){
        $result = true;
        $channel = $channel ? $channel : config("slack.notification_channel");

        try{
            $message = [
                "channel" => $channel,
                "text" => $message
            ];

            $ch = curl_init();

            $options = [
                CURLOPT_URL => config("slack.notification_url"),
                // 返り値を文字列で返す
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                // POST
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'payload' => json_encode($message)
                ])
            ];

            curl_setopt_array($ch, $options);
            curl_exec($ch);
            $errno = curl_errno($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($errno) {
                throw new Exception($error);
            }

        }catch(Exception $e){
            Log::error("Slackへの通知に失敗しました : ".$e->getMessage());
            $result = false;
        }
        return $result;
    }
}
