<?php

namespace OxidProfessionalServices\CreditPassModule\Core;

use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Config;
use OxidProfessionalServices\CreditPassModule\Core\Interfaces\ICreditPassStorageShopAwarePersistence;

/**
 * Class responsible for storing data (i.e. config backup).
 *
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class CreditPassStorage
{

    /**
     * An instance of oxConfig.
     *
     * @var CreditPassConfig
     */
    private $_oConfig;

    /**
     * An instance of ICreditPassStorageShopAwarePersistence.
     *
     * @var ICreditPassStorageShopAwarePersistence
     */
    private $_oShopAwarePersistence;

    /**
     * Constructor.
     *
     * @param Config                                 $oConfig               An instance of oxConfig.
     * @param ICreditPassStorageShopAwarePersistence $oShopAwarePersistence An instance of ICreditPassStorageShopAwarePersistence.
     */
    public function __construct(Config $oConfig, ICreditPassStorageShopAwarePersistence $oShopAwarePersistence)
    {
        $this->_oConfig = $oConfig;
        $this->_oShopAwarePersistence = $oShopAwarePersistence;
    }

    /**
     * Creates instance of CreditPassStorage.
     *
     * @return CreditPassStorage
     * @throws DatabaseConnectionException
     */
    public static function createInstance()
    {
        $oInstance = new CreditPassStorage(
            Registry::getConfig(),
            CreditPassStorageDbShopAwarePersistence::createInstance()
        );

        return $oInstance;
    }

    /**
     * Set value to storage.
     *
     * @param string $sKey   Data key of stored value.
     * @param mixed  $mValue Value to store.
     */
    public function setValue($sKey, $mValue)
    {
        $iShopId = $this->_getShopId();

        $this->_getShopAwarePersistence()->setValue($iShopId, $sKey, $mValue);
    }

    /**
     * Get value from storage.
     *
     * @param string $sKey Stored data key.
     *
     * @return mixed
     */
    public function getValue($sKey)
    {
        $iShopId = $this->_getShopId();

        $mValue = $this->_getShopAwarePersistence()->getValue($iShopId, $sKey);

        return $mValue;
    }

    /**
     * Gets instance of oxConfig.
     *
     * @return CreditPassConfig
     */
    private function _getConfig()
    {
        return $this->_oConfig;
    }

    /**
     * Gets shop id.
     *
     * @return int
     */
    private function _getShopId()
    {
        $iShopId = $this->_getConfig()->getShopId();
        if ('oxbaseshop' === $iShopId) {
            $iShopId = 1;
        }

        return $iShopId;
    }

    /**
     * Gets instance of oeICreditPassStorageShopAwarePersistence.
     *
     * @return ICreditPassStorageShopAwarePersistence
     */
    private function _getShopAwarePersistence()
    {
        return $this->_oShopAwarePersistence;
    }
}
