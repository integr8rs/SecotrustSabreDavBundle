<?php

namespace Secotrust\Bundle\SabreDavBundle\Entity;

/**
 * Class CardInterface.
 *
 * Interface for single Contact-Entity in CardDAV
 *
 * use prePersist()-Action (Doctrine-Call) to call "updateCTag()" and "updateLastmodified()" actions automatically
 *
 * @author lduer
 */
interface CardInterface
{
    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * Get vCardUid.
     *
     * @return string
     */
    public function getVCardUid();

    /**
     * Set vCardUid.
     *
     * @param string $vCardUid
     *
     * @return Contact
     */
    public function setVCardUid($vCardUid);

    /**
     * get the vCard.
     *
     * @return string
     */
    public function getVCard();

    /**
     * updates the current vCard.
     *
     * @return $this
     */
    public function setVCard($vCard);

    /**
     * get lastmodified-date of the current card.
     *
     * @return \DateTime()
     */
    public function getLastmodified();

    /**
     * updates the lastmodified-date of the current Card.
     *
     * @return $this
     */
    public function updateLastmodified();

    /**
     * Get the value of the current CTag.
     *
     * @return string
     */
    public function getCTag();

    /**
     * updates the cTag of the current Card.
     *
     * @return $this
     */
    public function updateCTag();

    /**
     * get ETag of current Card.
     *
     * possible method: return md5-checksum of vCard-String
     *      return md5($this->getVCard());
     */
    public function getETag();
}
