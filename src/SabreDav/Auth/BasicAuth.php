<?php

namespace Secotrust\Bundle\SabreDavBundle\SabreDav\Auth;

use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Sabre\HTTP\Auth\Basic;

/**
 * BasicAuth.
 * 
 * This file extends the basic-auth from sabre-dav
 *
 * @author lduer
 */
class BasicAuth extends Basic
{
    /**
     * @var UserManagerInterface
     */
    private $user_manager;

    /**
     * Constructor.
     * 
     * @param string                        $realm
     * @param \Sabre\HTTP\RequestInterface  $request
     * @param \Sabre\HTTP\ResponseInterface $response
     * @param UserManagerInterface          $user_manager
     */
    public function __construct($realm, RequestInterface $request, ResponseInterface $response, UserManagerInterface $user_manager)
    {
        $this->user_manager = $user_manager;
        parent::__construct($realm, $request, $response);
    }

    /**
     * find username in the user-manager.
     * 
     * @param string $username
     *
     * @return \FOS\UserBundle\Model\UserInterface
     */
    private function getUser($username)
    {
        $user = $this->user_manager->findUserByUsername($username);

        return $user;
    }

    /**
     * Return user-credentials; returned password is encoded via "security.encoder_factory".
     * 
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoder_service
     *
     * @return array|bool
     */
    public function getCredentials(EncoderFactoryInterface $encoder_service = null)
    {
        if (($user = $this->request->getRawServerValue('PHP_AUTH_USER')) && ($pass = $this->request->getRawServerValue('PHP_AUTH_PW'))) {
            $credentials = array($user, $pass);
        } else {

            // Most other webservers
            $auth = $this->request->getHeader('Authorization');

            // Apache could prefix environment variables with REDIRECT_ when urls
            // are passed through mod_rewrite
            if (!$auth) {
                $auth = $this->request->getRawServerValue('REDIRECT_HTTP_AUTHORIZATION');
            }

            if (!$auth) {
                return false;
            }

            if (strpos(strtolower($auth), 'basic') !== 0) {
                return false;
            }

            $credentials = explode(':', base64_decode(substr($auth, 6)), 2);
        }

        $user = $this->getUser($credentials[0]);

        if (!$user) {
            return false;
        }

        if ($encoder_service === null) {
            // don't return password, because it isn't encoded
            return array($credentials[0], '');
        }

        $encoder = $encoder_service->getEncoder($user);
        $encoded_pass = $encoder->encodePassword($credentials[1], $user->getSalt());

        return array($credentials[0], $encoded_pass);
    }
}
