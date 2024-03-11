<?php

namespace Secotrust\Bundle\SabreDavBundle\Entity;

/**
 * Class PrincipalInterface.
 *
 * @author lduer
 */
interface PrincipalInterface
{
    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * get username.
     *
     * @return string
     */
    public function getUsername();

    /**
     * set username.
     *
     * @param string $username
     */
    public function setUsername($username);

    /**
     * get Email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * set email.
     *
     * @param string $email
     */
    public function setEmail($email);

    /**
     * requried to define me-card as a property on the users' addressbook'.
     *
     * @return string
     */
    public function getVCardUrl();

    /**
     * set vCardUrl.
     *
     * @param string $vCardUrl
     */
    public function setVCardUrl($vCardUrl);
}
