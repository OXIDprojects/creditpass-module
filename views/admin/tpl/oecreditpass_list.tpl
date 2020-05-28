[{ include file="headitem.tpl" title="OECREDITPASS_SUBMENU"|oxmultilangassign box="list" }]

<script type="text/javascript">
    <!--
    window.onload = function () {
        top.reloadEditFrame();
        [{if $updatelist == 1}]
        top.oxid.admin.updateList('[{$oxid}]');
        [{/if}]
    }
    //-->
</script>

<form name="search" id="search" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{include file="_formparams.tpl" cl="CreditPassListController" fnc="" oxid=$oxid actedit=$actedit language=$actlang editlanguage=$actlang}]
</form>


<div id="liste">
    <img src="[{$oViewConf->getModuleUrl('oxps/creditpass', 'picture.png')}]" />
</div>

[{include file="pagetabsnippet.tpl"}]

<script type="text/javascript">
    if (parent.parent) {
        parent.parent.sShopTitle = "[{ $actshopobj->oxshops__oxname->value }]";
        parent.parent.sMenuItem = "[{ oxmultilang ident='mxshopsett' }]";
        parent.parent.sMenuSubItem = "[{ oxmultilang ident='OECREDITPASS_SUBMENU' }]";
        parent.parent.sWorkArea = "[{ $_act }]";
        parent.parent.setTitle();
    }
</script>

[{include file="bottomitem.tpl"}]
