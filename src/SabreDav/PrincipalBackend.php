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

use Sabre\DAV\Exception;
use Sabre\DAV\MkCol;
use Sabre\DAVACL\PrincipalBackend\AbstractBackend;
use Sabre\DAVACL\PrincipalBackend\CreatePrincipalSupport;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\GroupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PrincipalBackend extends AbstractBackend implements CreatePrincipalSupport
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $_em;

    /**
     * @var \FOS\UserBundle\Model\UserManagerInterface
     */
    private $user_manager;

    /**
     * @var \FOS\UserBundle\Model\GroupManagerInterface
     */
    private $group_manager;

    /**
     * @var string
     */
    private $principals_class;

    /**
     * @var string
     */
    private $principalgroups_class;

    /**
     * A list of additional fields to support.
     *
     * @var array
     */
    protected $fieldMap = array(
        /*
         * This property can be used to display the users' real name.
         */
        '{DAV:}displayname' => array(
            'getter' => 'getUsername',
            'setter' => 'setUsername',
        ),
        /*
         * This property is actually used by the CardDAV plugin, where it gets
         * mapped to {http://calendarserver.orgi/ns/}me-card.
         *
         * The reason we don't straight-up use that property, is because
         * me-card is defined as a property on the users' addressbook
         * collection.
         */
        '{http://sabredav.org/ns}vcard-url' => array(
            'getter' => 'getVCardUrl',
            'setter' => 'setVCardUrl',
        ),
        /*
         * This is the users' primary email-address.
         */
        '{http://sabredav.org/ns}email-address' => array(
            'getter' => 'getEmail',
            'setter' => 'setEmail',
        ),
    );

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->_em = $container->get('doctrine')->getManager();
        $this->principals_class = $container->getParameter('secotrust.principals_class');
        $this->principalgroups_class = $container->getParameter('secotrust.principalgroups_class');
        $this->user_manager = $container->get('fos_user.user_manager');

        if ($container->has('fos_user.group_manager')) {
            $this->group_manager = $container->get('fos_user.group_manager');
        }
    }

    /**
     * get Array with Principal-Data from User-Object.
     *
     * @param UserInterface|GroupInterface $principalObject
     * @param bool                         $show_id
     *
     * @return array
     *
     * @throws Exception
     */
    private function getPrincipalArray($principalObject, $show_id = false)
    {
        if (!($principalObject instanceof UserInterface) && !($principalObject instanceof GroupInterface)) {
            throw new Exception('$principalObject must be of type UserInterface of GroupInterface');
        }

        $principal = array();
        if ($show_id) {
            $principal['id'] = $principalObject->getId();
        }

        if ($principalObject instanceof UserInterface) {
            $principal['uri'] = 'principals/'.$principalObject->getUsername();
        } else {
            $principal['uri'] = 'principals/'.$principalObject->getName();
        }

        // get all fields from $this->fieldMap, additional to 'uri' and 'id'
        foreach ($this->fieldMap as $key => $value) {
            if (!method_exists($principalObject, $value['getter'])) {
                continue;
            }

            $valueGetter = call_user_func(array($principalObject, $value['getter']));

            if ($valueGetter) {
                $principal[$key] = $valueGetter;
            }
        }

        return $principal;
    }

    /**
     * Returns a list of principals based on a prefix.
     *
     * This prefix will often contain something like 'principals'. You are only
     * expected to return principals that are in this base path.
     *
     * You are expected to return at least a 'uri' for every user, you can
     * return any additional properties if you wish so. Common properties are:
     *   {DAV:}displayname
     *   {http://sabredav.org/ns}email-address - This is a custom SabreDAV
     *     field that's actually injected in a number of other properties. If
     *     you have an email address, use this property.
     *
     * @param string $prefixPath
     *
     * @return array
     */
    public function getPrincipalsByPrefix($prefixPath)
    {
        $userlist = $this->user_manager->findUsers();
        $principals = array();

        foreach ($userlist as $user) {

            // due to the lack of the implementation of prefixes, return all users
            $principals[] = $this->getPrincipalArray($user);
        }

        return $principals;
    }

    /**
     * Returns a specific principal, specified by it's path.
     * The returned structure should be the exact same as from
     * getPrincipalsByPrefix.
     *
     * @param string $path
     * @param bool   $getObject
     *
     * @return array|GroupInterface|UserInterface|void
     *
     * @throws Exception
     */
    public function getPrincipalByPath($path, $getObject = false)
    {
        $name = str_replace('principals/', '', $path);

        // get username from path-string, if string contains additional slashes (e.g. admin/calendar-proxy-read)
        if (!(strpos($name, '/') === false)) {
            $name = substr($name, 0, strpos($name, '/'));
        }

        $user = $this->user_manager->findUserByUsername($name);

        if ($user === null) {
            if (!$this->group_manager) {
                return;
            }

            // search in group-manager
            $group = $this->group_manager->findGroupByName($name);

            if ($group === null) {
                return;
            }

            if ($getObject === true) {
                return $group;
            }

            return $this->getPrincipalArray($group, true);
        }

        if ($getObject === true) {
            return $user;
        }

        return $this->getPrincipalArray($user, true);
    }

    /**
     * Updates one ore more webdav properties on a principal.
     *
     * The list of mutations is stored in a Sabre\DAV\PropPatch object.
     * To do the actual updates, you must tell this object which properties
     * you're going to process with the handle() method.
     *
     * Calling the handle method is like telling the PropPatch object "I
     * promise I can handle updating this property".
     *
     * Read the PropPatch documenation for more info and examples.
     * 
     * @param string               $path
     * @param \Sabre\DAV\PropPatch $propPatch
     */
    public function updatePrincipal($path, \Sabre\DAV\PropPatch $propPatch)
    {
        $principal = $this->getPrincipalByPath($path, true);

        if (empty($principal)) {
            return;
        }

        $propPatch->handle(array_keys($this->fieldMap), function ($properties) use ($principal) {

            foreach ($properties as $key => $value) {
                $setter = $this->fieldMap[$key]['setter'];
                $principal->$setter($value);
            }

            $this->_em->flush();

            return true;
        });
    }

    /**
     * This method is used to search for principals matching a set of
     * properties.
     *
     * This search is specifically used by RFC3744's principal-property-search
     * REPORT.
     *
     * The actual search should be a unicode-non-case-sensitive search. The
     * keys in searchProperties are the WebDAV property names, while the values
     * are the property values to search on.
     *
     * By default, if multiple properties are submitted to this method, the
     * various properties should be combined with 'AND'. If $test is set to
     * 'anyof', it should be combined using 'OR'.
     *
     * This method should simply return an array with full principal uri's.
     *
     * If somebody attempted to search on a property the backend does not
     * support, you should simply return 0 results.
     *
     * You can also just return 0 results if you choose to not support
     * searching at all, but keep in mind that this may stop certain features
     * from working.
     *
     * @param string $prefixPath
     * @param array  $searchProperties
     * @param string $test
     *
     * @return array
     */
    public function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof')
    {
        foreach ($searchProperties as $property => $value) {
            switch ($property) {

                case '{DAV:}displayname' :
                    $searchArray['email'] = $value;
                    break;
                case '{http://sabredav.org/ns}email-address' :
                    $searchArray['email'] = $value;
                    break;
                default :
                    // Unsupported property
                    return array();
            }
        }

        $principals = $this->_em->getRepository($this->principals_class)->searchPrincipals($prefixPath, $searchArray, $test);

        return $principals;
    }

    /**
     * Returns the list of members for a group-principal.
     *
     * @param string $principal
     *
     * @return array
     *
     * @throws Exception
     */
    public function getGroupMemberSet($principal)
    {
        $groupMemberSet = array();

        $principalObject = $this->getPrincipalByPath($principal, true);

        if (!$principalObject) {
            throw new Exception('Principal not found');
        }

        // add current principal to group-list
        $principalArray = $this->getPrincipalArray($principalObject);
        $groupMemberSet[] = $principalArray['uri'];

        if ($this->principalgroups_class === '') {
            // groups-class is not defined: return current principal as only group-member
            return $groupMemberSet;
        }

        //TODO: list all group memberships for current group (FOSUserBundle)

        return $groupMemberSet;
    }

    /**
     * Returns the list of groups a principal is a member of (each element of the list contains a URI).
     *
     * @param string $principal
     *
     * @return array
     */
    public function getGroupMembership($principal)
    {
        $principal_data = $this->getPrincipalByPath($principal, true);

        if (!$principal_data) {
            return array();
        }

        $groupMembership = array($principal);

        if ($this->principalgroups_class !== '') {
            foreach ($principal_data->getGroups() as $group) {
                $groupPrincipal = $this->getPrincipalArray($group);
                $groupMembership[] = $groupPrincipal['uri'];
            }
        }

        return $groupMembership;
    }

    /**
     * Updates the list of group members for a group principal.
     *
     * The principals should be passed as a list of uri's.
     *
     * @param string $principal
     * @param array  $members
     *
     * @throws Exception
     */
    public function setGroupMemberSet($principal, array $members)
    {
        $groupPrincipal = $this->getPrincipalByPath($principal);

        if (!$groupPrincipal || !($groupPrincipal instanceof GroupInterface)) {
            throw new Exception('(Group-)Principal not found');
        }

        // check if update of user-groups is possible; break if no group-manager or principalgroups_class
        if ($this->principalgroups_class === '' || !$this->group_manager) {
            return;
        }

        $memberObjects = array($groupPrincipal);

        foreach ($members as $memberUri) {
            $memberObjects[] = $this->getPrincipalByPath($memberUri);
        }

        // TODO: Implement the addition/deletion of new/old members
    }

    /**
     * Creates a new principal.
     *
     * This method receives a full path for the new principal. The mkCol object
     * contains any additional webdav properties specified during the creation
     * of the principal.
     *
     * @param string $path
     * @param MkCol  $mkCol
     */
    public function createPrincipal($path, MkCol $mkCol)
    {

        // create new user
        $username = str_replace('principal/', '', $path);

        $user = $this->user_manager->createUser();
        $user->setUsername($username);
        $user->setForename($username);

        $this->_em->persist($user);
        $this->_em->flush();
    }
}
