[{include file="headitem.tpl" title="OECREDITPASS_TAB_LOG"|oxmultilangassign}]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]"/>
    <input type="hidden" name="cl" value="oecreditpass_log_overview"/>
    <input type="hidden" name="fnc" value=""/>
    <input type="hidden" name="editlanguage" value="[{$editlanguage}]"/>
</form>

[{assign var="aLogDetails" value=$oView->getLogDetails()}]
[{if $aLogDetails.ID != ""}]
    [{include file="oecreditpass_log_details.tpl" aLogDetails=$aLogDetails}]
    [{else}]
    <div>[{oxmultilang ident="OECREDITPASS_LOG_NORESULTS"}]</div>
    [{/if}]

<br/>
<div class="messagebox">[{oxmultilang ident="OECREDITPASS_LOG_OVERVIEW_ORDERNUM_HINT"}]</div>

[{include file="bottomitem.tpl"}]