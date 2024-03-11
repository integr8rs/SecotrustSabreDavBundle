<?php

/*
 * This file is part of the SecotrustSabreDavBundle package.
 *
 * (c) Henrik Westphal <henrik.westphal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Secotrust\Bundle\SabreDavBundle\SabreDav;

use Sabre\DAV\Auth\Backend\BackendInterface;
use Sabre\DAV\Exception;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;

class AuthBackend implements BackendInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \FOS\UserBundle\Model\UserManagerInterface
     */
    private $user_manager;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    private $token_storage;

    /**
     * @var \Symfony\Component\Serializer\Encoder\EncoderInterface
     */
    private $encoder_service;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    protected $currentUser;

    /**
     * Authentication Realm.
     *
     * The realm is often displayed by browser clients when showing the
     * authentication dialog.
     *
     * @var string
     */
    protected $realm = 'SabreDAV';

    /**
     * This is the prefix that will be used to generate principal urls.
     *
     * @var string
     */
    protected $principalPrefix = 'principals/';

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param $realm
     */
    public function __construct(ContainerInterface $container, $realm)
    {
        $this->container = $container;
        $this->realm = $realm;

        $this->user_manager = $this->container->get('fos_user.user_manager');
        $this->token_storage = $this->container->get('security.token_storage');
        $this->encoder_service = $this->container->get('security.encoder_factory');
        $this->dispatcher = $this->container->get('event_dispatcher');
    }

    /**
     * Checks if username and password are valid. (Checked by the FOSUserManager)
     * Returns.
     *
     * @param $username
     * @param $passwordHash
     *
     * @return bool
     */
    public function validateUserPass($username, $passwordHash)
    {
        $user = $this->user_manager->findUserByUsername($username);

        if (is_null($user)) {
            return false;
        }

        if ($passwordHash === $user->getPassword()) {
            //            $this->userLoginAction($user, $passwordHash);
            return true;
        }

        return false;
    }

    /**
     * add the symfony-login "manually".
     * 
     * use the symfony token-storage for the generated UsernamePasswordToken 
     * to access the (logged in) user later (e.g. to check for roles or permissions)
     * 
     * the given $passwordHash must match the encrypted password in the user-object
     * 
     * before and after the generation/setting of the token, the events "secotrust.user_login.before" 
     * and "secotrust.user_login.after" are called, if some EventListeners are configured
     *
     * @param \FOS\UserBundle\Model\UserInterface $user
     * @param $passwordHash
     */
    private function userLoginAction(\FOS\UserBundle\Model\UserInterface $user, $passwordHash)
    {
        // call the pre-login-event        
        $event = new Event();
        $this->dispatcher->dispatch('secotrust.user_login.before', $event);

        if ($user->getPassword() !== $passwordHash) {
            // stop the login-action, when the password doesn't match
            return;
        }

        $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
        $this->token_storage->setToken($token);

        // call the post-login-event
        $event = new Event();
        $this->dispatcher->dispatch('secotrust.user_login.after', $event);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    /**
     * When this method is called, the backend must check if authentication was
     * successful.
     *
     * The returned value must be one of the following
     *
     * [true, "principals/username"]
     * [false, "reason for failure"]
     *
     * If authentication was successful, it's expected that the authentication
     * backend returns a so-called principal url.
     *
     * Examples of a principal url:
     *
     * principals/admin
     * principals/user1
     * principals/users/joe
     * principals/uid/123457
     *
     * If you don't use WebDAV ACL (RFC3744) we recommend that you simply
     * return a string such as:
     *
     * principals/users/[username]
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @return array
     *
     * @throws Exception
     */
    public function check(RequestInterface $request, ResponseInterface $response)
    {
        $auth = new Auth\BasicAuth($this->realm, $request, $response, $this->user_manager);
        $userpass = $auth->getCredentials($this->encoder_service);

        // No username was given
        if ($userpass === false) {
            return [false, "No 'Authorization' header found. Either the client didn't send one, or the server is mis-configured"];
        }

        // Authenticates the user
        if (!$this->validateUserPass($userpass[0], $userpass[1])) {
            return [false, 'Username or password was incorrect'];
        }

        $this->currentUser = $userpass[0];
        $request->setCurrentUsername($this->currentUser);

        return [true, $this->principalPrefix.$userpass[0]];
    }

    /**
     * This method is called when a user could not be authenticated, and
     * authentication was required for the current request.
     *
     * This gives you the opportunity to set authentication headers. The 401
     * status code will already be set.
     *
     * In this case of Basic Auth, this would for example mean that the
     * following header needs to be set:
     *
     * $response->addHeader('WWW-Authenticate', 'Basic realm=SabreDAV');
     *
     * Keep in mind that in the case of multiple authentication backends, other
     * WWW-Authenticate headers may already have been set, and you'll want to
     * append your own WWW-Authenticate header instead of overwriting the
     * existing one.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     */
    public function challenge(RequestInterface $request, ResponseInterface $response)
    {
        $auth = new Auth\BasicAuth($this->realm, $request, $response, $this->user_manager);
        $userpass = $auth->getCredentials($this->encoder_service);

        if (!$userpass) {
            $auth->requireLogin();
        }

        // Authenticates the user
        if (!$this->validateUserPass($userpass[0], $userpass[1])) {
            $auth->requireLogin();
        }

        $this->currentUser = $userpass[0];
        $request->setCurrentUsername($this->currentUser);
    }
}
