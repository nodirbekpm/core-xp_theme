jQuery(function ($) {
    var $select = $('.year-select');
    var $wrap   = $('#news-items');

    $select.on('change', function () {
        var year = $(this).val() || 'all';

        $wrap.addClass('is-loading'); // ixtiyoriy: loader class
        $.post(CoreNews.ajax_url, {
            action: 'core_filter_news',
            nonce:  CoreNews.nonce,
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
