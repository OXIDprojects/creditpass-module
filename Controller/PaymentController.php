<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link          http://www.oxid-esales.com
 * @package       controllers
 * @copyright (c) anzido GmbH, Andreas Ziethen 2008-2011
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version       SVN: $Id: $
 *
 * @extend        Payment
 */

namespace oe\oecreditpass\Controller;

class PaymentController extends PaymentController_parent
{

    /**
     * Module core class object.
     *
     * @var oeCreditPassAssessment
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
     *
     */
    public function getPaymentList()
    {
        $blModuleActive = oxRegistry::getConfig()->getConfigParam("blOECreditPassIsActive");

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
     *
     * @return null
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
     * @return oeCreditPassAssessment
     */
    protected function _getCrAssessment()
    {
        return oxNew("oeCreditPassAssessment");
    }
}