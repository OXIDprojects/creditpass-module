<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link      http://www.oxid-esales.com
 * @package   main
 * @copyright (c] OXID eSales AG 2003-#OXID_VERSION_YEAR#
 * @version   SVN: $Id: $
 */

use OxidEsales\Eshop\Application\Controller\OrderController;
use OxidEsales\Eshop\Application\Controller\PaymentController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Email;

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id'          => 'oecreditpass',
    'title'       => 'creditPass',
    'description' => [
        'de' => 'Modul f&uuml;r Bonit&auml;tspr&uuml;fungen mit creditPass.',
        'en' => 'Module for solvency check with creditPass.',
    ],
    'thumbnail'   => 'picture.png',
    'version'     => '4.0.0',
    'author'      => 'OXID eSales AG',
    'url'         => 'http://www.oxid-esales.com',
    'email'       => 'info@oxid-esales.com',
    'extend'      => [
        OrderController::class   => oe\oecreditpass\Controller\OrderController::class,
        PaymentController::class => oe\oecreditpass\Controller\PaymentController::class,
        Order::class             => oe\oecreditpass\Model\Order::class,
        Email::class             => oe\oecreditpass\Core\Mail::class
    ],
    'controllers' => [
        'oeICreditPassStorageShopAwarePersistence' => oe\oecreditpass\Core\Interfaces\ICreditPassStorageShopAwarePersistence::class,

        'oeCreditPassAssessment'                    => oe\oecreditpass\Core\Assessment::class,
        'oeCreditPassConfig'                        => oe\oecreditpass\Core\Config::class,
        'oeCreditPassResponseLogger'                => oe\oecreditpass\Core\ResponseLogger::class,
        'oeCreditPassEvents'                        => oe\oecreditpass\Core\Events::class,
        'oeCreditPassModelDbGateway'                => oe\oecreditpass\Core\ModelDbGateway::class,
        'oeCreditPassStorage'                       => oe\oecreditpass\Core\Storage::class,
        'oeCreditPassStorageDbShopAwarePersistence' => oe\oecreditpass\Core\StorageDbShopAwarePersistence::class,


        'oeCreditPass'              => oe\oecreditpass\Controller\Admin\CreditPassController::class,
        'oeCreditPass_List'         => oe\oecreditpass\Controller\Admin\ListController::class,
        'oeCreditPass_Main'         => oe\oecreditpass\Controller\Admin\MainController::class,
        'oeCreditPass_Payment'      => oe\oecreditpass\Controller\Admin\PaymentController::class,
        'oeCreditPass_Order'        => oe\oecreditpass\Controller\Admin\OrderController::class,
        'oeCreditPass_Log'          => oe\oecreditpass\Controller\Admin\LogController::class,
        'oeCreditPass_Log_List'     => oe\oecreditpass\Controller\Admin\LogListController::class,
        'oeCreditPass_Log_Overview' => oe\oecreditpass\Controller\Admin\LogOverviewController::class,
        'oeCreditPass_User'         => oe\oecreditpass\Controller\Admin\UserController::class,

        'oeCreditPassLog'                      => oe\oecreditpass\Model\Log::class,
        'oeCreditPassResultCache'              => oe\oecreditpass\Model\ResultCache::class,
        'oeCreditPassPaymentSettingsDbGateway' => oe\oecreditpass\Model\DbGateways\PaymentSettingsDbGateway::class,
        'oeCreditPassResponseCacheDbGateway'   => oe\oecreditpass\Model\DbGateways\ResponseCacheDbGateway::class,
        'oeCreditPassResponseLoggerDbGateway'  => oe\oecreditpass\Model\DbGateways\ResponseLoggerDbGateway::class,

        'oeCreditPassException'             => oe\oecreditpass\Core\Exceptions\Exception::class,
        'oeCreditPassNotSupportedException' => oe\oecreditpass\Core\Exceptions\NotSupportedException::class,
    ],
    'templates'   => [
        'oecreditpass.tpl'              => 'oe/oecreditpass/views/admin/tpl/oecreditpass.tpl',
        'oecreditpass_list.tpl'         => 'oe/oecreditpass/views/admin/tpl/oecreditpass_list.tpl',
        'oecreditpass_main.tpl'         => 'oe/oecreditpass/views/admin/tpl/oecreditpass_main.tpl',
        'oecreditpass_log.tpl'          => 'oe/oecreditpass/views/admin/tpl/oecreditpass_log.tpl',
        'oecreditpass_log_list.tpl'     => 'oe/oecreditpass/views/admin/tpl/oecreditpass_log_list.tpl',
        'oecreditpass_log_overview.tpl' => 'oe/oecreditpass/views/admin/tpl/oecreditpass_log_overview.tpl',
        'oecreditpass_log_details.tpl'  => 'oe/oecreditpass/views/admin/tpl/oecreditpass_log_details.tpl',
        'oecreditpass_payment.tpl'      => 'oe/oecreditpass/views/admin/tpl/oecreditpass_payment.tpl',
        'oecreditpass_order.tpl'        => 'oe/oecreditpass/views/admin/tpl/oecreditpass_order.tpl',
        'oecreditpass_user.tpl'         => 'oe/oecreditpass/views/admin/tpl/oecreditpass_user.tpl',
        'email/html/admin_notice.tpl'   => 'oe/oecreditpass/views/tpl/email/html/admin_notice.tpl',
        'email/plain/admin_notice.tpl'  => 'oe/oecreditpass/views/tpl/email/plain/admin_notice.tpl',
    ],
    'blocks'      => [
        ['template' => 'page/checkout/payment.tpl',
         'block'    => 'checkout_payment_nextstep',
         'file'     => '/views/blocks/oecreditpassdisablenext.tpl'],
        ['template' => 'page/checkout/payment.tpl',
         'block'    => 'checkout_payment_errors',
         'file'     => '/views/blocks/oecreditpassfallbackerror.tpl'],
    ],
    'events'      => [
        'onActivate'   => 'oeCreditPassEvents::onActivate',
        'onDeactivate' => 'oeCreditPassEvents::onDeactivate'
    ],

];
