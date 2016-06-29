# TradeGeko Provider for OAuth 2.0 Client

[![Latest Version](https://img.shields.io/github/release/alex-osborn/oauth2-tradegecko.svg?style=flat-square)](https://github.com/alex-osborn/oauth2-tradegecko/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/alex-osborn/oauth2-tradegecko/master.svg?style=flat-square)](https://travis-ci.org/alex-osborn/oauth2-tradegecko)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/alex-osborn/oauth2-tradegecko.svg?style=flat-square)](https://scrutinizer-ci.com/g/alex-osborn/oauth2-tradegecko/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/alex-osborn/oauth2-tradegecko.svg?style=flat-square)](https://scrutinizer-ci.com/g/alex-osborn/oauth2-tradegecko)
[![Total Downloads](https://img.shields.io/packagist/dt/alex-osborn/oauth2-tradegecko.svg?style=flat-square)](https://packagist.org/packages/alex-osborn/oauth2-tradegecko)

This package provides TradeGecko OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

See [http://developer.tradegecko.com/](http://developer.tradegecko.com/).

## Installation

To install, use composer:

```
composer require alex-osborn/oauth2-tradegecko
```

## Usage

Usage is the same as The League's OAuth client, using `\AlexOsborn\OAuth2\Client\Provider\TradeGecko` as the provider.

### Authorization Code Flow

```php
$provider = new AlexOsborn\OAuth2\Client\Provider\TradeGecko([
    'clientId'          => '{tradegecko-client-id}',
    'clientSecret'      => '{tradegecko-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url'
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getId());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/alex-osborn/oauth2-tradegecko/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Alex Osborn](https://github.com/alex-osborn)
- [Steven Maguire](https://github.com/stevenmaguire) (Based on github.com/stevenmaguire/oauth2-bitbucket)
- [All Contributors](https://github.com/alex-osborn/oauth2-tradegecko/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/alex-osborn/oauth2-tradegecko/blob/master/LICENSE) for more information.
