<?php

function sendEmail($dataSend){

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://18.222.56.180:3008/sendemail',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($dataSend,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));
    $response = curl_exec($curl);
    
    curl_close($curl);
}

function getSend($dataUser, $htmlData=null, $message=null){
    $shopDefault = (isset($dataUser['access']))? $dataUser['access']['shop'][$dataUser['access']['shopdefault']] : $dataUser;
    if (!is_null($shopDefault['emailto']) && !empty($shopDefault['emailto'])) {

        $htmlData = (is_null($htmlData)) ? '<table><thead><tr><th>utilidad</th><th>iva</th></tr></thead><tbody><tr><td>' . $shopDefault['storeutility'] . '</td><td>' . $shopDefault['iva'] . '</td></tr></tbody></table>': $htmlData;
        $message = (is_null($message))?"Cambio en configuración": $message;

        $sendEmail = [
            'email' => $shopDefault['emails'],
            'emailto' => $shopDefault['emailto'],
            'emailfrom' => $shopDefault['emailfrom'],
            'html' => $htmlData,
            'subject' => $message,
            'host' => $shopDefault['host'],
            'user' => $shopDefault['hostuser'],
            'pass' => $shopDefault['hostpass'],
        ];

        sendEmail($sendEmail);
    }
}