<?php

namespace Secotrust\Bundle\SabreDavBundle\SabreDav;

use Sabre\DAVACL\Plugin;
use Sabre\DAVACL\IACL;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class extends the SabreDAV ACL Plugin to provide some additional methods and setter for public variables.
 * 
 * @author lduer
 */
class AclPlugin extends Plugin
{
    /**
     * the "parent" node (Addressbook for Cards, Calendar for CalendarObject).
     *
     * This is set in the addressbook- or calendar-acl-request and
     * used in all sub-requests (cards & events)
     *
     * @var IACL
     */
    private $groupNode;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authChecker;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \$davSecurityService
     */
    private $davSecurity;

    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authChecker
     * @param ContainerInterface            $container
     */
    public function __construct(AuthorizationCheckerInterface $authChecker, ContainerInterface $container)
    {
        $this->authChecker = $authChecker;
        $this->container = $container;
    }

    /**
     * By default nodes that are inaccessible by the user, can still be seen
     * in directory listings (PROPFIND on parent with Depth: 1).
     * 
     * @param bool $flag
     */
    public function setHideNodesFromListings($flag = false)
    {
        $this->hideNodesFromListings = (bool) $flag;
    }

    /**
     * By default ACL is only enforced for nodes that have ACL support (the
     * ones that implement IACL). For any other node, access is
     * always granted.
     *
     * To override this behaviour you can turn this setting off. This is useful
     * if you plan to fully support ACL in the entire tree.
     * 
     * @param bool $flag
     */
    public function setAccessToNodesWithoutACL($flag = true)
    {
        $this->allowAccessToNodesWithoutACL = (bool) $flag;
    }

    /**
     * This string is prepended to the username of the currently logged in
     * user. This allows the plugin to determine the principal path based on
     * the username.
     * 
     * @param string $usernamePath
     */
    public function setDefaultUsernamePath($usernamePath = 'principals')
    {
        $this->defaultUsernamePath = $usernamePath;
    }

    /**
     * add a principal to the admin-list to automatically receive {DAV:}all privileges.
     * 
     * @param string $principal
     *
     * @return bool
     */
    public function addAdminPrincipal($principal)
    {
        if (strpos($principal, $this->defaultUsernamePath.'/') !== 0) {
            $principal = $this->defaultUsernamePath.'/'.$principal;
        }

        if (!in_array($principal, $this->adminPrincipals)) {
            $this->adminPrincipals[] = $principal;

            return true;
        }

        return false;
    }

    /**
     * remove principal from admin-list.
     * 
     * @param string $principal
     *
     * @return bool
     */
    public function removeAdminPrincipal($principal)
    {
        if (strpos($principal, $this->defaultUsernamePath.'/') !== 0) {
            $principal = $this->defaultUsernamePath.'/'.$principal;
        }

        if (false !== ($key = \array_search($principal, $this->adminPrincipals))) {
            unset($this->adminPrincipals[$key]);

            return true;
        }

        return false;
    }

    /**
     * Returns the full ACL list.
     *
     * Either a uri or a DAV\INode may be passed.
     *
     * null will be returned if the node doesn't support ACLs.
     *
     * @param string|\Sabre\DAV\INode $node
     *
     * @return array
     */
    public function getACL($node)
    {
        if (is_string($node)) {
            $node = $this->server->tree->getNodeForPath($node);
        }

        if (!$node instanceof IACL) {
            return;
        }

        $username = $this->server->httpRequest->getCurrentUsername();
        $acl = array();

        $this->davSecurity = $this->container->get('secotrust.sabredav_acl_securityManager');

        if (!is_null($this->davSecurity) && (
                $node instanceof \Sabre\CalDAV\Calendar ||
                $node instanceof \Sabre\CalDAV\CalendarObject ||
                $node instanceof \Sabre\CardDAV\AddressBook ||
                $node instanceof \Sabre\CardDAV\Card
                )) {
            $objectClass = '';
            $objectIdentifier = array('name' => $node->getName());

            if ($node instanceof \Sabre\CardDAV\AddressBook) {
                $objectClass = $this->container->getParameter('secotrust.addressbooks_class');
                $objectIdentifier = $node->getProperties(['id']);
                $this->groupNode = $objectIdentifier;
            } elseif ($node instanceof \Sabre\CardDAV\Calendar) {
                $objectClass = $this->container->getParameter('secotrust.calendar_class');
                $this->groupNode = $objectIdentifier;
            } elseif ($node instanceof \Sabre\CardDAV\CalendarObject) {
                $objectClass = $this->container->getParameter('secotrust.calendarobject_class');
            } elseif ($node instanceof \Sabre\CardDAV\Card) {
                $objectClass = $this->container->getParameter('secotrust.cards_class');
            }

            // load the permission-list from the davSecurity-Service
            $permissionList = $this->davSecurity->getACL(
                    $username, $objectClass, $objectIdentifier, $this->groupNode
            );

            if ($permissionList === null) {
                // use the "default" ACL from the current node
                $acl = $node->getACL();
            } else {
                // write permissions to DAV-ACL
                foreach ($permissionList as $permission) {
                    $acl[] = array(
                        'privilege' => '{DAV:}'.$permission,
                        'principal' => $this->defaultUsernamePath.'/'.$username,
                        'protected' => true,
                    );
                }
            }
        } elseif (!($node instanceof \Sabre\DAVACL\Principal && $node->getName() !== $username)) {
            // get node-acl; if node is a principal-node 
            // and the name is not like the current username, don't display the node-acl
            $acl = $node->getACL();
        }

        // add admin-privileges for all adminPrincipals 
        foreach ($this->adminPrincipals as $adminPrincipal) {
            $acl[] = array(
                'principal' => $adminPrincipal,
                'privilege' => '{DAV:}all',
                'protected' => true,
            );
        }

        return $acl;
    }
}
