jQuery(document).ready(function ($) {

    function initChoise() {
        document.querySelectorAll('.vacancy_create_tab .input-wrap').forEach(wrap => {
            const select = wrap.querySelector('select');
            const choise = wrap.querySelector('.choise');
            if (!select || !choise) return;

            const lis = Array.from(choise.querySelectorAll('li'));
            const options = Array.from(select.options);

            // 1️⃣ Li elementlarga data-value biriktiramiz (shu joyni qayta yoqamiz)
            lis.forEach((li, i) => {
                if (options[i]) {
                    li.dataset.value = options[i].value;
                } else if (!li.dataset.value && li.hasAttribute('value')) {
                    li.dataset.value = li.getAttribute('value');
                }
            });

            // 2️⃣ Li bosilganda ishlovchi funksiya
            lis.forEach(li => {
                li.addEventListener('click', (e) => {
                    const value = li.dataset.value;
                    const isCancel = e.target.closest('img');

                    if (isCancel) {
                        li.classList.remove('selected');
                        select.value = "";
                        select.dispatchEvent(new Event('change', {bubbles: true}));
                        return;
                    }

                    lis.forEach(x => x.classList.remove('selected'));
                    li.classList.add('selected');
                    if (value !== undefined) {
                        select.value = value;
                        select.dispatchEvent(new Event('change', {bubbles: true}));
                    }
                });
            });

            // 3️⃣ Select qiymati o‘zgarsa — li’larni yangilaymiz
            select.addEventListener('change', () => {
                const val = select.value;
                lis.forEach(li => {
                    if (li.dataset.value == val) {
                        li.classList.add('selected');
                    } else {
                        li.classList.remove('selected');
                    }
                });
            });

            // 4️⃣ Dastlabki sinxronlash
            // select.dispatchEvent(new Event('change'));
        });
    }


    function resetForm() {
        $('#vacanci-form')[0].reset();
        $('input[name="vacancy_id"]').val('');
        $('input').val('');
        $('textarea').val('');
        $('.tab-btn.is-active').removeClass('is-active');
        initChoise();
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
            beforeSend: function () {
                $('.vacancy_create_content').addClass('loading');
            },
            success: function (res) {
                $('.vacancy_create_content').removeClass('loading');
                if (res.success) {
                    const v = res.data;

                    $('input[name="vacancy_id"]').val(v.id);
                    $('input[name="vacancy_title"]').val(v.title);
                    $('input[name="salary_from"]').val(v.salary_from);
                    $('input[name="salary_to"]').val(v.salary_to);
                    $('textarea[name="responsibilities"]').val(v.responsibilities);
                    $('textarea[name="requirements"]').val(v.requirements);
                    $('textarea[name="conditions"]').val(v.conditions);

                    initChoise();
                    // Selectlarga qiymatlarni joylash
                    for (const key in v.terms) {
                        const termIds = v.terms[key].map(String);
                        const $select = $(`select[name="${key}"]`);

                        console.log(`Key: ${key}`, "termIds:", termIds);
                        console.log("Select found:", $select.length, " | Options:", $select.find('option').map((i,o)=>o.value).get());

                        if ($select.length) {
                            // 1️⃣ Avval barcha tanlovlarni tozalaymiz
                            $select.find('option').prop('selected', false);

                            // 2️⃣ Kelayotgan ID'larni belgilaymiz
                            termIds.forEach(id => {
                                $select.find(`option[value="${id}"]`).prop('selected', true);
                            });

                            // 3️⃣ Selectni yangilash (event trigger)
                            $select.trigger('change');

                            // 4️⃣ Choise interfeysini ham sinxronlash
                            const wrap = $select.closest('.input-wrap');
                            const $choise = wrap.find('.choise');
                            if ($choise.length) {
                                const $lis = $choise.find('li');
                                $lis.removeClass('selected');

                                $lis.each(function () {
                                    const liVal = $(this).data('value') || $(this).attr('value');
                                    if (termIds.includes(String(liVal))) {
                                        $(this).addClass('selected');
                                    }
                                });
                            }
                        } else {
                            console.warn(`⚠️ Select not found for key: "${key}"`);
                        }
                    }



                } else {
                    alert('Ошибка загрузки данных');
                }
            }
        });
    }

    // Tab bosilganda
    $('.vacancy_create_tab .tab-menu').on('click', '.tab-btn', function (e) {
        e.preventDefault();
        const vacancyId = $(this).data('id');
        $('.tab-btn').removeClass('is-active');
        $(this).addClass('is-active');
        loadVacancy(vacancyId);
    });

    // Forma submit
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

    // Forma reset
    $('#vacanci-form').on('reset', function (e) {
        e.preventDefault();
        resetForm();
    });

    // Sahifa yuklanganda choise’larni ishga tushirish
    initChoise();
});
