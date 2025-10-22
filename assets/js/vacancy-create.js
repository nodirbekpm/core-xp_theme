jQuery(document).ready(function ($) {

    function updateChoiseLists() {
        $('.vacancy_create_content select').each(function () {
            const $select = $(this);
            const $ul = $select.siblings('.choise');
            if (!$ul.length) return;
            $ul.empty();
            const selected = $select.find('option:selected');
            selected.each(function () {
                const val = $(this).val();
                if (!val) return;
                const text = $(this).text();
                $ul.append(`<li data-val="${val}">${text}<img src="/wp-content/themes/yourtheme/assets/img/icons/cansel.svg" alt=""></li>`);
            });
        });
    }

    function resetForm() {
        $('#vacanci-form')[0].reset();
        $('input[name="vacancy_id"]').val('');
        $('.tab-btn.is-active').removeClass('is-active');
        $('ul.choise').empty();
    }

    function loadVacancy(vacancyId) {
        $.ajax({
            url: vacancyAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_vacancy_detail_full',
                nonce: vacancyAjax.nonce,
                vacancy_id: vacancyId
            },
            success: function (res) {
                if (res.success) {
                    const v = res.data;

                    $('input[name="vacancy_id"]').val(v.id);
                    $('input[name="vacancy_title"]').val(v.title);
                    $('input[name="salary_from"]').val(v.salary_from);
                    $('input[name="salary_to"]').val(v.salary_to);
                    $('textarea[name="responsibilities"]').val(v.responsibilities);
                    $('textarea[name="requirements"]').val(v.requirements);
                    $('textarea[name="conditions"]').val(v.conditions);

                    for (const key in v.terms) {
                        const termIds = v.terms[key];
                        $(`select[name="${key}${Array.isArray(termIds) ? '[]' : ''}"]`).val(termIds);
                    }

                    updateChoiseLists();
                } else {
                    alert('Ошибка загрузки данных');
                }
            }
        });
    }

    $('.tab-menu').on('click', '.tab-btn', function (e) {
        e.preventDefault();
        const vacancyId = $(this).data('id');
        $('.tab-btn').removeClass('is-active');
        $(this).addClass('is-active');
        loadVacancy(vacancyId);
    });

    $('#vacanci-form').on('submit', function (e) {
        e.preventDefault();
        const data = $(this).serialize();

        $.post(vacancyAjax.ajaxUrl, {
            action: 'save_vacancy',
            nonce: vacancyAjax.nonce,
            data
        }, function (res) {
            if (res.success) {
                alert('Вакансия успешно сохранена!');
                location.reload();
            } else {
                alert('Ошибка при сохранении!');
            }
        });
    });

    $('#vacanci-form').on('reset', function (e) {
        e.preventDefault();
        resetForm();
    });

    // Selectlar o'zgarsa choise listni yangilash
    $('.vacancy_create_content select').on('change', updateChoiseLists);

    // Choiseni o'chirish
    $(document).on('click', '.choise li img', function () {
        const $li = $(this).parent();
        const val = $li.data('val');
        const $select = $li.closest('.input-wrap').find('select');
        $select.find(`option[value="${val}"]`).prop('selected', false);
        $li.remove();
    });
});
