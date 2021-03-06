<?php

$sLangName = 'Deutsch';
$iLangNr = 0;
$aLang = [
    'charset'                                     => 'UTF-8',
    'OECREDITPASS_TITLE'                          => 'oecreditpass',
    'OECREDITPASS_SUBMENU'                        => 'creditPass',
    'OECREDITPASS_TAB_SETTINGS'                   => 'Einstellungen',
    'OECREDITPASS_TAB_PAYMENT'                    => 'Zahlungsarten',
    'OECREDITPASS_TAB_ORDER'                      => 'creditPass',
    'OECREDITPASS_TAB_USER'                       => 'creditPass',
    'OECREDITPASS_SETTINGS_VERSION'               => 'Version der Core-Datei',
    'OECREDITPASS_SETTINGS_ACTIVATE'              => 'Modul an/aus',
    'OECREDITPASS_SETTINGS_IS_ACTIVE'             => 'creditPass-Prüfungen ausführen',
    'OECREDITPASS_SETTINGS_LOGIN_DATA'            => 'creditPass-Zugang',
    'OECREDITPASS_SETTINGS_SERVICE_URL'           => 'URL',
    'OECREDITPASS_SETTINGS_LOGIN'                 => 'Login',
    'OECREDITPASS_SETTINGS_PASSWORD'              => 'Passwort',
    'OECREDITPASS_SETTINGS_USER_GROUPS'           => 'Benutzergruppen',
    'OECREDITPASS_SETTINGS_USER_GROUPS_EXCL'      => 'Benutzergruppen, die nicht von creditPass geprüft werden sollen',
    'OECREDITPASS_SETTINGS_PROCESSING'            => 'Verarbeitungslogik',
    'OECREDITPASS_SETTINGS_CACHE_TIMEOUT'         => 'creditPass-Prüfung wiederholen (in Tagen, Standard 0, maximal 60)',
    'OECREDITPASS_SETTINGS_MANUAL_WORKFLOW'       => 'Bestellungen wie ausgewählt abschließen, wenn creditPass eine manuelle Prüfung empfiehlt',
    'OECREDITPASS_SETTINGS_MANUAL_EMAIL'          => 'E-Mail-Adresse des Shopbetreibers für die Benachrichtigung zur manuellen Prüfung',
    'OECREDITPASS_SETTINGS_WORKFLOW_ACK'          => 'Autorisiert',
    'OECREDITPASS_SETTINGS_WORKFLOW_NACK'                => 'Nicht autorisiert',
    'OECREDITPASS_SETTINGS_WORKFLOW_MANUAL'              => 'Manuelle Prüfung',
    'OECREDITPASS_SETTINGS_TESTING'                      => 'Test & Debug',
    'OECREDITPASS_SETTINGS_TEST_MODE'                    => 'Test-Modus',
    'OECREDITPASS_SETTINGS_DEBUG_MODE'                   => 'Debug-Modus',
    'OECREDITPASS_SETTINGS_ERROR_UNAUTHORISED'           => 'Fehlermeldung bei Ablehnung der Zahlungsart',
    'OECREDITPASS_ORDER_LASTRESULT'                      => 'Ergebnis letzte Bonitätsabfrage',
    'OECREDITPASS_ORDER_VALUE'                           => 'Wert',
    'OECREDITPASS_ORDER_DATE'                            => 'Datum',
    'OECREDITPASS_ORDER_NORESULTS'                       => 'Es gibt keine creditPass-Prüfung für diese Bestellung.',
    'OECREDITPASS_USER_RESULTS'                          => 'Bisherige Ergebnisse von Bonitätsabfragen',
    'OECREDITPASS_USER_DATE'                             => 'Zeitpunkt',
    'OECREDITPASS_USER_RESULT'                           => 'Ergebnis',
    'OECREDITPASS_USER_CREDITRATING'                     => 'Bonitätswert',
    'OECREDITPASS_USER_CREDITRATING_SCALE'               => 'auf Skala von 0 - 8',
    'OECREDITPASS_USER_REQUESTLINK'                      => 'request',
    'OECREDITPASS_USER_RESPONSELINK'                     => 'response',
    'OECREDITPASS_USER_RESULT_ERROR'                     => 'Fehler bei Abfrage',
    'OECREDITPASS_USER_RESULT_CHECKS'                    => 'Durchgeführte Prüfungen',
    'OECREDITPASS_USER_RESULT_TYPE'                      => 'Art',
    'OECREDITPASS_USER_RESULT_RESULT'                    => 'Ergebnis',
    'OECREDITPASS_USER_RESULT_DETAILS'                   => 'Details',
    'OECREDITPASS_USER_RESULT_DATE'                      => 'Datum',
    'OECREDITPASS_USER_RESULT_CONTENT'                   => 'Inhalt',
    'OECREDITPASS_USER_RESULT_AZ'                        => 'AZ',
    'OECREDITPASS_USER_NORESULTS'                        => 'Es gibt keine creditPass-Prüfungen für diesen Benutzer.',
    'OECREDITPASS_USER_MAINTAINANCE_NOTE'                => 'Bei auffälligen bzw. unerwarteten Prüfungsergebnissen beachten Sie bitte immer auch mögliche Hinweise zu Wartungsarbeiten bei creditPass.',
    'OECREDITPASS_USER_RESULT_DELETE'                    => 'Ergebnis löschen',
    'OECREDITPASS_PAYMENT_SETTINGS_PAYMENT_ACTIVE_NOT'   => 'nein (sichere Zahlungsart)',
    'OECREDITPASS_PAYMENT_SETTINGS_PAYMENT_ACTIVE_YES'   => 'ja (unsichere Zahlungsart)',
    'OECREDITPASS_PAYMENT_SETTINGS_FALLBACK_NOT'         => 'nein',
    'OECREDITPASS_PAYMENT_SETTINGS_FALLBACK_YES'         => 'ja',
    'OECREDITPASS_PAYMENT_SETTINGS_ALLOWONERROR_NOT'     => 'Zahlungsart nicht zulassen',
    'OECREDITPASS_PAYMENT_SETTINGS_ALLOWONERROR_YES'     => 'Zahlungsart zulassen',
    'OECREDITPASS_PAYMENT_SETTINGS_SAVE'                 => 'Speichern',
    'OECREDITPASS_PAYMENT_SETTINGS_PAYMENT_METHOD'       => 'Zahlungsart',
    'OECREDITPASS_PAYMENT_SETTINGS_PAYMENT_ACTIVE'       => 'Prüfung durchführen',
    'OECREDITPASS_PAYMENT_SETTINGS_PAYMENT_FALLBACK'     => 'Fallback',
    'OECREDITPASS_PAYMENT_SETTINGS_PAYMENT_PURCHASETYPE' => 'Purchase Type',
    'OECREDITPASS_PAYMENT_SETTINGS_PAYMENT_ALLOWONERROR' => 'Bei Fehler oder Nichtverfügbarkeit des Dienstes',
    'OECREDITPASS_PAYMENT_SETTINGS_FALLBACK_HINT'        => 'Fallback-Zahlungsarten stehen allen Kunden auch nach vorheriger Ablehnung zu Verfügung. Nur sichere Zahlungsarten können als Fallback eingestellt werden.',
    'OECREDITPASS_LOG_MENU_TITLE'                        => 'creditPass-Prüfungen',
    'OECREDITPASS_LOG_SUBMENU'                           => 'creditPass-Prüfungen',
    'OECREDITPASS_LOG_TAB_LOG'                           => 'creditPass-Prüfung',
    'OECREDITPASS_LOG_LIST_ALL'                          => 'Alle',
    'OECREDITPASS_LOG_LIST_WITH_ORDER'                   => 'Mit Bestellung',
    'OECREDITPASS_LOG_LIST_NO_ORDER'                     => 'Ohne Bestellung',
    'OECREDITPASS_LOG_LIST_ID'                           => 'Log ID',
    'OECREDITPASS_LOG_LIST_CUSTNR'                       => 'Kundennummer',
    'OECREDITPASS_LOG_LIST_ORDERNR'                      => 'Bestellnummer',
    'OECREDITPASS_LOG_LIST_TIMESTAMP'                    => 'Transaktionszeit',
    'OECREDITPASS_LOG_LIST_ANSWERCODE'                   => 'Antwort-Code',
    'OECREDITPASS_LOG_LIST_ANSWERTEXT'            => 'Antwort-Text',
    'OECREDITPASS_LOG_LIST_ANSWERDETAILS'         => 'Antwort-Details',
    'OECREDITPASS_LOG_LIST_SOURCE'                => 'Quelle',
    'OECREDITPASS_LOG_LIST_CACHED'                => 'Gespeichertes Ergebnis',
    'OECREDITPASS_LOG_LIST_NEWCALL'               => 'Neue Anfrage',
    'OECREDITPASS_LOG_LIST_TRANSACTIONID'         => 'Transaktionsreferenz creditPass',
    'OECREDITPASS_LOG_LIST_CUSTOMERTRANSACTIONID' => 'Transaktionsreferenz Shop',
    'OECREDITPASS_LOG_LIST_ACK'                   => 'Autorisiert',
    'OECREDITPASS_LOG_LIST_NACK'                  => 'Nicht autorisiert',
    'OECREDITPASS_LOG_LIST_MANUAL'                => 'Manuelle Prüfung',
    'OECREDITPASS_LOG_LIST_ERROR'                 => 'Keine Antwort oder Fehler',
    'OECREDITPASS_LOG_LIST_EMPTY'                 => '',
    'OECREDITPASS_LOG_LIST_ORDERNUM_EMPTY'        => 'Nicht abgeschlossen',
    'OECREDITPASS_LOG_DETAILS_ORDERNUM_EMPTY'     => 'Nicht abgeschlossen',
    'OECREDITPASS_LOG_USER_ORDERNUM_EMPTY'        => 'Nicht abgeschlossen',
    'OECREDITPASS_LOG_OVERVIEW_ORDERNUM_HINT'     => 'Es wird keine Bestellnummer vergeben, wenn der Kunde die Bestellung nicht abgeschlossen hat.',
    'OECREDITPASS_LOG_USER_ORDERNUM_HINT'         => 'Es wird keine Bestellnummer vergeben, wenn der Kunde die Bestellung nicht abgeschlossen hat.',
    'OECREDITPASS_LOG_NORESULTS'                  => 'Bitte wählen Sie eine creditPass-Prüfung aus der Liste!',
    'OECREDITPASS_EXCEPTION_PURCHASETYPENOTSET'   => 'Bitte geben Sie den Purchase Type für die aktivierte Prüfung der Zahlungsmethode an!',
    'OECREDITPASS_ORDERFOLDER_REVIEW'             => 'Manuelle Prüfung',
    'OECREDITPASS_ERROR_HTTPSWRAPPER'             => 'https-Wrapper von PHP ist nicht installiert.<br />Der https-wrapper muss installiert sein. Installieren Sie hierzu die OpenSSL-Erweiterung von PHP.',
    'OECREDITPASS_ERROR_CALL_IP'                  => 'Die creditPass-URL kann nicht aufgerufen werden.<br />Ihre IP ist von creditPass nicht freigeschaltet. Bitte wenden Sie sich an creditPass.',
    'OECREDITPASS_DEFAULT_ERROR_TITLE'            => 'creditPass - abgelehnte Zahlungsart',
    'OECREDITPASS_DEFAULT_ERROR_MSG'              => 'Die gewünschte Zahlungsart steht derzeit nicht zur Verfügung. Bitte wählen Sie eine andere!',
    'OECREDITPASS_MANUAL_REVIEW_EMAIL_ORDER'      => 'Die unten aufgelisteten Artikel wurden soeben unter [{ $shop->oxshops__oxname->value }] bestellt. Das Ergebnis der creditPass-Prüfung lautet \'Manuelle Prüfung\'. Bitte prüfen Sie daher diese Bestellung!'
];
