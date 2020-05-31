<?php

/**
 * @extend    Order
 */

namespace OxidProfessionalServices\CreditPassModule\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassAssessment;

/**
 * Order controller class
 *
 * @extend    Oxid OrderController
 * @phpcs     :disable PSR2.Methods.MethodDeclaration.Underscore
 */
class OrderController extends \OxidEsales\Eshop\Application\Controller\OrderController
{

    /**
     * If user goes to order page, bonicheck is started.
     *
     * @extend init
     */
    public function init()
    {
        $blModuleActive = Registry::getConfig()->getConfigParam("blOECreditPassIsActive");

        if ($blModuleActive) {
            $fnc = Registry::getConfig()->getRequestParameter('fnc');

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
     * @return CreditPassAssessment module core class
     */
    protected function _getCrAssessment()
    {
        return oxNew(CreditPassAssessment::class);
    }

    // @codeCoverageIgnoreStart

    /**
     * Redirects to the payment page.
     *
     * (this method does not return, it calls exit() after a header redirect)
     */
    protected function _redirect()
    {
        // redirecting to payment step on error ..
        Registry::getUtils()->redirect(Registry::getConfig()->getShopCurrentURL() . '&cl=payment', true, 302);
    }
    // @codeCoverageIgnoreEnd
}
