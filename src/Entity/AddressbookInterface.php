<?php

namespace Secotrust\Bundle\SabreDavBundle\Entity;

/**
 * Class AddressbookInterface.
 *
 * @author lduer
 */
interface AddressbookInterface
{
    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * Get label.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set label.
     *
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label);

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get the synctoken of the current CTag.
     *
     * @return string
     */
    public function getSynctoken();

    /**
     * updates the synctoken of the current Group.
     *
     * @return $this
     */
    public function updateSynctoken();

    /**
     * get the Uri.
     *
     * @return string
     */
    public function getUri();

    /**
     * Get all Cars for current Addressbook.
     *
     * @return array
     */
    public function getCards();

    /**
     * Search Card by URI in current Addressbook.
     *
     * @param string $uri
     */
    public function findCard($uri);

    /**
     * Adds the given card to the current Addressbook.
     *
     * @param \Secotrust\Bundle\SabreDavBundle\Entity\CardInterface $card
     */
    public function addCard(CardInterface $card);

    /**
     * Remove the given card from the current Addressbook.
     *
     * Caution: Check the field-configuration & your field-connections<br>
     * and use the desired setting, if you want to delete the cards <br>
     * - either only from the current Addressbook <br>
     * - or from the cards-table too
     *
     * @param \Secotrust\Bundle\SabreDavBundle\Entity\CardInterface $card
     *
     * @return bool
     */
    public function removeCard(CardInterface $card);

    /**
     * Remove all Cards from current Addressbook.
     *
     * Possible solution: use <code>$cards = $this->getCards()</code> and
     * <code>$this->removeCard($card)</code> to remove all cards
     */
    public function removeAllCards();
}
