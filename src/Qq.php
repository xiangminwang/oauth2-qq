<?php

namespace XiangminWang\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Qq extends \League\OAuth2\Client\Provider\AbstractProvider
{
    public $responseType = 'string';
    public $domain = 'https://graph.qq.com/oauth2.0';
    public $apiDomain = 'https://graph.qq.com/user';
    public $openid = ''; // only stupid tencent offers this..

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

    public function getUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        // Fetching openid from '/me' with access_token
        $openid_response = $this->fetchUserDetails($token);

        // pickup openid
        $first_open_brace_pos = strpos($openid_response, '{');
        $last_close_brace_pos = strrpos($openid_response, '}');
        $openid_response = json_decode(substr(
            $openid_response,
            $first_open_brace_pos,
            $last_close_brace_pos - $first_open_brace_pos + 1
        ));

        $this->openid = $openid_response->openid;

        // fetch QQ user profile
        $params = [
            'access_token' => $token->accessToken,
            'oauth_consumer_key' => $this->clientId,
            'openid' => $this->openid
        ];

        $request = $this->httpClient->get($this->apiDomain . '/get_user_info?' . http_build_query($params));
        $response = json_decode($request->send()->getBody());

        // check response status
        if ($response->ret < 0) {
            // handle tencent's style exception.
            $result['code'] = $response->ret;
            $result['message'] = $response->msg;
            throw new \League\OAuth2\Client\Exception\IDPException($result);
        }

        return $this->userDetails($response, $token);
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User();
        $gender = (isset($response->gender)) ? $response->gender : null;
        $province = (isset($response->province)) ? $response->province : null;
        $imageUrl = (isset($response->figureurl)) ? $response->figureurl : null;
        $user->exchangeArray([
            'uid' => $this->openid,
            'nickname' => $response->nickname,
            'gender' => $gender,
            'province' => $province,
            'imageUrl' => $imageUrl,
            'urls'  => null,
        ]);

        return $user;
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->id;
    }
}