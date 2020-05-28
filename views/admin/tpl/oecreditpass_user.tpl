[{include file="headitem.tpl" title="OECREDITPASS_TAB_USER"|oxmultilangassign}]

<style>
    /* adapt main.css style */
    table {
        width: 98%;
    }

    td.listheader {
        height: 15px;
    }

    td.listitem,
    td.listitem1,
    td.listitem2,
    td.listitem3 {
        height: 15px;
        vertical-align: top;
    }
</style>

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]"/>
    <input type="hidden" name="cl" value="CreditPassUserController"/>
    <input type="hidden" name="fnc" value=""/>
    <input type="hidden" name="editlanguage" value="[{$editlanguage}]"/>
</form>

[{assign var="aLogList" value=$oView->getLogList()}]
[{if $aLogList|@count > 0}]
    [{assign var="aAnswerCodes" value=$oViewConf->getCreditPassAnswerCodesForLog()}]
    <table cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td class="listheader first">
                [{oxmultilang ident="OECREDITPASS_LOG_LIST_TIMESTAMP"}]
            </td>
            <td class="listheader">
                [{oxmultilang ident="OECREDITPASS_LOG_LIST_ORDERNR"}]
            </td>
            <td class="listheader">
                [{oxmultilang ident="OECREDITPASS_LOG_LIST_ANSWERCODE"}]
            </td>
            <td class="listheader">
                [{oxmultilang ident="OECREDITPASS_LOG_LIST_ANSWERTEXT"}]
            </td>
            <td class="listheader">
                [{oxmultilang ident="OECREDITPASS_LOG_LIST_TRANSACTIONID"}]
            </td>
            <td class="listheader">
                [{oxmultilang ident="OECREDITPASS_LOG_LIST_CUSTOMERTRANSACTIONID"}]
            </td>
        </tr>

        [{assign var="sLineStyle" value=""}]
        [{foreach from=$aLogList item=aLogItem}]
        <tr>
            [{assign var="sListClass" value=listitem$sLineStyle}]
            <td class="[{$sListClass}]">
                [{$aLogItem.TIMESTAMP|oxformdate:'datetime':true}]
            </td>
            <td class="[{$sListClass}]">
                [{if $aLogItem.ORDERNR}]
                [{$aLogItem.ORDERNR}]
                [{else}]
                [{oxmultilang ident="OECREDITPASS_LOG_USER_ORDERNUM_EMPTY"}]
                [{/if}]
            </td>
            <td class="[{$sListClass}]">
                [{assign var="iAnswerCode" value=$aLogItem.ANSWERCODE}]
                [{oxmultilang ident=$aAnswerCodes.$iAnswerCode}]
            </td>
            <td class="[{$sListClass}]">
                [{$aLogItem.ANSWERTEXT}]
            </td>
            <td class="[{$sListClass}]">
                [{$aLogItem.TRANSACTIONID}]
            </td>
            <td class="[{$sListClass}]">
                [{$aLogItem.CUSTOMERTRANSACTIONID}]
            </td>
        </tr>
        [{if $sLineStyle == "2"}]
        [{assign var="sLineStyle" value=""}]
        [{else}]
        [{assign var="sLineStyle" value="2"}]
        [{/if}]
        [{/foreach}]
    </table>
    [{else}]
    <div>[{oxmultilang ident="OECREDITPASS_USER_NORESULTS"}]</div>
    [{/if}]

<br/>
<div class="messagebox">[{oxmultilang ident="OECREDITPASS_LOG_OVERVIEW_ORDERNUM_HINT"}]</div>

[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]