[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]
[{assign var="lastTransfer" value=$oView->tc_getLastTransfer()}]
<script src="[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/js/lib/jquery.min.js') }]"></script>
<script src="[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/js/lib/bootstrap.min.js') }]"></script>
<script src="[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/js/lib/tooltipster.bundle.min.js') }]"></script>
<script src="[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/js/tc_cleverreach_tooltips.js') }]"></script>
<link rel="stylesheet" href="[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/css/tc_cleverreach_style.css') }]">
<link rel="stylesheet" href="[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/css/tooltipster.bundle.min.css') }]">
<link rel="stylesheet" href="[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/css/tooltipster-sideTip-light.min.css') }]">

<script type="application/javascript">
    var oAuthUrl = "[{$oView->tc_getOAuthUrl()}]";
</script>

<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="0">
    <input type="hidden" name="cl" value="CleverReachConfig">
</form>
<div class="container">
    <h2>[{oxmultilang ident="TC_CLEVERREACH_START"}]</h2>
    <br>
    <div id="reload-modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body js-reload-frame">
                    <h4 style="color: red;text-align: center">[{ oxmultilang ident="TC_CLEVERREACH_OAUTH_RELOAD" }]</h4>
                </div>
            </div>
        </div>
    </div>

    [{assign var="isOAuthTokenValid" value=$oView->tc_isOAuthTokenValid()}]
    [{if !$isOAuthTokenValid }]
        <div class="oauth-status">
            <div class="status-indicator disconnected"></div>
            [{oxmultilang ident="TC_CLEVERREACH_OAUTH_DISC"}]
        </div>
        <div class="first-usage">
            <div class="panel panel-default panel-quickmail">
                <div class="panel-body">
                    <img src="[{$oViewConf->getModuleUrl('tccleverreach', 'out/src/img/icon_quickstartmailing.svg') }]"
                         class="img-responsive center-block">
                    <h2>[{oxmultilang ident="TC_CLEVERREACH_GET_STARTED"}]</h2>
                    <p>[{oxmultilang ident="TC_CLEVERREACH_GET_STARTED_HELPER_TEXT"}] </p>
                    <button class="btn btn-success js-open-oauth-window">
                        [{oxmultilang ident="TC_CLEVERREACH_OAUTH_NEEDED_BUTTON"}]
                    </button>
                </div>
            </div>
        </div>
    [{else}]
        <div class="oauth-status">
            <form action="[{$oViewConf->getSelfLink()}]" method="post" name="form-oauth-reset" id="form-oauth-reset">
                [{$oViewConf->getHiddenSid()}]
                <input type="hidden" name="cl" value="CleverReachConfig">
                <input type="hidden" name="fnc" value="resetOAuthToken">
            </form>
            <div class="status-indicator connected"></div>
            [{oxmultilang ident="TC_CLEVERREACH_OAUTH_DONE"}]
            <a href="#" class="js-remove-oauth">
                [{oxmultilang ident="TC_CLEVERREACH_OAUTH_RESET"}]
            </a>
        </div>
        [{if $oView->getListId()}]
            <div class="oauth-status row">
                <strong>[{oxmultilang ident="TC_CLEVERREACH_CURRENT"}]:</strong> [{$oView->getListNameById()}]
                <form action="[{$shop->selflink}]" method="post" class="form-inline" name="form-disconnect-list" id="form-disconnect-list">
                    <input type="hidden" name="cl" value="CleverReachConfig">
                    <input type="hidden" name="fnc" value="disconnectList">
                    <a href="#" class="js-disconnect-list"><i class="fa fa-chain-broken" aria-hidden="true"></i></a>
                </form>
            </div>
        [{/if}]
        [{if !$oView->getListId()}]
            <form action="[{$oViewConf->getSelfLink()}]" method="post" name="form-create-list" id="form-create-list">
                [{$oViewConf->getHiddenSid()}]
                <input type="hidden" name="cl" value="CleverReachConfig">
                <input type="hidden" name="fnc" value="createList">

                <div id="create-list-modal" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">[{oxmultilang ident="TC_CLEVERREACH_CREATE_LIST"}]</h4>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="list-name">[{oxmultilang ident="TC_CLEVERREACH_GROUP_NAME"}]</label>
                                    <input type="text" class="form-control" name="list-name">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button"
                                        class="btn btn-success js-create-list">[{oxmultilang ident="TC_CLEVERREACH_CREATE_LIST"}]</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="first-usage">
                <div class="panel panel-default panel-quickmail">
                    <div class="panel-body">
                        <i class="icon-group"></i>

                        <h2>[{oxmultilang ident="TC_CLEVERREACH_CREATE_LIST"}]</h2>
                        <p class="text-muted">[{oxmultilang ident="TC_CLEVERREACH_CREATE_LIST_HELPER_TEXT"}]</p>
                        <button class="btn btn-default js-create-list-open-modal">
                            [{oxmultilang ident="TC_CLEVERREACH_CREATE_LIST"}]
                        </button>
                        <br>
                        [{oxmultilang ident="TC_CLEVERREACH_EXISTING_LIST"}] <a href="#" class="js-select-list-open-modal">[{oxmultilang ident="TC_CLEVERREACH_EXISTING_ONE"}]</a>
                    </div>
                </div>
            </div>
            <form action="[{$shop->selflink}]" method="post" class="form-inline" name="form-select-list" id="form-select-list">
                [{$oViewConf->getHiddenSid()}]
                <div id="select-list-modal" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">[{oxmultilang ident="TC_CLEVERREACH_LIST"}]</h4>
                            </div>
                            <div class="modal-body">
                                    <input type="hidden" name="cl" value="CleverReachConfig">
                                    <input type="hidden" name="fnc" value="select_list">
                                    <div class="dropdown">
                                        <div class="form-group">
                                            <select class="form-control" name="selectlist" id="select_list">
                                                <option value="">-</option>
                                                [{foreach from=$oView->getLists() item=list}]
                                                <option value="[{$list->id}]" [{if $oView->getListId() == $list->id}]selected[{/if}]>[{$list->name}]</option>
                                                [{/foreach}]
                                            </select>
                                        </div>
                                    </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button"
                                        class="btn btn-success js-select-list">[{oxmultilang ident="TC_CLEVERREACH_SELECT_LIST"}]</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        [{else}]
            <div class="first-usage">
                <div class="panel panel-default panel-quickmail">
                    <div class="panel-body">
                        <i class="fa fa-check-square-o" aria-hidden="true"></i>

                        <h2>[{oxmultilang ident="TC_CLEVERREACH_LETS_GET_STARTED"}]</h2>
                        <p class="text-muted">[{oxmultilang ident="TC_CLEVERREACH_LETS_GET_STARTED_HELPER_TEXT"}]</p>
                        <form action="[{$shop->selflink}]" method="post" target="tc_cleverreach_transfer_manual">
                            <input type="hidden" name="cl" value="CleverReachTransferManual">
                            <input type="hidden" name="fnc" value="transfer_start">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="1" name="tc_cleverreach_with_orders"
                                           [{if $oView->getOptionToggleOrders()}]CHECKED[{/if}]>
                                    [{oxmultilang ident="TC_CLEVERREACH_WITH_ORDERS"}]
                                </label>
                                <span class="kl-tooltip" data-tooltip-content="#tooltip_content_order">
                                    <i class="fa fa-question fa-1" aria-hidden="true"></i>
                                </span>
                                <div class="tooltip_templates">
                                        <span id="tooltip_content_order">
                                            [{oxmultilang ident="TC_CLEVERREACH_HELP_ORDER"}]
                                        </span>
                                </div>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="1" name="tc_cleverreach_fulllist">
                                    [{oxmultilang ident="TC_CLEVERREACH_FULL_EXPORT"}]
                                </label>
                                <span class="kl-tooltip"
                                      data-tooltip-content="#tooltip_content_full_export">
                                    <i class="fa fa-question fa-1" aria-hidden="true"></i>
                                </span>
                                <div class="tooltip_templates">
                                    <span id="tooltip_content_full_export">
                                        [{oxmultilang ident="TC_CLEVERREACH_HELP_FULL_EXPORT"}]
                                    </span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success js-start-transfer">
                                [{oxmultilang ident="TC_CLEVERREACH_START_TRANSFER"}]
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <span>
                <b>[{oxmultilang ident="TC_CLEVERREACH_LAST_TRANSFER"}]</b>: [{if !$lastTransfer}][{oxmultilang ident="TC_CLEVERREACH_NOT_YET"}][{else}][{$lastTransfer}][{/if}]
                <br><br>
            </span>
            <div>
            <span>
                [{oxmultilang ident="TC_CLEVERREACH_TRANSFER_INFO"}]
            </span>
            </div>
            <div style="margin-top: 20px;">
            <span>
                <b>[{oxmultilang ident="TC_CLEVERREACH_TRANSFER_INFO_CONFIG_CRON"}]</b>
            </span>
            </div>
            <div class="tc_div_paths">
                <div style="margin-bottom: 5px;">
                    [{oxmultilang ident="TC_CLEVERREACH_CRON_RECIPIENTS"}]
                    <br>
                    <span>
                        "/usr/bin/php [{$oViewConf->getModulePath('tccleverreach', "/")}]crons/tc_cleverreach.php"
                    </span>
                </div>
                <div style="margin-bottom: 5px;">
                    [{oxmultilang ident="TC_CLEVERREACH_CRON_ORDER"}]
                    <br>
                    <span>
                        "/usr/bin/php [{$oViewConf->getModulePath('tccleverreach', "/")}]crons/tc_cleverreach.php orders"
                    </span>
                </div>
            </div>
        [{/if}]
    [{/if}]
</div>
[{oxscript include=$oViewConf->getModuleUrl('tccleverreach', "out/src/js/tc_cleverreach_start.js") }]
[{include file="bottomitem.tpl"}]
