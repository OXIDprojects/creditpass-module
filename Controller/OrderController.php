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
 * @extend        Order
 */

namespace oe\oecreditpass\Controller;

class OrderController extends OrderController_parent
{

    /**
     * If user goes to order page, bonicheck is started.
     *
     * @extend init
     *
     * @return null
     */
    public function init()
    {
        $blModuleActive = oxRegistry::getConfig()->getConfigParam("blOECreditPassIsActive");

        if ($blModuleActive) {
            $fnc = oxRegistry::getConfig()->getRequestParameter('fnc');

            if ("execute" != $fnc) {
                $oCrAssessment = $this->_getCrAssessment();
                $oCrAssessment->clearDebugData();
                $bl2Order = $oCrAssessment->checkAll();

                if (!$bl2Order) {
                    $this->_redirect();
                }
            }
        }
        parent::init();
    }

    /**
     * Returns an instance of the module core class.
     *
     * @return oeCreditPassAssessment module core class
     */
    protected function _getCrAssessment()
    {
        return oxNew("oeCreditPassAssessment");
    }

    // @codeCoverageIgnoreStart

    /**
     * Redirects to the payment page.
     *
     * @return null (this method does not return, it calls exit() after a header redirect)
     */
    protected function _redirect()
    {
        // redirecting to payment step on error ..
        oxRegistry::getUtils()->redirect(oxRegistry::getConfig()->getShopCurrentURL() . '&cl=payment', true, 302);
    }
    // @codeCoverageIgnoreEnd
}