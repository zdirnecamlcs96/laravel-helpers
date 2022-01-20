<?php

namespace Zdirnecamlcs96\Helpers\Traits;

trait FCM {

    use Logging;

    function __sendFirebaseCloudMessagingToken($tokens, $type, $title, $text, $type_id = null, bool $silence = false, $sound = null) // https://stackoverflow.com/questions/39375200/fcm-message-to-multiple-registration-ids-limit
    {
        if(is_array($tokens)){
            $tokens = array_values(array_filter(array_unique($tokens)));
            $this->__normalLog('Sending FCM Token: ' . implode(', ', $tokens));
        }else{
            $this->__normalLog('Sending FCM Token: ' . $tokens);
        }

        if (!config('others.fcm.enabled')) {
            return false;
        }

        /**
         * registration_ids: multiple token array
         * to: single token
         */
        $tokenName = is_array($tokens) ? 'registration_ids' : 'to';

        /**
         * For in apps handling
         */
        $extraNotificationData = [
            "click_action" => "FLUTTER_NOTIFICATION_CLICK",
            "message" => $text,
            "type" => $type,
            "type_id" => $type_id,
            'title' => $title,
            "silence" => (int)$silence
        ];

        $data = [
            "$tokenName" => $tokens,
            'data' => $extraNotificationData,
            'priority' => 'high',
            'badge' => 1,
            'content_available' => $silence // set 'true' if need silent IOS notification
        ];

        if(!$silence) { // For Android silent notification
            $data = array_merge($data, [
                'notification' => [
                    'title' => $title,
                    'text' => $text,
                    'body' => $text, // body used for iOS
                    'android_channel_id' => 'push_noti_roger_squad',
                    'sound' => $sound != null ? $sound : true,
                    'icon' => asset('favicon.png')
                ]
            ]);
        }

        $url = config('others.fcm.url');

        $headers = [
            'Authorization: key=' . config('others.fcm.secret'),
            'Content-Type: application/json'
        ];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($ch);
        curl_close($ch);

        $this->__normalLog("FCM Token Sent.(" . (is_array($tokens) ? implode(', ', $tokens) : $tokens) . ")" . json_encode($result));

        return true;
    }
}