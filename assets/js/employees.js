(function(){
    'use strict';

    var ajaxEndpoint = (window.coreEmployees && coreEmployees.ajaxurl) ? coreEmployees.ajaxurl : (window.ajaxurl ? window.ajaxurl : (window.location.origin + '/wp-admin/admin-ajax.php'));
    var container = document.getElementById('employees-container');
    var select = document.getElementById('department-filter');

    function showLoader() {
        if (!container) return;
        container.innerHTML = '<p>Загрузка…</p>';
    }

    function fetchDepartmentsHtml(termId) {
        termId = parseInt(termId, 10) || 0;
        var fd = new FormData();
        fd.append('action', 'core_get_departments_employees_ajax');
        fd.append('term_id', termId);
        if (window.coreEmployees && coreEmployees.nonce) {
            fd.append('nonce', coreEmployees.nonce);
        }
        return fetch(ajaxEndpoint, {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
        }).then(function(res){
            if (!res.ok) throw new Error('Network ' + res.status);
            return res.json();
        });
    }

    function renderHtml(html) {
        if (!container) return;
        container.innerHTML = html || '';
        // re-init any global scripts for new nodes (e.g., share buttons)
        if (window.simpleShareInit && typeof window.simpleShareInit === 'function') {
            try { window.simpleShareInit(); } catch(e){ }
        }
        // or re-bind generic handlers:
        if (window.simpleShareInit === undefined) {
            // if your share script attaches on DOMContentLoaded only, consider exposing init.
        }
    }

    // handle select change
    if (select) {
        select.addEventListener('change', function(e){
            var val = this.value;
            showLoader();
            fetchDepartmentsHtml(val).then(function(json){
                if (json && json.success) {
                    renderHtml(json.data.html);
                } else {
                    renderHtml(''); // keep empty
                }
            }).catch(function(err){
                console.error('employees fetch error', err);
                renderHtml('');
            });
        }, false);
    }

    // Also support clicking on department header to filter by it:
    document.addEventListener('click', function(e){
        var head = e.target.closest('.department-head');
        if (!head) return;
        var id = head.getAttribute('data-dept-id');
        if (!id) return;
        // set select to this id (if present)
        if (select) select.value = id;
        // trigger same fetch
        showLoader();
        fetchDepartmentsHtml(id).then(function(json){
            if (json && json.success) {
                renderHtml(json.data.html);
            } else {
                renderHtml('');
            }
        }).catch(function(err){
            console.error('employees fetch error', err);
            renderHtml('');
        });
    }, false);

    // On load: no special action (server already rendered), but you can optionally refresh all
})();
