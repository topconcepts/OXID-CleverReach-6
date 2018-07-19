(function () {
    $('.js-open-oauth-window').on('click', function () {
        $(this).attr("disabled", "disabled");
        $('#reload-modal').modal('show');
        window.open(oAuthUrl, '', 'width=800,height=450,toolbar=no,scrollbars=no,resizable=no,top=240,left=500,');
    });

    $('.js-reload-frame').on('click', function () {
        top.forceReloadingEditFrame();
        top.reloadEditFrame();
    });

    $('.js-remove-oauth').on('click', function () {
        $('#form-oauth-reset').submit();
    });

    $('.js-create-list-open-modal').on('click', function () {
        $('#create-list-modal').modal('show');
    });

    $('.js-create-list').on('click', function () {
        $('#form-create-list').submit();
    });

    $('.js-disconnect-list').on('click', function () {
        $('#form-disconnect-list').submit();
    });

    $('.js-select-list-open-modal').on('click', function () {
        $('#select-list-modal').modal('show');
    });

    $('.js-select-list').on('click', function () {
        $('#form-select-list').submit();
    });

})();