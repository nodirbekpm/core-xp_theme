jQuery(document).ready(function ($) {

    function setActiveTab(vacancyId) {
        $('.vacancy_tab .tab-btn').removeClass('is-active active-folder');
        const activeBtn = $(`.vacancy_tab .tab-btn[data-id="${vacancyId}"]`);
        activeBtn.addClass('is-active active-folder');
    }

    function loadVacancy(vacancyId, pushUrl = true) {
        $.ajax({
            url: vacancy_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_vacancy_detail',
                nonce: vacancy_ajax.nonce,
                vacancy_id: vacancyId,
            },
            beforeSend: function () {
                $('.vacancy_content .tab-wrap').html('<p>Загрузка...</p>');
            },
            success: function (res) {
                if (res.success) {
                    $('.vacancy_content .tab-wrap').html(res.data.html);

                    // Aktiv tabni belgilaymiz
                    setActiveTab(vacancyId);

                    // URLni yangilaymiz
                    if (pushUrl) {
                        const newUrl = new URL(window.location.href);
                        newUrl.searchParams.set('vacancy_id', vacancyId);
                        history.pushState({ vacancy_id: vacancyId }, '', newUrl);
                    }
                } else {
                    $('.vacancy_content .tab-wrap').html('<p>Ошибка загрузки</p>');
                }
            },
        });
    }

    // Vacancyga bosilganda yuklash
    $('.vacancy_tab').on('click', '.tab-btn', function (e) {
        e.preventDefault();
        const vacancyId = $(this).data('id');
        setActiveTab(vacancyId);
        loadVacancy(vacancyId);
    });

    // Sahifa reloadsiz back/forward ishlashi
    window.addEventListener('popstate', function (e) {
        const vacancyId = e.state?.vacancy_id || new URL(window.location.href).searchParams.get('vacancy_id');
        if (vacancyId) {
            setActiveTab(vacancyId);
            loadVacancy(vacancyId, false);
        }
    });

    // Agar sahifa URL da vacancy_id bilan ochilgan bo‘lsa — darhol yuklaymiz
    const initVacancyId = new URL(window.location.href).searchParams.get('vacancy_id');
    if (initVacancyId) {
        setActiveTab(initVacancyId);
        loadVacancy(initVacancyId, false);
    }
});
