<style>
    /* adapt main.css style */
    td.edittext {
        height: 15px;
    }
    /* END adapt main.css style */

    td.oecreditpass_log_value {
        font-weight: bold;
        padding-left: 10px;
    }
</style>

[{assign var="aAnswerCodes" value=$oViewConf->getCreditPassAnswerCodesForLog()}]

<table class="oecreditpass_log" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td class="edittext">[{oxmultilang ident="OECREDITPASS_LOG_LIST_ID"}]</td>
        <td class="edittext oecreditpass_log_value">[{$aLogDetails.ID}]</td>
    </tr>
    <tr>
        <td class="edittext">[{oxmultilang ident="OECREDITPASS_LOG_LIST_TIMESTAMP"}]</td>
        <td class="edittext oecreditpass_log_value">[{$aLogDetails.TIMESTAMP|oxformdate}]</td>
    </tr>
    <tr>
        <td class="edittext">[{oxmultilang ident="OECREDITPASS_LOG_LIST_CUSTNR"}]</td>
        <td class="edittext oecreditpass_log_value">[{$aLogDetails.CUSTNR}]</td>
    </tr>
    <tr>
        <td class="edittext">[{oxmultilang ident="OECREDITPASS_LOG_LIST_ORDERNR"}]</td>
        <td class="edittext oecreditpass_log_value">
            [{if $aLogDetails.ORDERNR}]
            [{$aLogDetails.ORDERNR}]
            [{else}]
            [{oxmultilang ident="OECREDITPASS_LOG_DETAILS_ORDERNUM_EMPTY"}]
            [{/if}]
        </td>
    </tr>
    <tr>
        [{assign var="iAnswerCode" value=$aLogDetails.ANSWERCODE}]
        <td class="edittext">[{oxmultilang ident="OECREDITPASS_LOG_LIST_ANSWERCODE"}]</td>
        <td class="edittext oecreditpass_log_value">[{oxmultilang ident=$aAnswerCodes.$iAnswerCode}]</td>
    </tr>
    <tr>
        <td class="edittext">[{oxmultilang ident="OECREDITPASS_LOG_LIST_ANSWERTEXT"}]</td>
        <td class="edittext oecreditpass_log_value">[{$aLogDetails.ANSWERTEXT}]</td>
    </tr>
    <tr>
        <td class="edittext">[{oxmultilang ident="OECREDITPASS_LOG_LIST_CACHED"}]</td>
        <td class="edittext oecreditpass_log_value">[{$aLogDetails.CACHED}]</td>
    </tr>
    <tr>
        <td class="edittext">[{oxmultilang ident="OECREDITPASS_LOG_LIST_ANSWERDETAILS"}]</td>
        <td class="edittext oecreditpass_log_value">[{$aLogDetails.ANSWERDETAILS}]</td>
    </tr>
    <tr>
        <td class="edittext">[{oxmultilang ident="OECREDITPASS_LOG_LIST_TRANSACTIONID"}]</td>
        <td class="edittext oecreditpass_log_value">[{$aLogDetails.TRANSACTIONID}]</td>
    </tr>
    <tr>
        <td class="edittext">[{oxmultilang ident="OECREDITPASS_LOG_LIST_CUSTOMERTRANSACTIONID"}]</td>
        <td class="edittext oecreditpass_log_value">[{$aLogDetails.CUSTOMERTRANSACTIONID}]</td>
    </tr>
</table>