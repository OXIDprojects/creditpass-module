<?php

namespace OxidProfessionalServices\CreditPassModule\Core\Interfaces;

/**
 * CreditPass shop aware persistence interface.
 */
interface ICreditPassStorageShopAwarePersistence
{

    /**
     * Sets value.
     *
     * @param integer $iShopId Shop ID.
     * @param string  $sKey    Key of value.
     * @param mixed   $mValue  Value to store.
     */
    public function setValue($iShopId, $sKey, $mValue);

    /**
     * Gets value.
     *
     * @param integer $iShopId Shop ID.
     * @param string  $sKey    key of value.
     *
     * @return mixed
     */
    public function getValue($iShopId, $sKey);
}
