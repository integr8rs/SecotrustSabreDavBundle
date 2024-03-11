<?php

namespace Secotrust\Bundle\SabreDavBundle\Entity\Repository;

/**
 * Class AddressbookRepositoryInterface.
 *
 * @author lduer
 */
interface AddressbookRepositoryInterface
{
    /**
     * get all Addressbooks for submitted principal.
     *
     * @param string $principalUri
     *
     * @return array
     */
    public function findAllPrincipalAddressbooks($principalUri);
}
