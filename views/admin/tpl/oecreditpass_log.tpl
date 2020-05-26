<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
    <title>[{ oxmultilang ident="OECREDITPASS_LOG_MENU_TITLE" }]</title>
</head>

<frameset rows="300,*" border="0">
    <frame src="[{ $oViewConf->getSelfLink()|oxaddparams:'cl=oecreditpass_log_list' }]&oxid=[{ $oViewConf->getActiveShopId() }]"
           name="list" id="list" frameborder="0" scrolling="Auto" noresize marginwidth="0" marginheight="0">
    <frame src="[{ $oViewConf->getSelfLink()|oxaddparams:'cl=oecreditpass_log_overview' }]&oxid=[{ $oViewConf->getActiveShopId() }]"
           name="edit" id="edit" frameborder="0" scrolling="Auto" noresize marginwidth="0" marginheight="0">
</frameset>

</html>