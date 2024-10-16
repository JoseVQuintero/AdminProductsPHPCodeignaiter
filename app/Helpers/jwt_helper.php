<?php

use Config\Services;
use Firebase\JWT\JWT;
use App\Models\UserModel;

function getJWTFromRequest($authenticationHeader): string
{
    if (is_null($authenticationHeader)) {
        throw new Exception('Missing or invalid JWT in request');
    }

    return explode(' ', $authenticationHeader)[1];
}

function validateJWTFromRequest(string $encodedToken)
{
    $key = Services::getSecretKey();
    $decodedToken = JWT::decode($encodedToken, $key, ['HS256']);
    $userModel = new UserModel();
    $userModel->findUserByEmailAddress($decodedToken->email);
}

function validateJWTFromRequestSecret($payload,string $encodedToken/*,$userId*/){
    /*$key = Services::getSecretKey();
    $decodedToken = JWT::decode($encodedToken, $key, ['HS256']);*/
    $userModel = new UserModel();
    return $userModel->findUserBySecret($payload,$encodedToken/*,$userId*/);
}

function getSignedJWTForUser(string $email): string
{
    $issuedAtTime = time();
    $tokenTimeToLive = getenv('JWT_TIME_TO_LIVE');
    $tokenExpiration = $issuedAtTime + $tokenTimeToLive;
    $payload = [
        'email' => $email,
        'iat' => $issuedAtTime,
        'exp' => $tokenExpiration
    ];

    $jwt = JWT::encode($payload, Services::getSecretKey());

    return $jwt;
}

function getSignedJWTForSecret(string $email,string $userToken): string
{
    $issuedAtTime = time();
    $tokenTimeToLive = getenv('JWT_TIME_TO_LIVE_SECRET');
    $tokenExpiration = $issuedAtTime + $tokenTimeToLive;
    $payload = [
        'usertoken'=>$userToken,
        'email' => $email,
        'iat' => $issuedAtTime,
        'exp' => $tokenExpiration
    ];

    $jwt = JWT::encode($payload, Services::getSecretKey());

    return $jwt;
}