[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<script src="[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/js/lib/jquery.min.js') }]"></script>
<script src="[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/js/lib/bootstrap.min.js') }]"></script>
<script src="[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/js/lib/tooltipster.bundle.min.js') }]"></script>
<script src="[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/js/tc_cleverreach_tooltips.js') }]"></script>
<link rel="stylesheet" href="[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/css/tc_cleverreach_style.css') }]">
<link rel="stylesheet" href="[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/css/tooltipster.bundle.min.css') }]">
<link rel="stylesheet" href="[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/css/tooltipster-sideTip-light.min.css') }]">

<style type="text/css">

    .tc_div_paths {
        border: 1px solid lightgrey;
        max-width: 800px;
        padding: 10px 8px;
        margin-bottom: 15px;
        margin-top: 10px;
    }

    .box {
        background-image: url("[{ $oViewConf->getModuleUrl('tccleverreach', 'out/src/img/tc_logo.jpg')}]");
        background-position: left bottom;
        background-repeat: no-repeat;
    }

</style>


<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="0">
    <input type="hidden" name="cl" value="CleverReachManualCsv">
</form>


<h2>[{oxmultilang ident="TC_CLEVERREACH_CSV"}]</h2>

<div>
    <span>
        [{oxmultilang ident="TC_CLEVERREACH_CSV_INFO"}]
    </span>
</div>

<div style="margin-top: 20px;">
    <span>
        <b>[{oxmultilang ident="TC_CLEVERREACH_CSV_CRON"}]</b>
    </span>
</div>
<div class="tc_div_paths">
    <span>
        "/usr/bin/php [{$oView->tc_shopBasePath()}]crons/tc_cleverreach.php csv"
    </span>
</div>
<form action="[{$shop->selflink}]" method="post" target="tc_cleverreach_transfer_manual">
    <div class="form-group row">
        <div class="col-xs-3">
            <label for="tc_cleverreach_exportpath">
                [{oxmultilang ident="TC_CLEVERREACH_CSV_INFO_MANUELL_TRANSFER"}]
                <span class="kl-tooltip" data-tooltip-content="#tooltip_content_path">
                    <i class="fa fa-question fa-1" aria-hidden="true"></i>
                </span>
                <div class="tooltip_templates">
                    <span id="tooltip_content_path">
                        [{oxmultilang ident="TC_CLEVERREACH_HELP_EXPORTPATH"}]
                    </span>
                </div>
            </label>
            <input type="text" class="form-control input-sm" id="tc_cleverreach_exportpath"
                   name="tc_cleverreach_exportpath"
                   value="[{$oView->tc_getCsvPath()}]">
        </div>
    </div>

    [{if $oView->getCompletePath()}]
        <div>
            ([{$oView->getCompletePath()}])
        </div>
    [{/if}]

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

    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="cl" value="CleverReachTransferManual">
    <input type="hidden" name="fnc" value="transfer_start_csv">

    <div class="checkbox">
        <label>
            <input type="checkbox" value="1" name="tc_cleverreach_fulllist">
            [{oxmultilang ident="TC_CLEVERREACH_FULL_EXPORT"}]
        </label>
        <span class="kl-tooltip" data-tooltip-content="#tooltip_content_full_export">
            <i class="fa fa-question fa-1" aria-hidden="true"></i>
        </span>
        <div class="tooltip_templates">
            <span id="tooltip_content_full_export">
                [{oxmultilang ident="TC_CLEVERREACH_HELP_FULL_EXPORT_CSV"}]
            </span>
        </div>
    </div>

    <button type="submit" class="btn btn-success">
        [{oxmultilang ident="TC_CLEVERREACH_START_TRANSFER_CSV"}]
    </button>
</form>

<div id="tc_logo">

</div>

[{include file="bottomitem.tpl"}]
