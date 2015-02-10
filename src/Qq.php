<?php

namespace XiangminWang\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Qq extends League\OAuth2\Client\Provider\AbstractProvider
{
    public $responseType = 'string';
    public $domain = 'https://graph.qq.com/oauth2.0';
    public $apiDomain = 'https://graph.qq.com/user';
    public function urlAuthorize()
    {
        return $this->domain.'/authorize';
    }
    public function urlAccessToken()
    {
        return $this->domain.'/token';
    }
    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return $this->domain.'/me?access_token='.$token;
    }
    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User();
        $name = (isset($response->name)) ? $response->name : null;
        $email = (isset($response->email)) ? $response->email : null;
        $imageUrl = (isset($response->figureurl)) ? $response->figureurl : null;
        $user->exchangeArray([
            'uid' => $response->openid,
            'nickname' => $response->nickname,
            'name' => $name,
            'email' => $email,
            'imageUrl' => $figureurl,
            'urls'  => null,
        ]);
        return $user;
    }
    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->id;
    }
}