<?php

namespace Ferus\UserBundle\Entity;

use Ferus\AccountBundle\Entity\Account;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 */
class User extends BaseUser
{

    /**
     * @var Account
     */
    private $account;

    /**
     * @var string
     */
    private $google_id;

    /**
     * @var string
     */
    private $google_access_token;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * @param \Ferus\AccountBundle\Entity\Account $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * @return \Ferus\AccountBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set google_id
     *
     * @param string $googleId
     * @return User
     */
    public function setGoogleId($googleId)
    {
        $this->google_id = $googleId;

        return $this;
    }

    /**
     * Get google_id
     *
     * @return string 
     */
    public function getGoogleId()
    {
        return $this->google_id;
    }

    /**
     * Set google_access_token
     *
     * @param string $googleAccessToken
     * @return User
     */
    public function setGoogleAccessToken($googleAccessToken)
    {
        $this->google_access_token = $googleAccessToken;

        return $this;
    }

    /**
     * Get google_access_token
     *
     * @return string 
     */
    public function getGoogleAccessToken()
    {
        return $this->google_access_token;
    }
}
