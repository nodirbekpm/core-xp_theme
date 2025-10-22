(function () {
    function $(sel, ctx = document) { return ctx.querySelector(sel); }
    function $all(sel, ctx = document) { return Array.from(ctx.querySelectorAll(sel)); }

    const modal = $('.modal');
    const modalBack = $('.modal-back');
    const form = modal ? modal.querySelector('form') : null;
    let selectedVacancy = null;

    function openModal(vacancyId) {
        selectedVacancy = vacancyId;
        if (modal) {
            modal.classList.add('is-active');
            modal.style.display = 'block';
        }
        if (modalBack) {
            modalBack.classList.add('is-active');
            modalBack.style.display = 'block';
        }
    }

    function closeModal() {
        selectedVacancy = null;
        if (modal) {
            modal.classList.remove('is-active');
            modal.style.display = 'none';
        }
        if (modalBack) {
            modalBack.classList.remove('is-active');
            modalBack.style.display = 'none';
        }
        if (form) form.reset();
    }

    // Modal ochish – har safar AJAX bilan yuklangan vacancy ichidagi .btn-get ni ushlab olamiz
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-get');
        if (!btn) return;
        e.preventDefault();
        const vacancyId = btn.dataset.vacancy;
        openModal(vacancyId);
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.close-modal') || e.target.classList.contains('modal-back')) {
            closeModal();
        }
    });

    // Submit resume AJAX
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const fileInput = form.querySelector('input[type="file"]');
            const comment = form.querySelector('textarea[name="comment"]').value;

            if (!fileInput.files.length) {
                alert('Выберите файл!');
                return;
            }

            const fd = new FormData();
            fd.append('action', 'submit_resume');
            fd.append('nonce', resume_ajax.nonce);
            fd.append('vacancy_id', selectedVacancy);
            fd.append('comment', comment);
            fd.append('file', fileInput.files[0]);

            fetch(resume_ajax.ajax_url, {
                method: 'POST',
                body: fd
            })
                .then(res => res.json())
                .then(data => {
                    const msg = $('#resumeMessage');
                    if (data.success) {
                        msg.style.color = 'green';
                        msg.textContent = data.data;
                        setTimeout(closeModal, 1200);
                    } else {
                        msg.style.color = 'red';
                        msg.textContent = data.data || 'Ошибка при отправке';
                    }
                })
                .catch(() => alert('Ошибка соединения'));
        });
    }
})();
