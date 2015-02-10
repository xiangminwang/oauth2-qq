<?php

namespace XiangminWang\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Qq extends \League\OAuth2\Client\Provider\AbstractProvider
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
    public function getUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        $openid_response = $this->fetchUserDetails($token);
        // the response of QQ openAPI need special handling
        //
        // eg: "callback( {"client_id":"101083709","openid":"A201E2458192683555A3069B59FDF47C"} );
        // "
        // transform the response of request openid
        $first_open_brace_pos = strpos($openid_response, '{');
        $last_close_brace_pos = strrpos($openid_response, '}');
        $openid_response = json_decode(substr(
            $openid_response,
            $first_open_brace_pos,
            $last_close_brace_pos - $first_open_brace_pos + 1
        ));
        $this->openid = $openid_response->openid;
        // fetch QQ user profile
        $params = array(
            'access_token' => $token->accessToken,
            'oauth_consumer_key' => $this->clientId,
            'openid' => $openid_response->openid
        );
        $response = $this->httpClient->get($apiDomain . '/get_user_info?' . http_build_query($params));
        // check response
        if (is_array($response) && (isset($response['error']) || isset($response['message']))) {
            throw new \League\OAuth2\Client\Exception\IDPException($response);
        }
        $response = json_decode($response);
        if (!isset($response->ret) || $response->ret != 0) {
            $result['code'] = $response->ret;
            $result['message'] = $response->msg;
            throw new \League\OAuth2\Client\Exception\IDPException($result);
        }
        return $this->userDetails($response, $token);
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