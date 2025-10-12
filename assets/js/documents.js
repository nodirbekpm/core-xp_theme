(function(){
    'use strict';

    // === CONFIG ===
    // Agar true bo'lsa, agar hech qanday folder tanlanmagan bo'lsa avtomatik birinchi folderni yuklaydi.
    // Siz: hozir defaultni ko'rsatmaslik uchun false qo'ying.
    var ALLOW_DEFAULT = false;

    var ajaxEndpoint = (window.coreDocs && coreDocs.ajaxurl) ? coreDocs.ajaxurl : (window.ajaxurl ? window.ajaxurl : (window.location.origin + '/wp-admin/admin-ajax.php'));
    var cache = Object.create(null);
    var lastClickTime = 0;
    var DEBOUNCE_MS = 200;

    function showLoader() {
        var c = document.getElementById('documents-list');
        if (c) c.innerHTML = '<p>Загрузка...</p>';
    }

    function ajaxFetchDocuments(termId, includeChildren) {
        includeChildren = !!includeChildren;
        var cacheKey = termId + (includeChildren ? ':inc' : ':direct');
        if (cache[cacheKey]) {
            return Promise.resolve({ success: true, data: { html: cache[cacheKey], docs_count: (cache[cacheKey].indexOf('document-item') !== -1) ? 1 : 0 } });
        }

        var fd = new FormData();
        fd.append('action', 'core_get_documents_by_folder_ajax');
        fd.append('term_id', termId);
        fd.append('include_children', includeChildren ? '1' : '0');

        return fetch(ajaxEndpoint, { method: 'POST', credentials: 'same-origin', body: fd })
            .then(function(res){
                if (!res.ok) throw new Error('Network error ' + res.status);
                return res.json();
            })
            .then(function(json){
                if (json && json.success && json.data && json.data.html && json.data.html.trim().length) {
                    cache[cacheKey] = json.data.html;
                }
                return json;
            });
    }

    function renderDocumentsHtml(html) {
        var container = document.getElementById('documents-list');
        if (!container) {
            console.warn('documents.js: #documents-list not found');
            return false;
        }
        container.innerHTML = html;
        return true;
    }

    function clearActiveFolders() {
        document.querySelectorAll('#doc-folders .tab-btn').forEach(function(el){
            el.classList.remove('active-folder');
        });
    }

    function ensureParentOpen(tabBtn) {
        var parent = tabBtn.parentElement.closest('.tab-btn');
        if (parent) parent.classList.add('is-open');
    }

    function clearActiveTabItems() {
        document.querySelectorAll('.tab-item').forEach(function(el){
            el.classList.remove('is-active');
        });
    }

    function getFolderIdFromUrl() {
        try {
            var sp = new URL(window.location).searchParams;
            var val = sp.get('folder_id');
            return val ? val : null;
        } catch (e) { return null; }
    }

    function pushFolderToUrl(folderId) {
        try {
            var url = new URL(window.location);
            url.searchParams.set('folder_id', folderId);
            window.history.replaceState({}, '', url);
        } catch(e){}
    }

    // If server-side provided data-selected attribute on #doc-folders it will be available
    function getFolderIdFromDataSelected() {
        var docFolders = document.getElementById('doc-folders');
        if (!docFolders) return null;
        return docFolders.dataset && docFolders.dataset.selected ? docFolders.dataset.selected : null;
    }

    // safe rendering helper with retry (keeps visual .tab-item.is-active in sync)
    function safeInsertHtml(json, folderId) {
        var html = '';
        if (json && json.success && json.data) {
            if (json.data.html && json.data.html.trim().length) {
                html = json.data.html;
            } else if (json.data.docs_count && json.data.docs_count > 0 && json.data.titles && json.data.titles.length) {
                html = '<div class="documents-row">';
                json.data.titles.forEach(function(t){
                    html += '<div class="document-item"><div class="document-right"><div class="item-title">'+ escapeHtml(t) +'</div></div></div>';
                });
                html += '</div>';
            }
        }
        if (!html) html = '<p>Документы не найдены</p>';

        // render and ensure .tab-item.is-active is present (visual)
        renderDocumentsHtml(html);
        clearActiveTabItems();
        var tabItem = document.querySelector('.tab-item');
        if (tabItem) tabItem.classList.add('is-active');

        // double-check presence of .document-item; if server returned HTML but DOM doesn't show items (race),
        // retry once after short delay (120ms)
        var container = document.getElementById('documents-list');
        var hasItems = container && container.querySelector('.document-item');

        if (!hasItems && json && json.success && json.data && json.data.html && json.data.html.trim().length) {
            setTimeout(function(){
                renderDocumentsHtml(json.data.html);
                var t2 = document.querySelector('.tab-item');
                if (t2) t2.classList.add('is-active');
                var has2 = container && container.querySelector('.document-item');
                if (!has2) {
                    // fallback to titles if still nothing
                    if (json.data.titles && json.data.titles.length) {
                        var fallback = '<div class="documents-row">';
                        json.data.titles.forEach(function(t){ fallback += '<div class="document-item"><div class="document-right"><div class="item-title">'+ escapeHtml(t) +'</div></div></div>'; });
                        fallback += '</div>';
                        renderDocumentsHtml(fallback);
                    } else {
                        renderDocumentsHtml('<p>Документы не найдены</p>');
                    }
                    var t3 = document.querySelector('.tab-item');
                    if (t3) t3.classList.add('is-active');
                }
            }, 120);
        }
    }

    // ---------- Click handling ----------
    document.addEventListener('click', function(e){
        var head = e.target.closest('.tab-head');
        if (!head) return;

        e.preventDefault();

        var now = Date.now();
        if (now - lastClickTime < DEBOUNCE_MS) return;
        lastClickTime = now;

        var tabBtn = head.closest('.tab-btn');
        if (!tabBtn) return;

        var clickedId = tabBtn.getAttribute('data-folder-id');

        // toggle visual inner menu for folders that have children
        var innerMenu = tabBtn.querySelector('.inner-menu');
        if (innerMenu) tabBtn.classList.toggle('is-open');

        clearActiveFolders();
        tabBtn.classList.add('active-folder');
        ensureParentOpen(tabBtn);

        // make content tab visible
        clearActiveTabItems();
        var tabItem = document.querySelector('.tab-item');
        if (tabItem) tabItem.classList.add('is-active');

        if (!clickedId) {
            console.warn('documents.js: no data-folder-id on clicked element');
            return;
        }

        showLoader();

        ajaxFetchDocuments(clickedId, false).then(function(json){
            safeInsertHtml(json, clickedId);
            pushFolderToUrl(clickedId);
        }).catch(function(err){
            console.error('documents.js fetch error', err);
            renderDocumentsHtml('<p>Ошибка загрузки документов</p>');
        });

    }, false);

    // ---------- Initial load logic ----------
    // Priority order to auto-load: URL ?folder_id -> data-selected (server) -> (first folder if ALLOW_DEFAULT true)
    document.addEventListener('DOMContentLoaded', function(){
        var fid = getFolderIdFromUrl();
        var serverSelected = getFolderIdFromDataSelected();

        // If URL param present, use it. Else if server provided, use that. Else if ALLOW_DEFAULT true, use first element.
        if (!fid && serverSelected) {
            fid = serverSelected;
        }

        if (!fid && !ALLOW_DEFAULT) {
            // Do not auto-load any folder. But ensure if server selected exists visually mark it (if provided)
            if (serverSelected) {
                var node = document.querySelector('#doc-folders .tab-btn[data-folder-id="'+serverSelected+'"]');
                if (node) {
                    clearActiveFolders();
                    node.classList.add('active-folder');
                    ensureParentOpen(node);
                }
                // load serverSelected docs (we allow serverSelected to load even if ALLOW_DEFAULT false)
                showLoader();
                ajaxFetchDocuments(serverSelected, false).then(function(json){
                    safeInsertHtml(json, serverSelected);
                }).catch(function(err){
                    console.error('documents.js initial fetch error', err);
                });
            }
            // If neither URL nor serverSelected — do nothing (no default load)
            return;
        }

        // At this point fid is either from URL or serverSelected or null but ALLOW_DEFAULT true
        var useEl = null;
        if (fid) {
            useEl = document.querySelector('#doc-folders .tab-btn[data-folder-id="'+fid+'"]') || null;
            if (useEl) {
                clearActiveFolders();
                useEl.classList.add('active-folder');
                ensureParentOpen(useEl);
            }
        } else {
            // no fid but ALLOW_DEFAULT true: pick first tab-btn
            var firstEl = document.querySelector('#doc-folders .tab-btn');
            if (firstEl) {
                useEl = firstEl;
                clearActiveFolders();
                useEl.classList.add('active-folder');
                ensureParentOpen(useEl);
            }
        }

        if (useEl) {
            var fid2 = useEl.getAttribute('data-folder-id');
            if (fid2) {
                // show content pane and load docs if needed
                clearActiveTabItems();
                var tItem = document.querySelector('.tab-item');
                if (tItem) tItem.classList.add('is-active');

                var docsList = document.getElementById('documents-list');
                var hasContent = docsList && docsList.children && docsList.children.length > 0 && !/Документы не найдены/i.test(docsList.innerText);
                if (!hasContent) {
                    showLoader();
                    ajaxFetchDocuments(fid2, false).then(function(json){
                        safeInsertHtml(json, fid2);
                    }).catch(function(err){
                        console.error('documents.js initial fetch error', err);
                    });
                }
            }
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"'\/]/g, function (s) {
            var entityMap = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': '&quot;', "'": '&#39;', "/": '&#x2F;' };
            return entityMap[s];
        });
    }

})();
