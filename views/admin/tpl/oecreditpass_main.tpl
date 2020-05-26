[{ include file="headitem.tpl" title="OECREDITPASS_TAB_SETTINGS"|oxmultilangassign }]
[{ if $readonly }]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
[{/if}]

[{oxscript include="js/libs/jquery.min.js"}]
[{oxscript include="js/libs/jquery-ui.min.js"}]
[{oxscript include=$oView->getModuleAdminUrl('out/admin/src/js/chosen_v1.0.0/chosen.jquery.min.js')}]
[{oxscript include=$oView->getModuleAdminUrl('out/admin/src/js/oecreditpass_main.js')}]
[{assign var=maxCachingDays value=$oView->getMaxCacheTtl()}]
[{oxscript add="oeCreditPassMain.setMaxCachingDays($maxCachingDays);"}]

<link rel="stylesheet" type="text/css" href="[{$oView->getModuleAdminUrl('/out/admin/src/js/chosen_v1.0.0/chosen.min.css')}]" />

<style>
    .oecreditpass_redBorder {
        border: 1px red double;
    }
</style>

<script language="JavaScript"><!--
    function _groupExp(el) {
        var _cur = el.parentNode;

        if (_cur.className == "exp") _cur.className = "";
        else _cur.className = "exp";
    }
    //-->
</script>

<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="oxid" value="[{$oxid}]"/>
    <input type="hidden" name="cl" value="oecreditpass_main"/>
    <input type="hidden" name="fnc" value=""/>
    <input type="hidden" name="editlanguage" value="[{$editlanguage}]"/>
</form>

<form name="myedit" id="myedit" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="cl" value="oecreditpass_main">
    <input type="hidden" name="fnc" value="">
    <input type="hidden" name="oxid" value="[{ $oxid }]">

    <div class="groupExp">
        <div class="exp">
            <a href="#" onclick="_groupExp(this);return false;" class="rc"><b>[{ oxmultilang ident="OECREDITPASS_SETTINGS_ACTIVATE" }]</b></a>

            <dl>
                <dt>
                    <input type=hidden name=confbools[blOECreditPassIsActive] value=false>
                    <input type=checkbox name=confbools[blOECreditPassIsActive] value=true  [{if ($confbools.blOECreditPassIsActive)}]checked[{/if}] [{ $readonly}]>
                    [{ oxinputhelp ident="HELP_OECREDITPASS_MAIN_ACTIVATE" }]
                </dt>
                <dd>
                    [{ oxmultilang ident="OECREDITPASS_SETTINGS_IS_ACTIVE" }]
                </dd>
                <div class="spacer"></div>
            </dl>
        </div>
    </div>

    <div class="groupExp">
        <div class="exp">
            <a href="#" onclick="_groupExp(this);return false;" class="rc"><b>[{ oxmultilang ident="OECREDITPASS_SETTINGS_LOGIN_DATA" }]</b></a>

            <dl>
                <dt>
                    <input class="txt" style="width:280px" name="confstrs[sOECreditPassUrl]" value="[{$confstrs.sOECreditPassUrl}]" type="text">
                </dt>
                <dd>
                    [{ oxmultilang ident="OECREDITPASS_SETTINGS_SERVICE_URL" }]
                </dd>
                <div class="spacer"></div>
            </dl>

            <dl>
                <dt>
                    <input class="txt" style="width:280px" name="confstrs[sOECreditPassAuthId]" value="[{$confstrs.sOECreditPassAuthId}]" value="true" type="text">
                </dt>
                <dd>
                    [{ oxmultilang ident="OECREDITPASS_SETTINGS_LOGIN" }]
                </dd>
                <div class="spacer"></div>
            </dl>

            <dl>
                <dt>
                    <input class="txt" style="width:280px" name="confstrs[sOECreditPassAuthPw]" value="[{$confstrs.sOECreditPassAuthPw}]" value="true" type="password">
                </dt>
                <dd>
                    [{ oxmultilang ident="OECREDITPASS_SETTINGS_PASSWORD" }]
                </dd>
                <div class="spacer"></div>
            </dl>

        </div>
    </div>

    <div class="groupExp">
        <div class="">

            <a href="#" onclick="_groupExp(this);return false;" class="rc"><b>[{ oxmultilang ident="OECREDITPASS_SETTINGS_USER_GROUPS" }]</b></a>

            <dl>
                <dt>
                    <input type="hidden" name="confarrs[aOECreditPassExclUserGroups][]" value="">
                    <select class="chosen-select-multiple" data-placeholder="[{ oxmultilang ident="OECREDITPASS_SETTINGS_USER_GROUPS" }]" name="confarrs[aOECreditPassExclUserGroups][]" multiple size="10">
                        [{foreach from=$aUserGroups->aList item=oExclUserGroup}]
                            <option value="[{$oExclUserGroup->oxgroups__oxid->value}]" [{if $oExclUserGroup->blIsExcl}]selected[{/if}]>[{$oExclUserGroup->oxgroups__oxtitle->value}]</option>
                        [{/foreach}]
                    </select>
                </dt>
                <dd>
                    [{ oxmultilang ident="OECREDITPASS_SETTINGS_USER_GROUPS_EXCL" }]
                </dd>
                <div class="spacer"></div>
            </dl>
        </div>
    </div>

    <div class="groupExp">
        <div class="">
            <a href="#" onclick="_groupExp(this);return false;" class="rc"><b>[{ oxmultilang ident="OECREDITPASS_SETTINGS_PROCESSING" }]</b></a>

            <dl>
                <dt>
                    <input id="oecreditpass_checkCacheTimeout" type="text" class="txt" style="width:70px" name="iOECreditPassCheckCacheTimeout" value="[{$oView->getCacheTtl()}]">
                    [{ oxinputhelp ident="HELP_OECREDITPASS_MAIN_CACHING_TTL" }]
                </dt>
                <dd>
                    [{ oxmultilang ident="OECREDITPASS_SETTINGS_CACHE_TIMEOUT" }]
                </dd>
                <div class="spacer"></div>
            </dl>

            <dl>
                <dt>
                    <select id="oecreditpass_selectManualWorkflow" class="chosen-select" data-placeholder="" name="confstrs[iOECreditPassManualWorkflow]">
                        <option value="0" [{if ($confstrs.iOECreditPassManualWorkflow == '0')}]selected[{/if}]>[{ oxmultilang ident="OECREDITPASS_SETTINGS_WORKFLOW_NACK" }]
                        <option value="1" [{if ($confstrs.iOECreditPassManualWorkflow == '1')}]selected[{/if}]>[{ oxmultilang ident="OECREDITPASS_SETTINGS_WORKFLOW_ACK" }]
                        <option value="2" [{if ($confstrs.iOECreditPassManualWorkflow == '2')}]selected[{/if}]>[{ oxmultilang ident="OECREDITPASS_SETTINGS_WORKFLOW_MANUAL" }]
                    </select>
                    [{ oxinputhelp ident="HELP_OECREDITPASS_MAIN_MANUAL_REVIEW" }]
                </dt>
                <dd>
                    [{ oxmultilang ident="OECREDITPASS_SETTINGS_MANUAL_WORKFLOW" }]
                </dd>
                <div class="spacer"></div>
            </dl>

            <dl>
                <dt>
                    <input id="oecreditpass_inputManualEmail" class="txt" style="width:280px" name="confstrs[sOECreditPassManualEmail]" value="[{$confstrs.sOECreditPassManualEmail}]" type="text">

                </dt>
                <dd>
                    [{ oxmultilang ident="OECREDITPASS_SETTINGS_MANUAL_EMAIL" }]
                </dd>
                <div class="spacer"></div>
            </dl>

        </div>
    </div>

    <div class="groupExp">
        <div class="">
            <a href="#" onclick="_groupExp(this);return false;" class="rc"><b>[{ oxmultilang ident="OECREDITPASS_SETTINGS_ERROR_UNAUTHORISED" }]</b></a>

            [{foreach from=$aLangs item=oLang}]
            <dl>
                <dt>
                    <textarea class="textarea" name="sUnauthorizedErrorMsg[oxcontents__oxcontent[{$oView->getLangPrefix($oLang->id)}]]" style="width:500px; height:70px">[{$oView->getUnauthorizedErrorMsg($oLang->id)}]</textarea>
                </dt>
                <dd>
                    [{$oLang->name}]
                </dd>
                <div class="spacer"></div>
            </dl>
            [{/foreach}]

        </div>
    </div>

    <div class="groupExp">
        <div class="">
            <a href="#" onclick="_groupExp(this);return false;" class="rc"><b>[{ oxmultilang ident="OECREDITPASS_SETTINGS_TESTING" }]</b></a>

            <dl>
                <dt>
                    <input type=hidden name=confbools[blOECreditPassTestMode] value=false>
                    <input type=checkbox name=confbools[blOECreditPassTestMode] value=true  [{if ($confbools.blOECreditPassTestMode)}]checked[{/if}] [{ $readonly}]>
                    [{ oxinputhelp ident="HELP_OECREDITPASS_MAIN_TESTIN_MODE" }]
                </dt>
                <dd>
                    [{ oxmultilang ident="OECREDITPASS_SETTINGS_TEST_MODE" }]
                </dd>
                <div class="spacer"></div>
            </dl>

            <dl>
                <dt>
                    <input type=hidden name=confbools[blOECreditPassDebug] value=false>
                    <input type=checkbox name=confbools[blOECreditPassDebug] value=true  [{if ($confbools.blOECreditPassDebug)}]checked[{/if}] [{ $readonly}]>
                    [{ oxinputhelp ident="HELP_OECREDITPASS_MAIN_DEBUG_MODE" }]
                </dt>
                <dd>
                    [{ oxmultilang ident="OECREDITPASS_SETTINGS_DEBUG_MODE" }]
                </dd>
                <div class="spacer"></div>
            </dl>

        </div>
    </div>

    <input id="oecreditpass_buttonMainSave" type="submit" name="save" value="[{ oxmultilang ident="GENERAL_SAVE" }]" onClick="Javascript:document.myedit.fnc.value='save'" [{ $readonly}]>


</form>


[{include file="bottomnaviitem.tpl"}]
[{include file="bottomitem.tpl"}]