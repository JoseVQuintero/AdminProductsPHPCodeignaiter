<?php

use Config\Services;
use Firebase\JWT\JWT;

function token(){
    return [
        'username'=>'APP_User_XxswXE#$%&TFRDEfgSWDRxc456677RTYTRV%&/=2233e',
        'tokePass' => 'APP_Pass_bdicronWE"#!"#$/$GDERDFTRE'
    ];
}

function tokenGuest()
{
    return [
        'username' => 'APP_User_GuestXxswXE#456677RTYTRV%Guest',
        'tokePass' => 'APP_Pass_Guest_bdicronWE"#!"#$/$GDERDFTREGuest'
    ];
}

function getCronJWTFromRequest($authenticationHeader): string
{
    if (is_null($authenticationHeader)) {
        throw new Exception('Missing or invalid JWT in request');
    }

    return explode(' ', $authenticationHeader)[1];
}

function validateCronJWTFromRequest(string $encodedToken)
{
    $key = Services::getSecretKey();
    $decodedToken = JWT::decode($encodedToken, $key, ['HS256']);
    if($decodedToken->username != token()['username'] && $decodedToken->password != token()['tokePass']){
        if($decodedToken->username != tokenGuest()['username'] && $decodedToken->password != tokenGuest()['tokePass']){
            throw new Exception('User does not exist for specified');
        }
    }
}

function getSignedCronJWTForUser(string $username, string $password): string
{
    $issuedAtTime = time();
    $tokenTimeToLive = getenv('JWT_TIME_TO_LIVE');
    $tokenExpiration = $issuedAtTime + $tokenTimeToLive;
    $payload = [
        'password'=> $password,
        'username' => $username,
        'iat' => $issuedAtTime,
        'exp' => $tokenExpiration
    ];

    $jwt = JWT::encode($payload, Services::getSecretKey());

    return $jwt;
}