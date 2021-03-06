<?php

/**
 * @extend    Payment
 */

namespace OxidProfessionalServices\CreditPassModule\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassAssessment;

/**
 * Payment controller class
 *
 * @extend    Oxid PaymentController
 * @phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
 */
class PaymentController extends \OxidEsales\Eshop\Application\Controller\PaymentController
{

    /**
     * Module core class object.
     *
     * @var CreditPassAssessment
     */
    protected $_oCrAssessment = null;

    /**
     * Result of the internal logic check.
     *
     * @var int
     */
    protected $_azIntLogicResponse = null;

    /**
     * Excludes the payments that require a higher credit rating (boni value).
     *
     * @extend getPaymentList
     *
     * @return object
     * @see    _processPaymentList()
     */
    public function getPaymentList()
    {
        $blModuleActive = Registry::getConfig()->getConfigParam("blOECreditPassIsActive");

        if ($this->_oPaymentList === null) {
            $this->_oPaymentList = parent::getPaymentList();
            if ($blModuleActive) {
                $this->_processPaymentList();
            }
        }

        return $this->_oPaymentList;
    }

    /**
     * Filters the list of payment methods and removes payment methods depending on the credit rating check.
     */
    protected function _processPaymentList()
    {
        $this->_oCrAssessment = $this->_getCrAssessment();

        if (count($this->_oPaymentList)) {
            $this->_oPaymentList = $this->_oCrAssessment->filterPaymentMethods($this->_oPaymentList);
        }
    }

    /**
     * Returns an instance of the module core class.
     *
     * @return CreditPassAssessment
     */
    protected function _getCrAssessment()
    {
        return oxNew(CreditPassAssessment::class);
    }
}
