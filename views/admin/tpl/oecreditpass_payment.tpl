[{include file="headitem.tpl" title="OECREDITPASS_TAB_PAYMENT"|oxmultilangassign}]

<style>
    table.oecreditpass_payment {
        border-collapse: collapse;
    }

    .oecreditpass_payment thead th {
        background-color: #f0f0f0;
        border: 1px solid #c8c8c8;
        padding: 2px 5px;
        color: #000000;
        font-weight: bold;
    }

    .oecreditpass_payment thead th + th {
        border-left: none;
    }

    .oecreditpass_payment tbody td,
    .oecreditpass_payment tbody tr.row1 td {
        background-color: #fafafa;
    }

    .oecreditpass_payment tbody tr.row2 td {
        background-color: #f0f0f0;
    }

    .oecreditpass_payment tbody td + td {
        border-left: 1px solid #ffffff;
    }

    .oecreditpass_payment tbody .title {
        font-weight: bold;
    }

    .oecreditpass_paymentsettings_error {
        border-color: #ff0000;
    }
</style>

[{oxscript include="js/libs/jquery.min.js"}]
[{oxscript include=$oView->getModuleAdminUrl('out/admin/src/js/oecreditpass_payment.js')}]
[{oxscript add="jQuery.noConflict();" priority=10}]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]"/>
    <input type="hidden" name="cl" value="oecreditpass_payment"/>
    <input type="hidden" name="fnc" value=""/>
    <input type="hidden" name="editlanguage" value="[{$editlanguage}]"/>
</form>

<form name="myedit" id="myedit" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="cl" value="oecreditpass_payment"/>
    <input type="hidden" name="fnc" value="save"/>

    <table class="oecreditpass_payment" id="oeCreditPassPaymentSettings">
        <thead>
        <tr>
            <th>[{oxmultilang ident="OECREDITPASS_PAYMENT_SETTINGS_PAYMENT_METHOD"}]</th>
            <th>[{oxmultilang ident="OECREDITPASS_PAYMENT_SETTINGS_PAYMENT_ACTIVE"}]</th>
            <th>[{oxmultilang ident="OECREDITPASS_PAYMENT_SETTINGS_PAYMENT_FALLBACK"}]</th>
            <th>[{oxmultilang ident="OECREDITPASS_PAYMENT_SETTINGS_PAYMENT_PURCHASETYPE"}] [{ oxinputhelp
                ident="HELP_OECREDITPASS_PAYMENT_PURCHASE_TYPE" }]
            </th>
            <th>[{oxmultilang ident="OECREDITPASS_PAYMENT_SETTINGS_PAYMENT_ALLOWONERROR"}]</th>
        </tr>
        </thead>
        <tbody>
        [{assign var="sListClass" value="row1"}]
        [{assign var="aPaymentSettings" value=$oView->getPaymentSettings()}]
        [{foreach from=$aPaymentSettings item=aPaymentSettingValue key=sPaymentSettingKey}]
            [{assign var="sCreditPassId" value=$aPaymentSettingValue->oxpayments__id->value}]
            [{assign var="sCreditPassPaymentId" value=$aPaymentSettingValue->oxpayments__paymentid->value}]
            [{assign var="sPaymentTitle" value=$aPaymentSettingValue->oxpayments__oxdesc->value}]
            [{assign var="sCreditPassPurchaseType" value=$aPaymentSettingValue->oxpayments__purchasetype->value}]
            [{assign var="blCreditPassActive" value=$aPaymentSettingValue->oxpayments__active->value}]
            [{assign var="blCreditPassFallback" value=$aPaymentSettingValue->oxpayments__fallback->value}]
            [{assign var="blCreditPassAllowOnError" value=$aPaymentSettingValue->oxpayments__allowonerror->value}]
            [{assign var="blCreditPassAllowOnErrorDefault" value=$oView->getDefaultAllowOnErrorValue()}]
            <tr class="oeCreditPassPaymentSettingsPaymentMethod [{$sListClass}]">
                <input type="hidden" name="aPaymentSettings[[{$sPaymentSettingKey}]][ID]" value="[{$sCreditPassId}]"/>
                <input type="hidden" name="aPaymentSettings[[{$sPaymentSettingKey}]][PAYMENTID]"
                       value="[{$sCreditPassPaymentId}]"/>
                <td class="title">[{$sPaymentTitle}]</td>
                <td>
                    <select name="aPaymentSettings[[{$sPaymentSettingKey}]][ACTIVE]"
                            class="oeCreditPassPaymentSettingsStatus" data-active="[{$blCreditPassActive}]">
                        <option value="1">[{oxmultilang ident="OECREDITPASS_PAYMENT_SETTINGS_PAYMENT_ACTIVE_YES"}]
                        </option>
                        <option value="0">[{oxmultilang ident="OECREDITPASS_PAYMENT_SETTINGS_PAYMENT_ACTIVE_NOT"}]
                        </option>
                    </select>
                </td>
                <td>
                    <select name="aPaymentSettings[[{$sPaymentSettingKey}]][FALLBACK]"
                            class="oeCreditPassPaymentSettingsFallback" data-active="[{$blCreditPassFallback}]">
                        <option value="1">[{oxmultilang ident="OECREDITPASS_PAYMENT_SETTINGS_FALLBACK_YES"}]</option>
                        <option value="0">[{oxmultilang ident="OECREDITPASS_PAYMENT_SETTINGS_FALLBACK_NOT"}]</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="aPaymentSettings[[{$sPaymentSettingKey}]][PURCHASETYPE]"
                           value="[{$sCreditPassPurchaseType}]" class="oeCreditPassPaymentSettingsPurchaseType"
                           data-active="[{$sCreditPassPurchaseType}]"/>
                </td>
                <td>
                    <select name="aPaymentSettings[[{$sPaymentSettingKey}]][ALLOWONERROR]"
                            class="oeCreditPassPaymentSettingsAllowOnError" data-active="[{$blCreditPassAllowOnError}]"
                            data-default="[{$blCreditPassAllowOnErrorDefault}]">
                        <option value="1">[{oxmultilang ident="OECREDITPASS_PAYMENT_SETTINGS_ALLOWONERROR_YES"}]
                        </option>
                        <option value="0">[{oxmultilang ident="OECREDITPASS_PAYMENT_SETTINGS_ALLOWONERROR_NOT"}]
                        </option>
                    </select>
                </td>
            </tr>
            [{assign var="sPaymentSettingKey" value=$sPaymentSettingKey+1}]
            [{if $sListClass == "row1"}]
            [{assign var="sListClass" value="row2"}]
            [{else}]
            [{assign var="sListClass" value="row1"}]
            [{/if}]
            [{/foreach}]
        </tbody>
    </table>

    <br/>
    <div class="messagebox">[{oxmultilang ident="OECREDITPASS_PAYMENT_SETTINGS_FALLBACK_HINT"}]</div>

    <br/>
    <input type="submit" value="[{oxmultilang ident="OECREDITPASS_PAYMENT_SETTINGS_SAVE"}]"/>
</form>

[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]