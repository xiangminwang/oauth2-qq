# OAuth2-QQ
QQ provider for league/oauth2-client

## Installation
```
composer require xiangminwang/oauth2-qq
```

## Usage
```
$provider = new XiangminWang\OAuth2\Client\Provider\Qq([
    'clientId' => '1104233555', // fill your "APP ID"
    'clientSecret' => '978N7tmeH7TMQsKy', // fill your "APP KEY"
    'redirectUri' => 'http://example.com/oauth-endpoint',
]);
```

## TODO

- [ ] Beta version
- [ ] Unit tests
- [x] Full feature support
