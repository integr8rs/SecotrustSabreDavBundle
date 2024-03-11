<?php

namespace Secotrust\Bundle\SabreDavBundle\Entity\Repository;

/**
 * Class CardRepositoryInterface.
 *
 * @author lduer
 */
interface CardRepositoryInterface
{
    /**
     * Find one Card By vCard-UID and Addressbook-id.
     *
     * @param string $uid
     * @param string $addressBookId
     */
    public function findSingleCardByUid($uid = null, $addressBookId = null);
}
