jQuery(function ($) {
    var $select = $('.year-select');
    var $wrap   = $('#event-items');

    $select.on('change', function () {
        var year = $(this).val() || 'all';

        $wrap.addClass('is-loading'); // ixtiyoriy: loader class
        $.post(CoreEvent.ajax_url, {
            action: 'core_filter_event',
            nonce:  CoreEvent.nonce,
            year:   year
        })
            .done(function (resp) {
                if (resp && resp.success) {
                    $wrap.html(resp.data.html);
                }
            })
            .always(function () {
                $wrap.removeClass('is-loading');
            });
    });
});
