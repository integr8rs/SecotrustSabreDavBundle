<?php

namespace Secotrust\Bundle\SabreDavBundle\SabreDav\Acl;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Description of SecurityManager.
 *
 * @author lduer
 */
class SecurityManager
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    protected $token;

    /**
     * authorization checker.
     * 
     * @var Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    protected $authorizationChecker;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->token = $container->get('security.token_storage')->getToken();
        $this->authorizationChecker = $container->get('security.authorization_checker');
    }

    /**
     * returns the ACL list in the following format:<br>
     * <code>   return array('read', 'write', 'delete');</code>.
     * 
     * consider: 
     * null will be returned to tell the AclPlugin to use the default Node-Acl (e.g. if no ACL was found for this entry)
     * 
     * an empty array will be returned, if the user has no permissions for the current object.
     *
     * @param string $username
     * @param $objectClass
     * @param $objectIdentifier
     * @param null $groupIdentifier
     */
    public function getACL($username, $objectClass, $objectIdentifier, $groupIdentifier = null)
    {
        return;
    }
}
