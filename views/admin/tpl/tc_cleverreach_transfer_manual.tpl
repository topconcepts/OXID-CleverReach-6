[{assign var="iStart"       value=$oView->getStart()}]
[{assign var="function"     value=$oView->getFunction()}]
[{assign var="iReceiver"    value=$oView->getReceiver()}]
[{assign var="transfer"     value=$oView->getTransfer()}]
[{assign var="full"         value=$oView->getFull()}]
[{assign var="offset"       value=$oView->getOffset()}]
[{assign var="refresh"      value=$oView->getRefresh()}]
[{assign var="end"          value=$oView->getEnd()}]

[{assign var="tc_error" value=$oView->getTcError()}]
[{assign var="error" value=$oView->getError()}]

[{include file="headitem.tpl" box="export "
title="AUCTMASTER_DO_TITLE"|oxmultilangassign
meta_refresh_sec=$refresh
meta_refresh_url=$oViewConf->getSelfLink()|cat:"&cl=CleverReachTransferManual&iStart=$iStart&fnc=$function&iReceiver=$iReceiver&transfer=$transfer&shopId=$shopId&full=$full&offset=$offset"
}]

[{if $blShowListResetPopUp == true}]
    [{$oView->resetList()}]
    [{oxmultilang ident="TC_CLEVERREACH_LIST_NOT_FOUND"}]
[{elseif $transfer == 'user'}]
    [{oxmultilang ident="TC_CLEVERREACH_SEND_USER"}] [{$iReceiver}]
[{elseif $transfer == 'order'}]
    [{oxmultilang ident="TC_CLEVERREACH_SEND_ORDER"}] [{$iReceiver}]
[{elseif isset($tc_error) == true}]
    [{$tc_error}]
[{elseif isset($error) == true}]
    [{$error}]
[{elseif isset($end) == true && $transfer == 'reset'}]
    [{oxmultilang ident="TC_CLEVERREACH_RESET_COMPLETE"}]
[{elseif isset($end) == true}]
    [{oxmultilang ident="TC_CLEVERREACH_TRANSFER_COMPLETE"}]
[{else}]
    [{oxmultilang ident="TC_CELVERREACH_WELCOME"}]
[{/if}]

<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="0">
    <input type="hidden" name="cl" value="actions_main">
</form>