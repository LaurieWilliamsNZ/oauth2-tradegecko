<?php namespace AlexOsborn\OAuth2\Client\Test\Provider;

use Mockery as m;

class TradeGeckoTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;

    protected function setUp()
    {
        $this->provider = new \AlexOsborn\OAuth2\Client\Provider\TradeGecko([
            'clientId'     => 'mock_client_id',
            'clientSecret' => 'mock_client_secret',
            'redirectUri'  => 'redirect_url',
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }


    public function testScopes()
    {
        $options = ['scope' => [uniqid(),uniqid()]];

        $url = $this->provider->getAuthorizationUrl($options);

        $this->assertContains(urlencode(implode(',', $options['scope'])), $url);
    }

    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauth/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/oauth/token', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token","scopes": "account","expires_in": 3600,"refresh_token": "mock_refresh_token","token_type": "bearer"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertEquals('mock_refresh_token', $token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testUserData()
    {
        $userId    = rand(1000, 9999);
        $email     = uniqid();
        $firstName = uniqid();
        $lastName  = uniqid();
        $location  = uniqid();
        $accountId = rand(1000, 9999);

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token","scopes": "account","expires_in": 3600,"refresh_token": "mock_refresh_token","token_type": "bearer"}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn('{"user": {"id": ' . $userId . ',"created_at": "2015-11-02T01:22:23.877Z","updated_at": "2015-11-02T01:22:23.920Z","action_items_email": "weekly","avatar_url": "/assets/avatars/avatar1-70fa2b37c7341a16d9ef72fc6b0f961c253965e9c395fc063ad0165bc65b7167.png","billing_contact": true,"email": "' . $email . '","first_name": "' . $firstName . '","last_name": "' . $lastName . '","last_sign_in_at": "2015-11-17T06:35:25.521Z","location": "' . $location . '","login_id": 1,"mobile": null,"notification_email": true,"permissions": ["read_reports","write_stocks","write_orders","write_products","write_settings","write_companies","read_buy_prices"],"phone_number": null,"position": null,"sales_report_email": true,"status": "active","account_id": ' . $accountId . '}}');
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertEquals($userId, $user->getId());
        $this->assertEquals($userId, $user->toArray()['id']);
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($email, $user->toArray()['email']);
        $this->assertEquals($firstName, $user->getFirstName());
        $this->assertEquals($firstName, $user->toArray()['first_name']);
        $this->assertEquals($lastName, $user->getLastName());
        $this->assertEquals($lastName, $user->toArray()['last_name']);
        $this->assertEquals($location, $user->getLocation());
        $this->assertEquals($location, $user->toArray()['location']);
        $this->assertEquals($accountId, $user->getAccountId());
        $this->assertEquals($accountId, $user->toArray()['account_id']);
    }

    public function testUserDataFails()
    {
        $errorPayloads = [
            '{"error":"mock_error","error_description": "mock_error_description"}',
            '{"error":{"message":"mock_error"},"error_description": "mock_error_description"}',
            '{"foo":"bar"}'
        ];

        $testPayload = function ($payload) {
            $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
            $postResponse->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token","scopes": "account","expires_in": 3600,"refresh_token": "mock_refresh_token","token_type": "bearer"}');
            $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

            $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
            $userResponse->shouldReceive('getBody')->andReturn($payload);
            $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
            $userResponse->shouldReceive('getStatusCode')->andReturn(500);

            $client = m::mock('GuzzleHttp\ClientInterface');
            $client->shouldReceive('send')
                ->times(2)
                ->andReturn($postResponse, $userResponse);
            $this->provider->setHttpClient($client);

            $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

            try {
                $user = $this->provider->getResourceOwner($token);
                return false;
            } catch (\Exception $e) {
                $this->assertInstanceOf('\League\OAuth2\Client\Provider\Exception\IdentityProviderException', $e);
            }

            return $payload;
        };

        $this->assertCount(2, array_filter(array_map($testPayload, $errorPayloads)));
    }
}
