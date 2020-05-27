<?php

namespace OxidProfessionalServices\CreditPassModule\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidProfessionalServices\CreditPassModule\Core\Interfaces\ICreditPassStorageShopAwarePersistence;

/**
 * CreditPass shop aware class which persists data into database.
 */
class StorageDbShopAwarePersistence implements ICreditPassStorageShopAwarePersistence
{

    const DATABASE_TABLE = 'oecreditpassstorage';

    /**
     * Database instance.
     *
     * @var DatabaseInterface
     */
    private $_oDb;

    /**
     * Constructor for oeCreditPassStorageDbShopAwarePersistence.
     *
     * @param DatabaseInterface $oDb An instance of database class.
     */
    public function __construct($oDb)
    {
        $this->_oDb = $oDb;
    }

    /**
     * Creates instance of oeCreditPassStorageDbShopAwarePersistence.
     *
     * @return StorageDbShopAwarePersistence
     * @throws DatabaseConnectionException
     */
    public static function createInstance()
    {
        $oInstance = new StorageDbShopAwarePersistence(
            DatabaseProvider::getDb()
        );

        return $oInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($iShopId, $sKey, $mValue)
    {
        $sTable = self::DATABASE_TABLE;

        $sEncodedValue = $this->_encodeValue($mValue);

        $sSql = "replace into `{$sTable}` (`SHOPID`, `KEY`, `VALUE`) VALUES (?, ?, ?)";
        $aSqlParameters = array($iShopId, $sKey, $sEncodedValue);

        $this->_getDb()->execute($sSql, $aSqlParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($iShopId, $sKey)
    {
        $sTable = self::DATABASE_TABLE;

        $sSql = "select `VALUE` from `{$sTable}` where `SHOPID` = ? and `KEY` = ?";
        $aSqlParameters = array($iShopId, $sKey);

        $sEncodedValue = $this->_getDb()->getOne($sSql, $aSqlParameters);

        $mValue = $this->_decodeValue($sEncodedValue);

        return $mValue;
    }

    /**
     * Gets database instance.
     *
     * @return DatabaseInterface
     */
    private function _getDb()
    {
        return $this->_oDb;
    }

    /**
     * Encodes values.
     *
     * @param mixed $mValue Value to be encoded.
     *
     * @return string
     */
    private function _encodeValue($mValue)
    {
        $sValue = serialize($mValue);

        return $sValue;
    }

    /**
     * Decodes values.
     *
     * @param string $sValue Encoded value.
     *
     * @return mixed
     */
    private function _decodeValue($sValue)
    {
        $mValue = unserialize($sValue);

        return $mValue;
    }
}
