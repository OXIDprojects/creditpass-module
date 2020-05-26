[{include file="headitem.tpl" title="OECREDITPASS_TAB_ORDER"|oxmultilangassign}]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]"/>
    <input type="hidden" name="cl" value="oecreditpass_order"/>
    <input type="hidden" name="fnc" value=""/>
    <input type="hidden" name="editlanguage" value="[{$editlanguage}]"/>
</form>

[{assign var="aLogDetails" value=$oView->getLogDetails()}]
[{if $aLogDetails.ID != ""}]
    [{include file="oecreditpass_log_details.tpl" aLogDetails=$aLogDetails}]
    [{else}]
    [{oxmultilang ident="OECREDITPASS_ORDER_NORESULTS"}]
    [{/if}]

[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]