[{include file="headitem.tpl" title="OECREDITPASS_LOG_SUBMENU"|oxmultilangassign box="list"}]

<style>
    /* adapt main.css style */
    #liste table {
        width: 100%;
    }

    #liste td.listfilter {
        height: 20px;
        vertical-align: top;
    }

    #liste td.listheader {
        height: 15px;
    }

    #liste td.listitem,
    #liste td.listitem1,
    #liste td.listitem2,
    #liste td.listitem3 {
        height: 15px;
        vertical-align: top;
    }
</style>

<script type="text/javascript">
    <!--
    window.onload = function () {
        top.reloadEditFrame();
        [{if $updatelist == 1}]
        top.oxid.admin.updateList('[{$oxid}]');
        [{/if}]
    }

    function sortCreditPassLogList(column) {
        top.oxid.admin.setSorting(document.search, 'oecreditpasslog', column, 'asc');
        document.search.submit();
    }

    function submitCreditPassLogList() {
        document.search.submit();
    }

    function editCreditPassLogListItem(item) {
        top.oxid.admin.editThis(item);
    }

    //-->
</script>

<div id="liste">
    [{assign var="aListFilter" value=$oView->getListFilter()}]
    [{assign var="aAnswerCodes" value=$oViewConf->getCreditPassAnswerCodesForLog()}]

    <form name="search" id="search" action="[{$oViewConf->getSelfLink()}]" method="post">
        [{include file="_formparams.tpl" cl="CreditPassLogListController" fnc="" oxid=$oxid actedit=$actedit language=$actlang editlanguage=$actlang}]

        <table cellpadding="0" cellspacing="0" border="0">
            <colgroup>
                <col>
                <col>
                <col>
                <col>
                <col>
                <col>
                <col>
            </colgroup>
            <tr>
                <td class="listfilter first">
                    <div class="r1">
                        <div class="b1">
                            <select name="pwrsearchfld" onChange="submitCreditPassLogList();">
                                [{assign var="aOrderFilters" value=$oView->getOrderFilters()}]
                                [{foreach from=$aOrderFilters item=aOrderFilter}]
                                <option value="[{$aOrderFilter.code}]"
                                        [{if $aOrderFilter.code == $pwrsearchfld}]selected[{/if}]>
                                    [{oxmultilang ident=$aOrderFilter.desc}]
                                </option>
                                [{/foreach}]
                            </select>
                            <input class="listedit" type="text" size="16" maxlength="32"
                                   name="where[oecreditpasslog][timestamp]"
                                   value="[{$aListFilter.oecreditpasslog.timestamp|oxformdate}]"/>
                        </div>
                    </div>
                </td>
                <td class="listfilter">
                    <div class="r1">
                        <div class="b1">
                        </div>
                    </div>
                </td>
                <td class="listfilter">
                    <div class="r1">
                        <div class="b1">
                        </div>
                    </div>
                </td>
                <td class="listfilter">
                    <div class="r1">
                        <div class="b1">
                            <select name="where[oecreditpasslog][answercode]" onChange="submitCreditPassLogList();">
                                [{assign var="aAnswerCodeFilters" value=$oView->getAnswerCodeFilters()}]
                                [{foreach from=$aAnswerCodeFilters item=aAnswerCodeFilter}]
                                <option value="[{$aAnswerCodeFilter.code}]"
                                        [{if $aAnswerCodeFilter.code == $aListFilter.oecreditpasslog.answercode}]selected[{/if}]>
                                    [{oxmultilang ident=$aAnswerCodeFilter.desc}]
                                </option>
                                [{/foreach}]
                            </select>
                        </div>
                    </div>
                </td>
                <td class="listfilter">
                    <div class="r1">
                        <div class="b1">
                            <input class="listedit" type="text" size="32" maxlength="128"
                                   name="where[oecreditpasslog][answertext]"
                                   value="[{$aListFilter.oecreditpasslog.answertext}]"/>
                        </div>
                    </div>
                </td>
                <td class="listfilter">
                    <div class="r1">
                        <div class="b1">
                            <select name="where[oecreditpasslog][cached]" onChange="submitCreditPassLogList();">
                                <option value="">[{ oxmultilang ident="OECREDITPASS_LOG_LIST_ALL" }]</option>
                                <option value="1" [{if $aListFilter.oecreditpasslog.cached === "1"}]selected[{/if}]>
                                    [{oxmultilang ident="OECREDITPASS_LOG_LIST_CACHED"}]
                                </option>
                                <option value="0" [{if $aListFilter.oecreditpasslog.cached === "0"}]selected[{/if}]>
                                    [{oxmultilang ident="OECREDITPASS_LOG_LIST_NEWCALL"}]
                                </option>
                            </select>
                        </div>
                    </div>
                </td>
                <td class="listfilter">
                    <div class="r1">
                        <div class="b1">
                            <input class="listedit" type="text" size="16" maxlength="32"
                                   name="where[oecreditpasslog][transactionid]"
                                   value="[{$aListFilter.oecreditpasslog.transactionid}]"/>
                        </div>
                    </div>
                </td>
                <td class="listfilter" colspan="2" nowrap>
                    <div class="r1">
                        <div class="b1">
                            <div class="find">
                                <input class="listedit" type="button" name="submitit"
                                       value="[{oxmultilang ident="GENERAL_SEARCH"}]"
                                       onClick="submitCreditPassLogList();"/>
                            </div>
                            <input class="listedit" type="text" size="16" maxlength="32"
                                   name="where[oecreditpasslog][customertransactionid]"
                                   value="[{$aListFilter.oecreditpasslog.customertransactionid}]"/>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="listheader first">
                    <a href="javascript:sortCreditPassLogList( 'timestamp' );"
                       class="listheader">[{oxmultilang ident="OECREDITPASS_LOG_LIST_TIMESTAMP"}]</a>
                </td>
                <td class="listheader">
                    [{oxmultilang ident="OECREDITPASS_LOG_LIST_CUSTNR"}]
                </td>
                <td class="listheader">
                    [{oxmultilang ident="OECREDITPASS_LOG_LIST_ORDERNR"}]
                </td>
                <td class="listheader">
                    <a href="javascript:sortCreditPassLogList( 'answercode' );"
                       class="listheader">[{oxmultilang ident="OECREDITPASS_LOG_LIST_ANSWERCODE"}]</a>
                </td>
                <td class="listheader">
                    <a href="javascript:sortCreditPassLogList( 'answertext' );"
                       class="listheader">[{oxmultilang ident="OECREDITPASS_LOG_LIST_ANSWERTEXT"}]</a>
                </td>
                <td class="listheader">
                    <a href="javascript:sortCreditPassLogList( 'cached' );"
                       class="listheader">[{oxmultilang ident="OECREDITPASS_LOG_LIST_SOURCE"}]</a>
                </td>
                <td class="listheader">
                    <a href="javascript:sortCreditPassLogList( 'transactionid' );"
                       class="listheader">[{oxmultilang ident="OECREDITPASS_LOG_LIST_TRANSACTIONID"}]</a>
                </td>
                <td class="listheader" colspan="2">
                    <a href="javascript:sortCreditPassLogList( 'customertransactionid' );"
                       class="listheader">[{oxmultilang ident="OECREDITPASS_LOG_LIST_CUSTOMERTRANSACTIONID"}]</a>
                </td>
            </tr>

            [{assign var="sLineStyle" value=""}]
            [{assign var="_cnt" value=0}]
            [{foreach from=$mylist item=oLogItem}]
            [{assign var="_cnt" value=$_cnt+1}]
            <tr id="row.[{$_cnt}]">
                [{assign var="sListClass" value=listitem$sLineStyle}]
                <td class="[{$sListClass}]">
                    <div class="listitemfloating">
                        <a href="javascript:editCreditPassLogListItem('[{$oLogItem->oecreditpasslog__id->value}]');"
                           class="[{$sListClass}]">[{$oLogItem->oecreditpasslog__timestamp|oxformdate:'datetime':true}]</a>
                    </div>
                </td>
                <td class="[{$sListClass}]">
                    <div class="listitemfloating">
                        <a href="javascript:editCreditPassLogListItem('[{$oLogItem->oecreditpasslog__id->value}]');"
                           class="[{$sListClass}]">[{$oLogItem->oecreditpasslog__custnr->value}]</a>
                    </div>
                </td>
                <td class="[{$sListClass}]">
                    <div class="listitemfloating">
                        <a href="javascript:editCreditPassLogListItem('[{$oLogItem->oecreditpasslog__id->value}]');"
                           class="[{$sListClass}]">
                            [{if $oLogItem->oecreditpasslog__ordernr->value}]
                            [{$oLogItem->oecreditpasslog__ordernr->value}]
                            [{else}]
                            [{oxmultilang ident="OECREDITPASS_LOG_LIST_ORDERNUM_EMPTY"}]
                            [{/if}]
                        </a>
                    </div>
                </td>
                <td class="[{$sListClass}]">
                    <div class="listitemfloating">
                        [{assign var="iAnswerCode" value=$oLogItem->oecreditpasslog__answercode->value}]
                        <a href="javascript:editCreditPassLogListItem('[{$oLogItem->oecreditpasslog__id->value}]');"
                           class="[{$sListClass}]">[{oxmultilang ident=$aAnswerCodes.$iAnswerCode}]</a>
                    </div>
                </td>
                <td class="[{$sListClass}]">
                    <div class="listitemfloating">
                        <a href="javascript:editCreditPassLogListItem('[{$oLogItem->oecreditpasslog__id->value}]');"
                           class="[{$sListClass}]">[{$oLogItem->oecreditpasslog__answertext->value|oxtruncate:50:"...":true}]</a>
                    </div>
                </td>
                <td class="[{$sListClass}]">
                    <div class="listitemfloating">
                        <a href="javascript:editCreditPassLogListItem('[{$oLogItem->oecreditpasslog__id->value}]');"
                           class="[{$sListClass}]">
                            [{if $oLogItem->oecreditpasslog__cached->value}]
                            [{oxmultilang ident="OECREDITPASS_LOG_LIST_CACHED"}]
                            [{else}]
                            [{oxmultilang ident="OECREDITPASS_LOG_LIST_NEWCALL"}]
                            [{/if}]
                        </a>
                    </div>
                </td>
                <td class="[{$sListClass}]">
                    <div class="listitemfloating">
                        <a href="javascript:editCreditPassLogListItem('[{$oLogItem->oecreditpasslog__id->value}]');"
                           class="[{$sListClass}]">[{$oLogItem->oecreditpasslog__transactionid->value}]</a>
                    </div>
                </td>
                <td class="[{$sListClass}]" colspan="2">
                    <div class="listitemfloating">
                        <a href="javascript:editCreditPassLogListItem('[{$oLogItem->oecreditpasslog__id->value}]');"
                           class="[{$sListClass}]">[{$oLogItem->oecreditpasslog__customertransactionid->value}]</a>
                    </div>
                </td>
            </tr>
            [{if $sLineStyle == "2"}]
            [{assign var="sLineStyle" value=""}]
            [{else}]
            [{assign var="sLineStyle" value="2"}]
            [{/if}]
            [{/foreach}]

            [{include file="pagenavisnippet.tpl" colspan="8"}]
        </table>

    </form>

</div>

[{include file="pagetabsnippet.tpl"}]

<script type="text/javascript">
    if (parent.parent) {
        parent.parent.sShopTitle = "[{$actshopobj->oxshops__oxname->value}]";
        parent.parent.sMenuItem = "[{oxmultilang ident='mxshopsett'}]";
        parent.parent.sMenuSubItem = "[{oxmultilang ident='OECREDITPASS_LOG_SUBMENU'}]";
        parent.parent.sWorkArea = "[{$_act}]";
        parent.parent.setTitle();
    }
</script>

[{include file="bottomitem.tpl"}]