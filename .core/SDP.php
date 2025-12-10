<?php

class SDP {

    private $baseUrl;
    private $sdpID;
    private $callbackurl;

    public function __construct() {
        $this->baseUrl      = SDP1;
        $this->sdpID        = SDP_ID;
        $this->callbackurl  = SDP_CALLBACK_URL;
    }

    /*-----------------------------------------------------
     | 1. GENERATE FRESHING TOKEN
     -----------------------------------------------------*/
    public function generateFreshingToken($payload) {
        $url = 'api/auth/login';
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Requested-With: XMLHttpRequest'
        ];
        $request = [
            "username" => $payload['username'],
            "password" => $payload['password']
        ];
        return $this->callAPI('POST', $url, $headers, $request);
    }

    /*-----------------------------------------------------
     | 2. GENERATE FRESHING TOKEN
     -----------------------------------------------------*/
    public function generateToken($payload){
        $url = "api/auth/RefreshToken";
        $headers = [
            'Content-Type: application/json',
            'X-Requested-With: XMLHttpRequest',
            'X-Authorization: Bearer '.$payload['token']
        ];
        return $this->callAPI('GET', $url, $headers);
    }

    /*-----------------------------------------------------
     | 2. SEND SINGLE / BULK SMS  
     |    → Used by Kafka consumer and HTTP real-time requests
     -----------------------------------------------------*/
    public function sendSMS($payload) { 
        $url = "api/public/CMS/bulksms";
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Requested-With: XMLHttpRequest',
            'X-Authorization: Bearer '.$payload['token']
        ];
        $payload = [
            "timeStamp" => $payload['timestamp'],
            "dataSet" => [
                [
                    "userName" => $payload['username'],
                    "channel" => "SMS",
                    //"packageId" => 10203, // in case you have multiple package id in your account, don’t specify the package id, remove it completely
                    "oa" => $payload['shortcode'], // "TestTP",
                    "cpPassword" => md5($this->sdpID.$payload['password'].$payload['timestamp']),
                    "msisdn" => $payload['contacts'], // ,254115242477,254728642504,254722636396,254710543307",
                    "message" => $payload['message'],
                    "uniqueId" => $payload['messageId'],
                    "actionResponseURL" => $this->callbackurl.'sdp/v1/dlrbulk',
                ]
            ]
        ];
        return json_encode([
            "keyword"    => "Bulk",
            "status"     => "SUCCESS",
            "statusCode" => "SC0000"
        ]);
        return $this->callAPI('POST', $url, $headers, $payload);
    }

    /*-----------------------------------------------------
     | 3. PROCESS DELIVERY REPORTS (DLR)
     |    → This is called when Safaricom hits TechPitch URL
     -----------------------------------------------------*/
    public function processDLR($input)
    {
        // SDP sends JSON DLR
        $dlr = json_decode($input, true);

        // Store to DB, Kafka, Redis, etc.
        // Example structure:
        // $dlr["deliveryInfoNotification"]["deliveryInfo"]["deliveryStatus"];
        // $dlr["deliveryInfoNotification"]["deliveryInfo"]["address"];

        return [
            "status" => "success",
            "message" => "DLR processed",
            "data" => $dlr
        ];
    }

    /*-----------------------------------------------------
     | 4. PROCESS MO (MESSAGE FROM CUSTOMER)
     |    → Customer replies to shortcode → SDP → TechPitch
     -----------------------------------------------------*/
    public function processMO($input)
    {
        $mo = json_decode($input, true);

        // Example fields:
        // $mo["inboundSMSMessageNotification"]["inboundSMSMessage"]["message"];
        // $mo["inboundSMSMessageNotification"]["inboundSMSMessage"]["senderAddress"];

        return [
            "status" => "success",
            "message" => "MO received",
            "data" => $mo
        ];
    }

    /*-----------------------------------------------------
     | CURL WRAPPER
     -----------------------------------------------------*/
    private function curl($url, $payload = null, $headers = [], $method = "POST")
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($method === "GET") {
            curl_setopt($curl, CURLOPT_HTTPGET, true);
        } else {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response  = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return [
            "http_code" => $http_code,
            "response"  => json_decode($response, true)
        ];
    }

    private function callAPI($method=null, $url=null, $headers=null, $request=null){
        if(is_null($url)) die('Request parameters required');

        $url = $this->baseUrl.$url;

        if(is_array($request)) $request = json_encode($request);   

        $curl = curl_init();

        switch ($method){
            case "POST":
                curl_setopt($curl, CURLOPT_POST, true);
                if($request) curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
                break;

            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if($request) curl_setopt($curl, CURLOPT_POSTFIELDS, $request);                              
                break;

            case "PATCH":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
                if($request) curl_setopt($curl, CURLOPT_POSTFIELDS, $request);                              
                break;

            case "GET":
            default:
                if($request) $url = sprintf("%s?%s", $url, http_build_query(json_decode($request,1)));
        }

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 1
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 2    
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);
        
        // OPTIONS:
        // curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // curl_setopt($curl, CURLOPT_SSLCERT, '/etc/ssl/mycerts/server.crt');
        // curl_setopt($curl, CURLOPT_SSLKEY, '/etc/ssl/mycerts/server.key');
        // curl_setopt($curl, CURLOPT_CAINFO, '/etc/ssl/mycerts/server.crt');

        // EXECUTE:
        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $error = curl_error($curl);
            curl_close($curl);
            return [
                "success" => false,
                "status"  => 0,
                "error"   => "Curl Error: $error",
                "url"     => $url
            ];
        }
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($status >= 200 && $status <= 299) return $response;
        else {
            // Failure
            return [
                "success"  => false,
                "status"   => $status,
                "response" => $response
            ];
        }
    }


}
