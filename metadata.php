<?php

use OxidEsales\Eshop\Application\Controller\OrderController;
use OxidEsales\Eshop\Application\Controller\PaymentController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Email;
use OxidEsales\Eshop\Core\ViewConfig;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\CreditPassController;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\CreditPassListController;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\CreditPassOrderController;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\CreditPassPaymentController;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\CreditPassLogController;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\CreditPassLogListController;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\CreditPassLogOverviewController;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\CreditPassMainController;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\CreditPassUserController;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassAssessment;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassConfig;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassEvents;
use OxidProfessionalServices\CreditPassModule\Core\Exceptions\CreditPassException;
use OxidProfessionalServices\CreditPassModule\Core\Exceptions\CreditPassNotSupportedException;
use OxidProfessionalServices\CreditPassModule\Core\Interfaces\ICreditPassStorageShopAwarePersistence;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassModelDbGateway;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassResponseLogger;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassStorage;
use OxidProfessionalServices\CreditPassModule\Core\CreditPassStorageDbShopAwarePersistence;
use OxidProfessionalServices\CreditPassModule\Model\DbGateways\CreditPassPaymentSettingsDbGateway;
use OxidProfessionalServices\CreditPassModule\Model\DbGateways\CreditPassResponseCacheDbGateway;
use OxidProfessionalServices\CreditPassModule\Model\DbGateways\CreditPassResponseLoggerDbGateway;
use OxidProfessionalServices\CreditPassModule\Model\CreditPassLog;
use OxidProfessionalServices\CreditPassModule\Model\CreditPassResultCache;

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = [
    'id'          => 'oxps/creditpass',
    'title'       => 'creditPass',
    'description' => [
        'de' => 'Modul für Bonitätsprüfungen mit creditPass.',
        'en' => 'Module for solvency check with creditPass.',
    ],
    'thumbnail'   => 'picture.png',
    'version'     => '4.0.0',
    'author'      => 'OXID eSales AG',
    'url'         => 'http://www.oxid-esales.com',
    'email'       => 'info@oxid-esales.com',
    'extend'      => [
        ViewConfig::class        => \OxidProfessionalServices\CreditPassModule\Core\ViewConfig::class,
        OrderController::class   => \OxidProfessionalServices\CreditPassModule\Controller\OrderController::class,
        PaymentController::class => \OxidProfessionalServices\CreditPassModule\Controller\PaymentController::class,
        Order::class             => \OxidProfessionalServices\CreditPassModule\Model\Order::class,
        Email::class             => \OxidProfessionalServices\CreditPassModule\Core\Email::class,
    ],
    'controllers' => [
        'ICreditPassStorageShopAwarePersistence' => ICreditPassStorageShopAwarePersistence::class,

        'CreditPassAssessment'                    => CreditPassAssessment::class,
        'CreditPassConfig'                        => CreditPassConfig::class,
        'CreditPassResponseLogger'                => CreditPassResponseLogger::class,
        'CreditPassEvents'                        => CreditPassEvents::class,
        'CreditPassModelDbGateway'                => CreditPassModelDbGateway::class,
        'CreditPassStorage'                       => CreditPassStorage::class,
        'CreditPassStorageDbShopAwarePersistence' => CreditPassStorageDbShopAwarePersistence::class,

        'CreditPassController'            => CreditPassController::class,
        'CreditPassListController'        => CreditPassListController::class,
        'CreditPassMainController'        => CreditPassMainController::class,
        'CreditPassPaymentController'     => CreditPassPaymentController::class,
        'CreditPassOrderController'       => CreditPassOrderController::class,
        'CreditPassLogController'         => CreditPassLogController::class,
        'CreditPassLogListController'     => CreditPassLogListController::class,
        'CreditPassLogOverviewController' => CreditPassLogOverviewController::class,
        'CreditPassUserController'        => CreditPassUserController::class,

        'CreditPassLog'                      => CreditPassLog::class,
        'CreditPassResultCache'              => CreditPassResultCache::class,
        'CreditPassPaymentSettingsDbGateway' => CreditPassPaymentSettingsDbGateway::class,
        'CreditPassResponseCacheDbGateway'   => CreditPassResponseCacheDbGateway::class,
        'CreditPassResponseLoggerDbGateway'  => CreditPassResponseLoggerDbGateway::class,

        'CreditPassException'             => CreditPassException::class,
        'CreditPassNotSupportedException' => CreditPassNotSupportedException::class,
    ],
    'templates'   => [
        'oecreditpass.tpl'              => 'oxps/creditpass/views/admin/tpl/oecreditpass.tpl',
        'oecreditpass_list.tpl'         => 'oxps/creditpass/views/admin/tpl/oecreditpass_list.tpl',
        'oecreditpass_main.tpl'         => 'oxps/creditpass/views/admin/tpl/oecreditpass_main.tpl',
        'oecreditpass_log.tpl'          => 'oxps/creditpass/views/admin/tpl/oecreditpass_log.tpl',
        'oecreditpass_log_list.tpl'     => 'oxps/creditpass/views/admin/tpl/oecreditpass_log_list.tpl',
        'oecreditpass_log_overview.tpl' => 'oxps/creditpass/views/admin/tpl/oecreditpass_log_overview.tpl',
        'oecreditpass_log_details.tpl'  => 'oxps/creditpass/views/admin/tpl/oecreditpass_log_details.tpl',
        'oecreditpass_payment.tpl'      => 'oxps/creditpass/views/admin/tpl/oecreditpass_payment.tpl',
        'oecreditpass_order.tpl'        => 'oxps/creditpass/views/admin/tpl/oecreditpass_order.tpl',
        'oecreditpass_user.tpl'         => 'oxps/creditpass/views/admin/tpl/oecreditpass_user.tpl',
        'email/html/admin_notice.tpl'   => 'oxps/creditpass/views/tpl/email/html/admin_notice.tpl',
        'email/plain/admin_notice.tpl'  => 'oxps/creditpass/views/tpl/email/plain/admin_notice.tpl',
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
        'onActivate'   => '\OxidProfessionalServices\CreditPassModule\Core\CreditPassEvents::onActivate',
        'onDeactivate' => '\OxidProfessionalServices\CreditPassModule\Core\CreditPassEvents::onDeactivate'
    ],
];
