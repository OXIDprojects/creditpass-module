<?php

/**
 * @extend        oxEmail
 */
namespace OxidProfessionalServices\CreditPassModule\Core;

use OxidEsales\Eshop\Core\Email;
use OxidEsales\Eshop\Core\Registry;

/**
 * CreditPass Mail class
 *
 * @extend        oxEmail
 */
class Email extends Email_parent
{

    /**
     * Admin mail template for manual workflow
     *
     * @var string
     */
    protected $_sAdminNoticeTemplate = "email/html/admin_notice.tpl";

    /**
     * Admin plain mail template for manual workflow
     *
     * @var string
     */
    protected $_sAdminNoticeTemplatePlain = "email/plain/admin_notice.tpl";

    /**
     * Sends email to admin
     *
     * @param      $oOrder
     * @param      $sManualReviewEmail
     * @param null $sSubject
     *
     * @return
     */
    public function sendCreditPassAdminEmail($oOrder, $sManualReviewEmail, $sSubject = null)
    {
        $myConfig = $this->getConfig();

        $oShop = $this->_getShop();

        // cleanup
        $this->_clearMailer();

        // add user defined stuff if there is any
        $oOrder = $this->_addUserInfoOrderEMail($oOrder);

        $oUser = $oOrder->getOrderUser();
        $this->setUser($oUser);

        // send confirmation to shop owner
        // send not pretending from order user, as different email domain rise spam filters
        $this->setFrom($sManualReviewEmail);

        $oLang = Registry::getLang();

        $iOrderLang = $oLang->getObjectTplLanguage();

        // if running shop language is different from admin lang. set in config
        // we have to load shop in config language
        if ($oShop->getLanguage() != $iOrderLang) {
            $oShop = $this->_getShop($iOrderLang);
        }

        $this->setSmtp($oShop);

        // create messages
        $oSmarty = $this->_getSmarty();
        $this->setViewData("order", $oOrder);

        // Process view data array through oxoutput processor
        $this->_processViewArray();

        $this->setBody($oSmarty->fetch($myConfig->getTemplatePath($this->_sAdminNoticeTemplate, false)));
        $this->setAltBody($oSmarty->fetch($myConfig->getTemplatePath($this->_sAdminNoticeTemplatePlain, false)));

        //Sets subject to email
        $sSubject = $oShop->oxshops__oxordersubject->getRawValue() . " (#" . $oOrder->oxorder__oxordernr->value . ")" . " " . $sTranslation = $oLang->translateString(
                'OECREDITPASS_SETTINGS_MANUAL_EMAIL_MESSAGE',
                $iOrderLang
            );

        $this->setSubject($sSubject);

        $this->setRecipient($sManualReviewEmail, $oLang->translateString("order"));

        $blSuccess = $this->send();

        return $blSuccess;
    }
}
