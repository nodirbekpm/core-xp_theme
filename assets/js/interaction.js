(function () {
    'use strict';

    // ensure coreRest exists
    if ( typeof window.coreRest === 'undefined' ) {
        console.warn('coreRest is not defined. Make sure script is localized via wp_localize_script.');
        window.coreRest = { root: '/wp-json/', nonce: '', is_user_logged_in: 0, login_url: '/wp-login.php' };
    }

    const REST_ROOT = (typeof coreRest.root === 'string') ? coreRest.root : '/wp-json/';
    const NONCE = coreRest.nonce || '';
    const IS_LOGGED = parseInt(coreRest.is_user_logged_in || 0, 10) === 1;
    const LOGIN_URL = coreRest.login_url || '/wp-login.php';

    function findPostContext(el) {
        const container = el.closest('[data-post-id][data-post-type]');
        if (!container) return null;
        return {
            el: container,
            postId: container.getAttribute('data-post-id'),
            postType: container.getAttribute('data-post-type')
        };
    }

    function updateLikeUI(likeElem, liked, count) {
        const likeElems = document.querySelectorAll('.like');
        likeElems.forEach((Elem) => {
            if (liked) {
                Elem.classList.add('is-active');
                Elem.setAttribute('aria-pressed', 'true');
            } else {
                Elem.classList.remove('is-active');
                Elem.setAttribute('aria-pressed', 'false');
            }

            const cnt = Elem.querySelector('.count');
            if (cnt) cnt.textContent = count;
        });
    }

    async function toggleLikeRequest(postId, postType) {
        const url = REST_ROOT.replace(/\/$/, '') + '/core/v1/toggle-like';
        const body = { post_id: parseInt(postId, 10), post_type: postType };
        const res = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': NONCE
            },
            body: JSON.stringify(body)
        });
        const json = await res.json().catch(()=>({ error: 'invalid_json', status: res.status }));
        json._status = res.status;
        return json;
    }

    async function addCommentRequest(postId, content, parent = 0) {
        const url = REST_ROOT.replace(/\/$/, '') + '/core/v1/add-comment';
        const body = { post_id: parseInt(postId, 10), content: content, parent: parseInt(parent, 10) };
        const res = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': NONCE
            },
            body: JSON.stringify(body)
        });
        const json = await res.json().catch(()=>({ error: 'invalid_json', status: res.status }));
        json._status = res.status;
        return json;
    }

    // Click delegation
    document.addEventListener('click', function (e) {
        // LIKE
        const likeIcon = e.target.closest('.like-icon');
        if (likeIcon) {
            e.preventDefault();
            const likeWrapper = likeIcon.closest('.like');
            if (!likeWrapper) return;

            const ctx = findPostContext(likeWrapper);
            if (!ctx) {
                console.warn('No post context for like');
                return;
            }

            if (!IS_LOGGED) {
                // redirect to login
                window.location.href = LOGIN_URL;
                return;
            }

            likeWrapper.classList.add('is-loading');

            toggleLikeRequest(ctx.postId, ctx.postType)
                .then(data => {
                    if (data && !data.error) {
                        updateLikeUI(likeWrapper, !!data.liked, data.like_count);
                    } else {
                        // If unauthorized, redirect to login
                        if (data && (data._status === 401 || data._status === 403)) {
                            window.location.href = LOGIN_URL;
                            return;
                        }
                        console.error('Like error:', data);
                        // optional: show small toast
                    }
                })
                .catch(err => {
                    console.error('Network error (like):', err);
                })
                .finally(() => likeWrapper.classList.remove('is-loading'));

            return;
        }

        // Open comment form
        const commentBtn = e.target.closest('.comment');
        if (commentBtn) {
            e.preventDefault();

            if (!IS_LOGGED) {
                window.location.href = LOGIN_URL;
                return;
            }

            const wrapper = commentBtn.closest('.item-actions');
            if (!wrapper) return;

            let nextForm = wrapper.nextElementSibling;
            if (nextForm && nextForm.classList.contains('comment-add')) {
                nextForm.style.display = (nextForm.style.display === 'flex') ? 'none' : 'flex';
                if (nextForm.style.display === 'flex') {
                    const ta = nextForm.querySelector('textarea');
                    if (ta) ta.focus();
                }
            } else {
                const ctx = findPostContext(wrapper);
                if (!ctx) return;
                const form = ctx.el.querySelector('.comment-add');
                if (form) {
                    form.style.display = 'flex';
                    const ta = form.querySelector('textarea');
                    if (ta) ta.focus();
                }
            }
            return;
        }

        // Reply
        const replyBtn = e.target.closest('.reply');
        if (replyBtn) {
            e.preventDefault();

            if (!IS_LOGGED) {
                window.location.href = LOGIN_URL;
                return;
            }

            const commentItem = replyBtn.closest('.comment-item');
            const ctx = findPostContext(replyBtn);
            if (!ctx || !commentItem) return;
            const form = ctx.el.querySelector('.comment-add');
            if (!form) return;

            form.setAttribute('data-parent-id', commentItem.getAttribute('data-comment-id') || '0');
            form.style.display = 'flex';
            const ta = form.querySelector('textarea');
            if (ta) ta.focus();
            return;
        }
    }, false);

    // Submit comment
    document.addEventListener('submit', function (e) {
        const form = e.target.closest('.comment-add');
        if (!form) return;
        e.preventDefault();

        if (!IS_LOGGED) {
            window.location.href = LOGIN_URL;
            return;
        }

        const ctx = findPostContext(form);
        if (!ctx) {
            console.warn('No post context for comment form');
            return;
        }

        const textarea = form.querySelector('textarea');
        if (!textarea) return;
        const content = textarea.value.trim();
        if (!content) return;

        const parent = form.getAttribute('data-parent-id') || 0;
        const btn = form.querySelector('.btn-post');
        if (btn) btn.disabled = true;

        addCommentRequest(ctx.postId, content, parent)
            .then(json => {
                if (json && json.success) {
                    let commentsContainer = ctx.el.querySelector('.comments-list');
                    if (!commentsContainer) {
                        commentsContainer = document.createElement('div');
                        commentsContainer.className = 'comments-list';
                        form.parentNode.insertBefore(commentsContainer, form.nextSibling);
                    }

                    const temp = document.createElement('div');
                    temp.innerHTML = json.comment_html;
                    commentsContainer.insertBefore(temp.firstElementChild, commentsContainer.firstChild);

                    // update counts
                    const commentCountEls = ctx.el.querySelectorAll('.comment .count');
                    commentCountEls.forEach(el => el.textContent = json.comment_count);

                    textarea.value = '';
                    form.style.display = 'none';
                } else {
                    if (json && (json._status === 401 || json._status === 403)) {
                        window.location.href = LOGIN_URL;
                        return;
                    }
                    console.error('Comment error:', json);
                }
            })
            .catch(err => console.error('Network error (comment):', err))
            .finally(() => {
                if (btn) btn.disabled = false;
                form.removeAttribute('data-parent-id');
            });

    }, false);

})();
