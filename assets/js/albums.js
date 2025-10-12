(function(){
    'use strict';

    var ajaxEndpoint = window.coreAlbums && coreAlbums.ajaxurl ? coreAlbums.ajaxurl : (window.ajaxurl ? window.ajaxurl : (window.location.origin + '/wp-admin/admin-ajax.php'));
    var container = document.getElementById('album-single-container');
    var foldersWrap = document.getElementById('album-folders');

    // simple cache to avoid repeated server hits
    var folderCache = Object.create(null);

    function fetchSingleAlbum(postId) {
        if (!postId) return Promise.reject('no id');
        var fd = new FormData();
        fd.append('action', 'core_get_single_album_ajax');
        fd.append('post_id', postId);
        return fetch(ajaxEndpoint, { method: 'POST', credentials: 'same-origin', body: fd })
            .then(function(res){ if(!res.ok) throw new Error(res.status); return res.json(); })
            .then(function(json){
                if (json && json.success && json.data && typeof json.data.html !== 'undefined') return json.data.html;
                throw new Error('Invalid response');
            });
    }

    function fetchAlbumsInFolder(termId) {
        if (!termId) return Promise.reject('no term');
        // use cache key
        if (folderCache[termId]) {
            return Promise.resolve(folderCache[termId]);
        }
        var fd = new FormData();
        fd.append('action', 'core_get_albums_by_folder_ajax');
        fd.append('term_id', termId);
        return fetch(ajaxEndpoint, { method: 'POST', credentials: 'same-origin', body: fd })
            .then(function(res){ if(!res.ok) throw new Error(res.status); return res.json(); })
            .then(function(json){
                // normalize: ensure an object with html & count
                var payload = { html: '', count: 0, titles: [] };
                if ( json && json.success && json.data ) {
                    payload.html = (typeof json.data.html !== 'undefined') ? json.data.html : '';
                    payload.count = (typeof json.data.count !== 'undefined') ? json.data.count : 0;
                    payload.titles = (Array.isArray(json.data.titles)) ? json.data.titles : [];
                }
                // cache raw payload
                folderCache[termId] = payload;
                return payload;
            });
    }

    function renderHtmlToSingle(html) {
        if (!container) return;
        container.innerHTML = html || '';
        // re-init fancybox if markup contains fancybox links
        if (window.Fancybox && typeof window.Fancybox.bind === 'function') {
            try { Fancybox.bind('[data-fancybox]'); } catch(e) {}
        }
    }

    function ensureInnerMenu(tabBtn) {
        var inner = tabBtn.querySelector('.inner-menu');
        if (!inner) {
            inner = document.createElement('div');
            inner.className = 'inner-menu';
            tabBtn.appendChild(inner);
        }
        return inner;
    }

    function clearActiveFolders() {
        document.querySelectorAll('#album-folders .tab-btn').forEach(function(el){ el.classList.remove('active-folder'); });
    }

    function setInnerDirect(tabBtn, html) {
        // IMPORTANT: do not inject any "not found" text here.
        // If html is empty -> leave inner-menu empty.
        var inner = ensureInnerMenu(tabBtn);
        inner.innerHTML = (typeof html === 'string') ? html : '';
        // mark a data attribute if you want to style empty state via CSS:
        if (!inner.innerHTML || !inner.innerHTML.trim()) {
            inner.setAttribute('data-empty', '1');
        } else {
            inner.removeAttribute('data-empty');
        }
    }

    // Helper: add is-open to all ancestor .tab-btn's of a node
    function openParentChain(node) {
        if (!node) return;
        var cur = node.parentElement;
        while (cur) {
            if (cur.classList && cur.classList.contains('tab-btn')) {
                cur.classList.add('is-open');
                cur.classList.add('is-active');
                cur.classList.add('active-folder');
            }
            cur = cur.parentElement;
        }
    }

    // Helper: ensure content .tab-item visible
    function ensureContentTabActive() {
        var items = document.querySelectorAll('.tab-item');
        if (!items || items.length === 0) return;
        // if none has is-active, add to first
        var any = document.querySelector('.tab-item.is-active');
        if (!any) {
            items[0].classList.add('is-active');
        }
    }

    // Activate an album DOM node (add classes and open parents)
    function activateAlbumNode(albumNode) {
        if (!albumNode) return;
        clearActiveFolders();
        albumNode.classList.add('active-folder');
        albumNode.classList.add('is-active');
        // also mark its .tab-btn parent chain open (so inner-menu visible)
        openParentChain(albumNode);
        ensureContentTabActive();
    }

    // Try to find album node by id
    function findAlbumNodeById(albumId) {
        if (!albumId) return null;
        return document.querySelector('#album-folders .tab-btn.album-item[data-album-id="'+albumId+'"]');
    }

    // Try to find folder node by id
    function findFolderNodeById(folderId) {
        if (!folderId) return null;
        return document.querySelector('#album-folders .tab-btn[data-folder-id="'+folderId+'"]');
    }

    // click handler
    document.addEventListener('click', function(e){
        var head = e.target.closest('.tab-head');
        if (!head) return;
        e.preventDefault();

        var tabBtn = head.closest('.tab-btn');
        if (!tabBtn) return;

        // If user toggles folder that already has inner-menu present, toggle visual state.
        if ( tabBtn.querySelector('.inner-menu') ) {
            tabBtn.classList.toggle('is-open');
        }

        clearActiveFolders();
        tabBtn.classList.add('active-folder');

        // album or folder?
        var albumId = tabBtn.getAttribute('data-album-id');
        var folderId = tabBtn.getAttribute('data-folder-id');

        if ( albumId ) {
            // show single album on right
            fetchSingleAlbum(albumId).then(function(html){
                renderHtmlToSingle(html);
                // mark album node active and open parents
                activateAlbumNode(tabBtn);
                try { var u = new URL(window.location); u.searchParams.set('album_id', albumId); u.searchParams.delete('folder_id'); window.history.replaceState({}, '', u); } catch(e){}
            }).catch(function(err){
                console.error('fetchSingleAlbum error', err);
                if (container) container.innerHTML = '';
            });
            return;
        }

        if ( folderId ) {
            var inner = ensureInnerMenu(tabBtn);
            // only fetch if inner is empty and not previously loaded
            if ( !inner.innerHTML || !inner.innerHTML.trim() ) {
                inner.innerHTML = '<p class="albums-loading">Загрузка…</p>';
                fetchAlbumsInFolder(folderId).then(function(payload){
                    setInnerDirect(tabBtn, payload.html);
                }).catch(function(){ setInnerDirect(tabBtn, ''); });
            } else {
                // inner already has server-rendered children/albums — just toggle
                tabBtn.classList.toggle('is-open');
            }
            // show hint in right pane
            if (container) container.innerHTML = (window.coreAlbums && coreAlbums.selectText) ? coreAlbums.selectText : '<p>Выберите альбом</p>';
            try { var u2 = new URL(window.location); u2.searchParams.set('folder_id', folderId); u2.searchParams.delete('album_id'); window.history.replaceState({}, '', u2); } catch(e){}
            // ensure content visible
            ensureContentTabActive();
            return;
        }

    });

    // On load: honor URL params (folder_id or album_id) or server-provided data attributes.
    document.addEventListener('DOMContentLoaded', function(){
        try {
            var sp = new URL(window.location).searchParams;
            var album = sp.get('album_id');
            var folder = sp.get('folder_id');

            // fallback to server rendered dataset on #album-folders
            if ( !album && foldersWrap && foldersWrap.dataset && foldersWrap.dataset.selectedAlbum ) {
                album = foldersWrap.dataset.selectedAlbum || album;
            }
            if ( !folder && foldersWrap && foldersWrap.dataset && foldersWrap.dataset.selected ) {
                folder = foldersWrap.dataset.selected || folder;
            }

            // If album param present: ensure menu chain open and album active.
            if ( album ) {
                // try to find album node in DOM
                var albNode = findAlbumNodeById(album);
                if ( albNode ) {
                    // album item exists in DOM (server rendered or previously loaded)
                    activateAlbumNode(albNode);
                    // If container empty (server might have rendered or not), ensure content shown:
                    var hasContent = container && container.innerHTML && container.innerHTML.trim();
                    if (!hasContent) {
                        // fetch and render single album
                        fetchSingleAlbum(album).then(renderHtmlToSingle).catch(function(){ if(container) container.innerHTML = ''; });
                    } else {
                        // content probably already server-rendered; ensure visible
                        ensureContentTabActive();
                    }
                    return;
                }

                // album node not in DOM: try to open selected folder chain and fetch its albums
                if ( folder ) {
                    var fnode = findFolderNodeById(folder);
                    if ( fnode ) {
                        // open its ancestors
                        openParentChain(fnode);
                        // mark the folder active so visually selected
                        clearActiveFolders();
                        fnode.classList.add('active-folder');

                        // fetch albums into this folder (will include our album if assigned directly)
                        fetchAlbumsInFolder(folder).then(function(payload){
                            setInnerDirect(fnode, payload.html);
                            // after injection, try to find album node again
                            var albNode2 = findAlbumNodeById(album);
                            if ( albNode2 ) {
                                // activate and fetch single
                                activateAlbumNode(albNode2);
                                fetchSingleAlbum(album).then(renderHtmlToSingle).catch(function(){ if(container) container.innerHTML = ''; });
                            } else {
                                // fallback: still not found, just load single album into right pane and open folder container
                                if (container) {
                                    fetchSingleAlbum(album).then(renderHtmlToSingle).catch(function(){ container.innerHTML = ''; });
                                }
                            }
                        }).catch(function(err){
                            // fetch failed — still try to render single album so user sees content
                            console.error('fetchAlbumsInFolder initial error', err);
                            if (container) fetchSingleAlbum(album).then(renderHtmlToSingle).catch(function(){ container.innerHTML = ''; });
                        });
                        return;
                    }
                }

                // No folder info or folder node not present — still try render single album and make content visible
                fetchSingleAlbum(album).then(function(html){
                    renderHtmlToSingle(html);
                    ensureContentTabActive();
                }).catch(function(){ if(container) container.innerHTML = ''; });
                return;
            }

            // If folder param present but no album — open folder and render its inner (but keep right pane placeholder)
            if ( folder ) {
                var fnode2 = findFolderNodeById(folder);
                if ( fnode2 ) {
                    clearActiveFolders();
                    fnode2.classList.add('active-folder');
                    openParentChain(fnode2);
                    // fetch and inject albums into the folder's inner-menu
                    fetchAlbumsInFolder(folder).then(function(payload){
                        setInnerDirect(fnode2, payload.html);
                    }).catch(function(){ setInnerDirect(fnode2, ''); });
                    // show placeholder/hint
                    if (container) container.innerHTML = (window.coreAlbums && coreAlbums.selectText) ? coreAlbums.selectText : '<p>Выберите альбом</p>';
                    ensureContentTabActive();
                } else {
                    // folder not found: still show placeholder
                    if (container) container.innerHTML = (window.coreAlbums && coreAlbums.selectText) ? coreAlbums.selectText : '<p>Выберите папку или альбом слева</p>';
                }
                return;
            }

            // nothing in URL: leave right pane empty or with placeholder
            if (container) container.innerHTML = (window.coreAlbums && coreAlbums.selectText) ? coreAlbums.selectText : '<p>Выберите папку или альбом слева</p>';
        } catch(e){
            if (container) container.innerHTML = (window.coreAlbums && coreAlbums.selectText) ? coreAlbums.selectText : '<p>Выберите папку или альбом слева</p>';
            console.error('albums.js init error', e);
        }
    });

})();
