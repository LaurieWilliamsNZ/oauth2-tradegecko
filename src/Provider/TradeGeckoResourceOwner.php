<?php namespace AlexOsborn\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class TradeGeckoResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get resource owner id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->response['user']['id'] ?: null;
    }

    /**
     * Get resource owner email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->response['user']['email'] ?: null;
    }

    /**
     * Get resource owner first name
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->response['user']['first_name'] ?: null;
    }

    /**
     * Get resource owner last name
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->response['user']['last_name'] ?: null;
    }

    /**
     * Get resource owner location
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->response['user']['location'] ?: null;
    }

    /**
     * Get resource owner account id
     *
     * @return string|null
     */
    public function getAccountId()
    {
        return $this->response['user']['account_id'] ?: null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response['user'];
    }
}
