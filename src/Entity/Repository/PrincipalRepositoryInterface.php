<?php

namespace Secotrust\Bundle\SabreDavBundle\Entity\Repository;

/**
 * Class AddressbookRepositoryInterface.
 *
 * @author lduer
 */
interface PrincipalRepositoryInterface
{
    /**
     * Returns a list of principals based on a prefix.
     *
     * This prefix will often contain something like 'principals'. You are only
     * expected to return principals that are in this base path.
     *
     *
     * @param string $prefixPath
     */
    public function getPrincipalsByPrefix($prefixPath);

    /**
     * The actual search should be a unicode-non-case-sensitive search. The
     * keys in searchProperties are the WebDAV property names, while the values
     * are the property values to search on.
     *
     * By default, if multiple properties are submitted to this method, the
     * various properties should be combined with 'AND'. If $test is set to
     * 'anyof', it should be combined using 'OR'.
     *
     * @param string $prefixPath
     * @param array  $searchArray
     * @param string $test
     */
    public function searchPrincipals($prefixPath, array $searchArray, $test = 'allof');

    /**
     * Finds a principal by its URI.
     *
     * This method may receive any type of uri, but mailto: addresses will be
     * the most common.
     *
     * Implementation of this API is optional. It is currently used by the
     * CalDAV system to find principals based on their email addresses. If this
     * API is not implemented, some features may not work correctly.
     *
     * This method must return a relative principal path, or null, if the
     * principal was not found or you refuse to find it.
     *
     * @param string $uri
     *
     * @return string
     */
    public function findByUri($uri);
}
