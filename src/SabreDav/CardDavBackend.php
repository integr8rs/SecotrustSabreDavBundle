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

use Sabre\CardDAV\Backend\AbstractBackend;
use Sabre\CardDAV\Backend\SyncSupport;
use Sabre\CardDAV;
use Sabre\DAV\Exception\BadRequest;
use Secotrust\Bundle\SabreDavBundle\Entity\CardInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CardDavBackend extends AbstractBackend implements SyncSupport
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $_em;

    /**
     * @var string
     */
    private $addressbooks_class;

    /**
     * @var string
     */
    private $cards_class;

    /**
     * Create array with Card-Data.
     *
     * @param CardInterface $entity
     * @param bool          $show_id
     *
     * @return array|bool
     */
    private function getCardArray($entity, $show_id = false)
    {
        if (!($entity instanceof CardInterface)) {
            return false;
        }

        $card = array(
            'id' => $entity->getId(),
            'carddata' => $entity->getVCard(),
            'uri' => $entity->getVCardUid().'.vcf',
            'lastmodified' => $entity->getLastmodified(),
            'size' => strlen($entity->getVCard()),
            'etag' => $entity->getETag(),
        );

        if ($show_id === false) {
            unset($card['id']);
        }

        return $card;
    }

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->_em = $container->get('doctrine')->getManager();

        $this->addressbooks_class = $container->getParameter('secotrust.addressbooks_class');
        $this->cards_class = $container->getParameter('secotrust.cards_class');
    }

    /**
     * Returns the list of addressbooks for a specific user.
     *
     * Every addressbook should have the following properties:
     *   id - an arbitrary unique id
     *   uri - the 'basename' part of the url
     *   principaluri - Same as the passed parameter
     *
     * Any additional clark-notation property may be passed besides this. Some
     * common ones are :
     *   {DAV:}displayname
     *   {urn:ietf:params:xml:ns:carddav}addressbook-description
     *   {http://calendarserver.org/ns/}getctag
     *
     * @param string $principalUri
     *
     * @return array
     */
    public function getAddressBooksForUser($principalUri)
    {
        $addressBooks = array();

        $entities = $this->_em->getRepository($this->addressbooks_class)->findAllPrincipalAddressbooks($principalUri);

        foreach ($entities as $entity) {
            $addressBooks[] = array(
                'id' => $entity->getId(),
                'uri' => $entity->getUriLabel(),
                'principaluri' => $principalUri,
                '{DAV:}displayname' => $entity->getLabel(),
                '{'.CardDAV\Plugin::NS_CARDDAV.'}addressbook-description' => $entity->getDescription(),
                '{http://calendarserver.org/ns/}getctag' => $entity->getSyncToken(),
                '{http://sabredav.org/ns}sync-token' => $entity->getSyncToken(),
            );
        }

        return $addressBooks;
    }

    /**
     * Updates properties for an address book.
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
     * @param string               $addressBookId
     * @param \Sabre\DAV\PropPatch $propPatch
     */
    public function updateAddressBook($addressBookId, \Sabre\DAV\PropPatch $propPatch)
    {
        $supportedProperties = [
            '{DAV:}displayname',
            '{'.CardDAV\Plugin::NS_CARDDAV.'}addressbook-description',
        ];

        $addressbook = $this->_em->getRepository($this->addressbooks_class)->find($addressBookId);

        if (!$addressbook) {
            return;
        }

        $propPatch->handle($supportedProperties, function ($mutations) use ($addressbook) {

            $updates = [];
            foreach ($mutations as $property => $newValue) {
                switch ($property) {
                    case '{DAV:}displayname' :
                        $updates['setLabel'] = $newValue;
                        break;
                    case '{'.CardDAV\Plugin::NS_CARDDAV.'}addressbook-description' :
                        $updates['setDescription'] = $newValue;
                        break;
                }
            }

            foreach ($updates as $setter => $value) {
                if (method_exists($addressbook, $setter)) {
                    $addressbook->$setter($value);
                }
            }

            $this->_em->persist($addressbook);
            $this->_em->flush();

            return true;
        });
    }

    /**
     * Creates a new address book.
     *
     * @param string $principalUri
     * @param string $url          Just the 'basename' of the url.
     * @param array  $properties
     *
     * @throws BadRequest
     */
    public function createAddressBook($principalUri, $url, array $properties)
    {
        $values = array(
            'setLabel' => null,
            'setDescription' => null,
            'principaluri' => $principalUri,
            'uri' => $url,
        );

        foreach ($properties as $property => $newValue) {
            switch ($property) {
                case '{DAV:}displayname' :
                    $values['setLabel'] = $newValue;
                    break;
                case '{'.CardDAV\Plugin::NS_CARDDAV.'}addressbook-description' :
                    $values['setDescription'] = $newValue;
                    break;
                default :
                    throw new BadRequest('Unknown property: '.$property);
            }
        }

        // check if current addressbooks-class can be instantiated
        if ((new \ReflectionClass($this->addressbooks_class))->isAbstract()) {
            return;
        }

        $addressbook = new $this->addressbooks_class();

        foreach ($values as $setter => $value) {
            if (method_exists($addressbook, $setter)) {
                $addressbook->$setter($value);
            }
        }

        $this->_em->persist($addressbook);
        $this->_em->flush();

        return $addressbook->getId();
    }

    /**
     * Deletes an entire addressbook and all its contents.
     *
     * @param mixed $addressBookId
     */
    public function deleteAddressBook($addressBookId)
    {
        $addressbook = $this->_em->getRepository($this->addressbooks_class)->find($addressBookId);

        if (!$addressbook) {
            return;
        }

        $addressbook->removeAllCards();
        $this->_em->delete($addressbook);
    }

    /**
     * Returns all cards for a specific addressbook id.
     *
     * This method should return the following properties for each card:
     *   * carddata - raw vcard data
     *   * uri - Some unique url
     *   * lastmodified - A unix timestamp
     *
     * It's recommended to also return the following properties:
     *   * etag - A unique etag. This must change every time the card changes.
     *   * size - The size of the card in bytes.
     *
     * If these last two properties are provided, less time will be spent
     * calculating them. If they are specified, you can also ommit carddata.
     * This may speed up certain requests, especially with large cards.
     *
     * @param mixed $addressbookId
     *
     * @return array
     */
    public function getCards($addressbookId)
    {
        $contactGroup = $this->_em->getRepository($this->addressbooks_class)->findOneById($addressbookId);
        $entities = $contactGroup->getContactCollection();

        $cards = array();
        foreach ($entities as $entity) {
            $cards[] = $this->getCardArray($entity, true);
        }

        return $cards;
    }

    /**
     * Returns a specfic card.
     *
     * The same set of properties must be returned as with getCards. The only
     * exception is that 'carddata' is absolutely required.
     *
     * @param mixed  $addressBookId
     * @param string $cardUri
     *
     * @return array
     */
    public function getCard($addressBookId, $cardUri)
    {
        $entity = $this->_em->getRepository($this->cards_class)->findSingleCardByUid($cardUri, $addressBookId);

        return $this->getCardArray($entity, true);
    }

    /**
     * Creates a new card.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressbooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag is for the
     * newly created resource, and must be enclosed with double quotes (that
     * is, the string itself must contain the double quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed  $addressBookId
     * @param string $cardUri
     * @param string $cardData
     *
     * @return string|null
     */
    public function createCard($addressBookId, $cardUri, $cardData)
    {
        $addressbook = $this->_em->getRepository($this->addressbooks_class)->find($addressBookId);

        if ((new \ReflectionClass($this->cards_class))->isAbstract()) {
            return;
        }

        $card = new $this->cards_class();
        $card->setVCard($cardData);
        $card->setVCardUid($cardUri);
        $addressbook->addCard($card);

        $this->_em->persist($card);
        $this->_em->flush();

        return $card->getETag();
    }

    /**
     * Updates a card.
     *
     * The addressbook id will be passed as the first argument. This is the
     * same id as it is returned from the getAddressbooksForUser method.
     *
     * The cardUri is a base uri, and doesn't include the full path. The
     * cardData argument is the vcard body, and is passed as a string.
     *
     * It is possible to return an ETag from this method. This ETag should
     * match that of the updated resource, and must be enclosed with double
     * quotes (that is: the string itself must contain the actual quotes).
     *
     * You should only return the ETag if you store the carddata as-is. If a
     * subsequent GET request on the same card does not have the same body,
     * byte-by-byte and you did return an ETag here, clients tend to get
     * confused.
     *
     * If you don't return an ETag, you can just return null.
     *
     * @param mixed  $addressBookId
     * @param string $cardUri
     * @param string $cardData
     *
     * @return string|null
     */
    public function updateCard($addressBookId, $cardUri, $cardData)
    {
        $card = $this->_em->getRepository($this->cards_class)->findSingleCardByUid($cardUri, $addressBookId);

        if (!$card) {
            return;
        }

        $card->setVCard($cardData);

        $this->_em->flush();

        return $card->getEtag();
    }

    /**
     * Deletes a card.
     *
     * @param mixed  $addressBookId
     * @param string $cardUri
     *
     * @return bool
     */
    public function deleteCard($addressBookId, $cardUri)
    {
        $addressbook = $this->_em->getRepository($this->addressbooks_class)->find($addressBookId);

        $card = $addressbook->findCard($cardUri);

        if ($card instanceof CardInterface) {
            return $addressbook->removeCard($card);
        }

        return false;
    }

    /**
     * The getChanges method returns all the changes that have happened, since
     * the specified syncToken in the specified address book.
     *
     * This function should return an array, such as the following:
     *
     * [
     *   'syncToken' => 'The current synctoken',
     *   'added'   => [
     *      'new.txt',
     *   ],
     *   'modified'   => [
     *      'modified.txt',
     *   ],
     *   'deleted' => [
     *      'foo.php.bak',
     *      'old.txt'
     *   ]
     * ];
     *
     * The returned syncToken property should reflect the *current* syncToken
     * of the calendar, as reported in the {http://sabredav.org/ns}sync-token
     * property. This is needed here too, to ensure the operation is atomic.
     *
     * If the $syncToken argument is specified as null, this is an initial
     * sync, and all members should be reported.
     *
     * The modified property is an array of nodenames that have changed since
     * the last token.
     *
     * The deleted property is an array with nodenames, that have been deleted
     * from collection.
     *
     * The $syncLevel argument is basically the 'depth' of the report. If it's
     * 1, you only have to report changes that happened only directly in
     * immediate descendants. If it's 2, it should also include changes from
     * the nodes below the child collections. (grandchildren)
     *
     * The $limit argument allows a client to specify how many results should
     * be returned at most. If the limit is not specified, it should be treated
     * as infinite.
     *
     * If the limit (infinite or not) is higher than you're willing to return,
     * you should throw a Sabre\DAV\Exception\TooMuchMatches() exception.
     *
     * If the syncToken is expired (due to data cleanup) or unknown, you must
     * return null.
     *
     * The limit is 'suggestive'. You are free to ignore it.
     *
     * @param string $addressBookId
     * @param string $syncToken
     * @param int    $syncLevel
     * @param int    $limit
     *
     * @return array
     */
    public function getChangesForAddressBook($addressBookId, $syncToken, $syncLevel, $limit = null)
    {

        // the "Doctrine2 behavioral extensions" (https://github.com/Atlantic18/DoctrineExtensions)
        // are used to log the addressbook-changes
        $loggableClass = 'Gedmo\Loggable\Entity\LogEntry';

        if (!class_exists($loggableClass)) {
            return;
        }

        /* @var $addressbook \Secotrust\Bundle\SabreDavBundle\Entity\AddressbookInterface */
        $addressbook = $this->_em->getRepository($this->addressbooks_class)->find($addressBookId);

        if ($addressbook->getSynctoken() === 0) {
            return;
        }

        $result = [
            'syncToken' => $addressbook->getSynctoken(),
            'added' => [],
            'modified' => [],
            'deleted' => [],
        ];

        if ($syncToken) {

            // Fetching all changes
            $repo = $this->_em->getRepository($loggableClass);
            $logs = $repo->getLogEntries($addressbook);

            $changes = [];

            // This loop ensures that any duplicates are overwritten, only the
            // last change on a node is relevant.
            foreach ($logs as $log) {
                $changes[$addressbook->getUri()] = $log->getAction();
            }

            foreach ($changes as $uri => $operation) {
                switch ($operation) {
                    case 'create':
                        $result['added'][] = $uri;
                        break;
                    case 'update':
                        $result['modified'][] = $uri;
                        break;
                    case 'remove':
                        $result['deleted'][] = $uri;
                        break;
                }
            }
        } else {
            // No synctoken supplied, this is the initial sync.
            $result['added'] = $addressbook->getUri();
        }

        return $result;
    }

    /**
     * Adds a change record to the addressbookchanges table.
     *
     * @param mixed  $addressBookId
     * @param string $objectUri
     * @param int    $operation     1 = add, 2 = modify, 3 = delete
     */
    protected function addChange($addressBookId, $objectUri, $operation)
    {

        // it is suggested to use the Loggable-Extension for Doctrine to manage
        // the changes in the entities
        // https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/loggable.md
        //
        // if the extension is configured in the right way, the changes are logged automatically
        // configuration-example: https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/loggable.md#entity-mapping

        return;
    }
}
