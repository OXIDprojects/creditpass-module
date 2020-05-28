<?php

use OxidEsales\Eshop\Application\Controller\OrderController;
use OxidEsales\Eshop\Application\Controller\PaymentController;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Email;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\CreditPassController;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\ListController;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\LogController;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\LogListController;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\LogOverviewController;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\MainController;
use OxidProfessionalServices\CreditPassModule\Controller\Admin\UserController;
use OxidProfessionalServices\CreditPassModule\Core\Assessment;
use OxidProfessionalServices\CreditPassModule\Core\Config;
use OxidProfessionalServices\CreditPassModule\Core\Events;
use OxidProfessionalServices\CreditPassModule\Core\Exceptions\NotSupportedException;
use OxidProfessionalServices\CreditPassModule\Core\Interfaces\ICreditPassStorageShopAwarePersistence;
use OxidProfessionalServices\CreditPassModule\Core\Mail;
use OxidProfessionalServices\CreditPassModule\Core\ModelDbGateway;
use OxidProfessionalServices\CreditPassModule\Core\ResponseLogger;
use OxidProfessionalServices\CreditPassModule\Core\Storage;
use OxidProfessionalServices\CreditPassModule\Core\StorageDbShopAwarePersistence;
use OxidProfessionalServices\CreditPassModule\Model\DbGateways\PaymentSettingsDbGateway;
use OxidProfessionalServices\CreditPassModule\Model\DbGateways\ResponseCacheDbGateway;
use OxidProfessionalServices\CreditPassModule\Model\DbGateways\ResponseLoggerDbGateway;
use OxidProfessionalServices\CreditPassModule\Model\Log;
use OxidProfessionalServices\CreditPassModule\Model\ResultCache;

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
        'de' => 'Modul für Bonitätsprüfungen mit creditPass.',
        'en' => 'Module for solvency check with creditPass.',
    ],
    'thumbnail'   => 'picture.png',
    'version'     => '4.0.0',
    'author'      => 'OXID eSales AG',
    'url'         => 'http://www.oxid-esales.com',
    'email'       => 'info@oxid-esales.com',
    'extend'      => [
        OrderController::class   => \OxidProfessionalServices\CreditPassModule\Controller\OrderController::class,
        PaymentController::class => \OxidProfessionalServices\CreditPassModule\Controller\PaymentController::class,
        Order::class             => \OxidProfessionalServices\CreditPassModule\Model\Order::class,
        Email::class             => Mail::class
    ],
    'controllers' => [
        'oeICreditPassStorageShopAwarePersistence' => ICreditPassStorageShopAwarePersistence::class,

        'oeCreditPassAssessment'                    => Assessment::class,
        'oeCreditPassConfig'                        => Config::class,
        'oeCreditPassResponseLogger'                => ResponseLogger::class,
        'oeCreditPassEvents'                        => Events::class,
        'oeCreditPassModelDbGateway'                => ModelDbGateway::class,
        'oeCreditPassStorage'                       => Storage::class,
        'oeCreditPassStorageDbShopAwarePersistence' => StorageDbShopAwarePersistence::class,


        'oeCreditPass'              => CreditPassController::class,
        'oeCreditPass_List'         => ListController::class,
        'oeCreditPass_Main'         => MainController::class,
        'oeCreditPass_Payment'      => \OxidProfessionalServices\CreditPassModule\Controller\Admin\PaymentController::class,
        'oeCreditPass_Order'        => \OxidProfessionalServices\CreditPassModule\Controller\Admin\OrderController::class,
        'oeCreditPass_Log'          => LogController::class,
        'oeCreditPass_Log_List'     => LogListController::class,
        'oeCreditPass_Log_Overview' => LogOverviewController::class,
        'oeCreditPass_User'         => UserController::class,

        'oeCreditPassLog'                      => Log::class,
        'oeCreditPassResultCache'              => ResultCache::class,
        'oeCreditPassPaymentSettingsDbGateway' => PaymentSettingsDbGateway::class,
        'oeCreditPassResponseCacheDbGateway'   => ResponseCacheDbGateway::class,
        'oeCreditPassResponseLoggerDbGateway'  => ResponseLoggerDbGateway::class,

        'oeCreditPassException'             => \OxidProfessionalServices\CreditPassModule\Core\Exceptions\Exception::class,
        'oeCreditPassNotSupportedException' => NotSupportedException::class,
    ],
    'templates' => [
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
    'blocks'    => [
        ['template' => 'page/checkout/payment.tpl',
         'block'    => 'checkout_payment_nextstep',
         'file'     => '/views/blocks/oecreditpassdisablenext.tpl'],
        ['template' => 'page/checkout/payment.tpl',
         'block'    => 'checkout_payment_errors',
         'file'     => '/views/blocks/oecreditpassfallbackerror.tpl'],
    ],
    'settings'  => [

    ],
    'events'    => [
        'onActivate'   => '\OxidProfessionalServices\CreditPassModule\Core\Events::onActivate',
        'onDeactivate' => '\OxidProfessionalServices\CreditPassModule\Core\Events::onDeactivate'
    ],
];
