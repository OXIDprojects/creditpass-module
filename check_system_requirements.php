<?php

//error boolean. Set to true if error appears
$blError = false;

/**
 * Prints error message
 *
 * @param string $sError  Error message to be printed
 * @param string $sAction Suggestion for to be taken action
 *
 * @return null
 */
function printError($sError, $sAction)
{
    echo "<br>";
    echo '&nbsp;&nbsp;&nbsp;<b><font color="red">Fehler:</font></b> ' . $sError . '<br>';
    echo '&nbsp;&nbsp;&nbsp;<b>Ma&szlig;nahme:</b> ' . $sAction . '<br><br>';
}

/**
 * Prints warning message
 *
 * @param string $sError  Error message to be printed
 * @param string $sAction Suggestion for to be taken action
 *
 * @return null
 *
 */
function printWarning($sError, $sAction)
{
    echo "<br>";
    echo '&nbsp;&nbsp;&nbsp;<b><font color="orange">Warnung:</font></b> ' . $sError . '<br>';
    echo '&nbsp;&nbsp;&nbsp;<b>Ma&szlig;nahme:</b> ' . $sAction . '<br><br>';
}

/**
 * Prints OK message
 *
 * @param string $sNote additional note
 *
 * @return null
 *
 */
function printOk($sNote = '')
{
    if ($sNote) {
        $sNote = '&nbsp;' . $sNote;
    }
    echo " <b><font color='green'>OK</font></b>$sNote<br><br>";
}


/*--------------------------------------
-- check php version and zend decoder --
--------------------------------------*/
echo 'Teste, ob Decoder f&uuml;r PHP installiert ist.';
$sPhpVersion = '';
if (version_compare('5.4', phpversion()) < 0) {
    $sPhpVersion = '5.4';
} elseif (version_compare('5.3', phpversion()) < 0) {
    $sPhpVersion = '5.3';
} elseif (version_compare('5.2', phpversion()) < 0) {
    $sPhpVersion = '5.2';
} else {
    printError(
        "PHP 5.2 / 5.3 / 5.4 wird vorausgesetzt. Installiert ist jedoch: " . phpversion(),
        ". Bitte PHP 5.2.x oder gr&ouml;&szlig;er verwenden."
    );
    $blError = true;
}

/*--------------------------------
---  https-wrapper installed?  ---
--------------------------------*/
echo "Teste ob https-wrapper vorhanden ist";
if (in_array('https', stream_get_wrappers())) {
    printOk();
    $blHttps = true;
} else {
    printError(
        'https-Wrapper von PHP ist nicht installiert.',
        'Der https-wrapper muss installiert sein. Installieren Sie hierzu die OpenSSL-Erweiterung von PHP.'
    );
    $blError = true;
    $blHttps = false;
}

/*-------------------------------------
---  can creditpass URL be opened?  ---
-------------------------------------*/
//only check if https-wrapper is available
if ($blHttps) {
    echo "Teste, ob <i>https://secure.creditpass.de/cpgw/index.cfm</i> ge&ouml;ffnet werden kann";
    if ($sCreditpassContent = @file_get_contents("https://secure.creditpass.de/cpgw/index.cfm", 'r') != false) {
        printOk();
    } else {
        printError(
            'Die creditpass-URL kann nicht aufgerufen werden.',
            'Ihre IP ist von creditpass nicht freigeschaltet. Bitte wenden Sie sich an creditpass.'
        );
        $blError = true;
    }
}

/*-------------------------------------------------
---  Print result of system requirements check  ---
-------------------------------------------------*/
if ($blError) {
    echo '<b><font color="red">Die Systemvoraussetzungen sind nicht erf&uuml;llt.</font></b>';
} else {
    echo '<b><font color="green">Gl&uuml;ckwunsch. Die Systemvoraussetzungen sind erf&uuml;llt. Sie können das creditpass Modul installieren.</font></b>';
}