<?php

/**
 * Defining help texts (Deutsch)
 */

$aLang = [
    'charset'                              => 'UTF-8',
    'HELP_OECREDITPASS_MAIN_ACTIVATE'      => 'Schaltet creditPass Funktionalität an oder aus',
    'HELP_OECREDITPASS_MAIN_CACHING_TTL'   => 'für die angegebenen Tage werden die creditPass Ergebnisse für die aktuelle Nutzer/Adress-Kombination gespeichert. ' .
                                              ' Während die Ergebnisse gespeichert sind, wird für die Nutzer/Adress-Kombination keine creditPass Anfrage gestellt. ' .
                                              'Sollte "0" konfiguriert werden, wird kein Caching durchgeführt.',
    'HELP_OECREDITPASS_MAIN_MANUAL_REVIEW' => 'Konfiguriert, wie sich das creditPass Modul verhält, falls vom credtiPass Dienst manuelle Prüfung empfohlen wird. ' .
                                              'Solle "Manuelle Prüfung" konfiguriert sein und sollte, für eine bestimmte Bestellung, manuelle Prüfung vom creditPass Dienst empfohlen werden, ' .
                                              'so wird die durchgeführte Bestellung für manuelle Prüfung markiert und dies dem Shop-Betreiber per Email an die spezifizierte Email-Adresse mitgeteilt.',
    'HELP_OECREDITPASS_MAIN_TESTIN_MODE'   => 'Schaltet die creditPass-Dienst-Simulation an/aus Falls an, werden Anfragen an die creditPass-Dienst Simulation geschickt. ' .
                                              'Die Antwort der Simulation wird wie folgt durch die letzte Ziffer der Cent-Angabe des Gesamt-Einkaufskorbes bestimmt:' .
                                              '<table><thead><tr><th>Letzte Ziffer</th><th>Antwort der Simulation</th></tr></thead>
                                                    <tr><td>1</td><td>   Nicht Autorisiert</td></tr>
                                                    <tr><td>2</td><td>   Manuelle Prüfung</td></tr>
                                                    <tr><td>8</td><td>   Fehler</td></tr>
                                                    <tr><td>0,3,4,5,6,7,9</td><td> Autorisiert </td></tr></table>

                                                Das simulierte Ergebnis der Anfrage wird im Cache für die spezifizierte Anzahl von Tagen gespeichert.',

    'HELP_OECREDITPASS_MAIN_DEBUG_MODE'       => 'Speichert die vollständigen Anfrage/Antwort-Informationen im xml/ Verzeichnis des Moduls. ' .
                                                 'Speicher Debug-Information in der log/session.log Datei des Moduls.',
    'HELP_OECREDITPASS_PAYMENT_PURCHASE_TYPE' => 'Identifiziert die mit creditPass festgelegte Entscheidungsmatrix (Beispiel: 1,2,3,4).',
];
