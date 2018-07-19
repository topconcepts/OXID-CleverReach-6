[{include file="headitem.tpl" title="SYSREQ_MAIN_TITLE"|oxmultilangassign box="list"}]

<script type="text/javascript">
<!--
window.onload = function ()
{
    top.reloadEditFrame();
    [{ if $updatelist == 1}]
        top.oxid.admin.updateList('[{ $oxid }]');
    [{ /if}]
}
//-->
</script>

<form name="search" id="search" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="actedit" value="[{ $actedit }]">
    <input type="hidden" name="cl" value="CleverReachList">
    <input type="hidden" name="oxid" value="0">
</form>

<div id="liste">

</div>

[{include file="pagetabsnippet.tpl" noOXIDCheck=true oxid=0}]

<script type="text/javascript">
if (parent.parent)
{   parent.parent.sShopTitle   = "[{$actshopobj->oxshops__oxname->getRawValue()|oxaddslashes}]";
    parent.parent.sMenuItem    = "[{ oxmultilang ident="GENERAL_MENUITEM" }]";
    parent.parent.sMenuSubItem = "[{ oxmultilang ident="ATTRIBUTE_LIST_MENUSUBITEM" }]";
    parent.parent.sWorkArea    = "[{$_act}]";
    parent.parent.setTitle();
}
</script>

</body>
</html>







