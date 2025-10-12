(function () {
    'use strict';

    // small helpers
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }
        // fallback for older browsers
        return new Promise(function (resolve, reject) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            try {
                document.execCommand('copy');
                document.body.removeChild(ta);
                resolve();
            } catch (err) {
                document.body.removeChild(ta);
                reject(err);
            }
        });
    }

    function notify(msg) {
        try {
            var t = document.createElement('div');
            t.textContent = msg;
            t.style.position = 'fixed';
            t.style.right = '16px';
            t.style.bottom = '16px';
            t.style.background = 'rgba(0,0,0,0.85)';
            t.style.color = '#fff';
            t.style.padding = '8px 12px';
            t.style.borderRadius = '6px';
            t.style.zIndex = 99999;
            t.style.fontSize = '14px';
            t.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)';
            document.body.appendChild(t);
            setTimeout(function () { t.remove(); }, 1600);
        } catch (e) {
            // last resort
            alert(msg);
        }
    }

    function handleShare(btn) {
        // btn can be <a> or button
        var url = btn.getAttribute('data-share-url') || btn.getAttribute('href') || window.location.href;
        var title = btn.getAttribute('data-share-title') || document.title;
        var text = btn.getAttribute('data-share-text') || '';

        if (navigator.share) {
            // use native share if available
            navigator.share({
                title: title,
                text: text ? (title + '\n\n' + text) : title,
                url: url
            }).catch(function (err) {
                // user canceled or failed: fallback to copy
                copyToClipboard(url).then(function () {
                    notify('Ссылка скопирована в буфер обмена');
                }).catch(function () {
                    notify('Не удалось скопировать ссылку');
                });
            });
            return;
        }

        // fallback: copy + notify
        copyToClipboard(url).then(function () {
            notify('Ссылка скопирована в буфер обмена');
        }).catch(function () {
            notify('Ваш браузер не поддерживает автоматическое копирование');
        });
    }

    // Delegated click listener (works for dynamically added elements)
    function delegatedClickHandler(e) {
        var btn = e.target.closest('.btn-share, .btn-share2, [data-share]');
        if (!btn) return;
        // if it's an <a>, prevent navigation if it is a JS share button
        if (btn.tagName.toLowerCase() === 'a') {
            // if it has a real href that should navigate, you may want to allow it.
            // Heuristic: if it has data-share-url or data-share-text or has class .btn-share -> prevent
            if (btn.classList.contains('btn-share') || btn.dataset.shareUrl || btn.dataset.shareText || btn.dataset.shareTitle) {
                e.preventDefault();
            }
        } else {
            e.preventDefault();
        }
        try {
            handleShare(btn);
        } catch (err) {
            console.error('simple-share: handleShare error', err);
        }
    }

    // MutationObserver (optional) — for debug/logging or to run custom per-node setup.
    function observeContainer(selector) {
        var container = document.querySelector(selector);
        if (!container || !window.MutationObserver) return null;
        var mo = new MutationObserver(function (mutations) {
            // If you want to do something with newly added share buttons, do it here.
            // But with delegation it's usually unnecessary.
            // Example: add a tiny attribute to mark processed nodes if needed.
            mutations.forEach(function (m) {
                if (!m.addedNodes || !m.addedNodes.length) return;
                m.addedNodes.forEach(function (node) {
                    if (node.nodeType !== 1) return;
                    if (node.matches && node.matches('.btn-share, .btn-share2, [data-share]')) {
                        // optional: mark node or do some visual tweak
                        node.setAttribute('data-share-observed', '1');
                    }
                    // also check children
                    node.querySelectorAll && node.querySelectorAll('.btn-share, .btn-share2, [data-share]').forEach(function (el) {
                        el.setAttribute('data-share-observed', '1');
                    });
                });
            });
        });
        mo.observe(container, { childList: true, subtree: true });
        return mo;
    }

    // public API
    var api = {
        start: function () {
            // attach delegated listener once
            if (!api._listening) {
                document.addEventListener('click', delegatedClickHandler, false);
                api._listening = true;
            }
        },
        stop: function () {
            if (api._listening) {
                document.removeEventListener('click', delegatedClickHandler, false);
                api._listening = false;
            }
        },
        observe: function (selector) {
            return observeContainer(selector);
        },
        // convenience: manually trigger share for element or URL
        trigger: function (elOrUrl) {
            if (!elOrUrl) return;
            if (typeof elOrUrl === 'string') {
                copyToClipboard(elOrUrl).then(function () { notify('Ссылка скопирована в буфер обмена'); });
                return;
            }
            handleShare(elOrUrl);
        }
    };

    // auto-start
    api.start();

    // expose to global so other scripts (ajax callbacks) can call if needed
    window.simpleShare = api;

})();
