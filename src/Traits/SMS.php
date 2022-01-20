<?php

namespace Zdirnecamlcs96\Helpers\Traits;

use Nexmo\Client as NexmoClient;
use Nexmo\Client\Credentials\Basic as NexmoBasic;

trait SMS {

    use Logging;

    private function __sendIsentricSMS($phone_number, $message, $country_code = null, $ref = null)
    {

        $curl = curl_init();

        $data = array(
            'shortcode' => config("sms.isentric.shortcode"), // required
            'custid' => config("sms.isentric.cust_id"),
            'rmsisdn' => rawurlencode($country_code . $phone_number),
            'smsisdn' => config("sms.isentric.smsisdn"), // required
            'mtid' => $ref, // refer datatabase
            'mtprice' => '000',
            'productCode' => '',
            'productType' => 4,
            'keyword' => '',
            'dataEncoding' => 0,
            'dataStr' => stripslashes(config('app.name') . ": " . $message),
            'dataUrl' => '',
            'dnRep' => 0,
            'groupTag' => 10,
        );

        $url = "http://203.223.130.118/ExtMTPush/extmtpush";

        $data = http_build_query($data);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url . '?' . $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $this->__errorLog("cURL Error #:" . $err);
            return $err;
        } else {
            $this->__normalLog(json_decode($response));
            return $response;
        }

    }

    private function __sendOneWaySMS($country_code, $phone_number, $message)
    {
        if (config("sms.onewaysms.enable")) {

            $apiusername = config("sms.onewaysms.username");
            $apipassword = config("sms.onewaysms.password");
            $sms_from = 'INFO';

            $data = [
                'apiusername' => $apiusername,
                'apipassword' => $apipassword,
                'senderid' => rawurlencode($sms_from),
                'mobileno' => rawurlencode($country_code . $phone_number),
                'message' => stripslashes(config('app.name') . ": " . $message),
                'languagetype' => 1
            ];

            $this->__normalLog('SMS triggered: ' . var_export($data, true));

            $url = "http://gateway80.onewaysms.com.my/api2.aspx";

            $data = http_build_query($data);

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url . "?" . $data,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_TIMEOUT => 30000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                ],
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $this->__errorLog("cURL Error #:" . $err);
                return $err;
            } else {
                $this->__normalLog(json_decode($response));
                return $response;
            }
        }
    }

    private function __sendNexmoSMS($country_code, $phone_number, $message)
    {
        if (config('sms.nexmo.enable')) {

            $api_key = config("sms.nexmo.api_key");
            $secret_key = config("sms.nexmo.secret_key");

            $client = new NexmoClient(new NexmoBasic($api_key, $secret_key));

            try {
                $message = $client->message()->send([
                    'to' => $country_code . $phone_number,
                    'from' => config('app.name'),
                    'text' => $message
                ]);
                $response = $message->getResponseData();

                if ($response['messages'][0]['status'] == 0) {
                    $this->__normalLog(json_decode($response));
                    return $response;
                } else {
                    return $response['messages'][0]['status']; // Error Status
                }
            } catch (Exception $e) {
                $this->__errorLog("Nexmo SMS Error #:" . $e->getMessage());
            }

        }
    }

    function __sendSMS($country_code, $phone_number, $message, $ref = null)
    {
        $sms_prefix = config('sms.prefix');

        $type = config('sms.driver');
        if ($type == "isentric") {
            return $this->__sendIsentricSMS($phone_number, $sms_prefix.$message, $country_code, $ref);
        }
        if ($type == "onewaysms") {
            return $this->__sendOneWaySms($country_code, $phone_number, $sms_prefix.$message);
        }
        if ($type == "nexmo") {
            return $this->__sendNexmoSMS($country_code, $phone_number, $sms_prefix.$message);
        }

        return;
    }
}