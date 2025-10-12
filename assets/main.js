document.addEventListener('DOMContentLoaded', function () {

    if (document.querySelectorAll('[data-fancybox]').length) {
        Fancybox.bind('[data-fancybox]', {
        });
    }

    if(document.querySelector('.hero-swiper')){
        const heroSwiper = new Swiper('.hero-swiper', {
            slidesPerView: 1,
            loop: true,
            speed: 800,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            // autoplay: {
            //   delay: 5000,
            //   disableOnInteraction: false,
            // },
        });
    }

    if(document.querySelector('.news-swiper')){
        const newsSwiper = new Swiper('.news-swiper', {
            slidesPerView: 1,
            loop: true,
            speed: 800,
            spaceBetween: 10,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            // autoplay: {
            //   delay: 5000,
            //   disableOnInteraction: false,
            // },
        });
    }

    if(document.querySelectorAll('.content-swiper')){
        document.querySelectorAll('.content-swiper').forEach((swiperEl, index) => {
            new Swiper(swiperEl, {
                slidesPerView: 1,
                spaceBetween: 10,
                speed: 800,
                pagination: {
                    el: swiperEl.querySelector('.swiper-pagination'),
                    clickable: true,
                },
                breakpoints: {
                    992: {
                        slidesPerView: 2
                    }
                },
            });
        });
    }

    // document.querySelectorAll('.like-icon').forEach(icon => {
    //     icon.addEventListener('click', () => {
    //         icon.parentElement.classList.toggle('is-active');
    //     });
    // });


    const hamburger = document.querySelector(".hamburger");
    const main = document.querySelector("main");

    hamburger.addEventListener("click", () => {
        hamburger.classList.toggle("is-active");
        main.classList.toggle("min");
    });

    // document.querySelectorAll('select[data-sort]').forEach(select => {
    //     const run = () => {
    //         const selected = select.value;
    //         const targetId = select.dataset.sort;
    //         const container = document.querySelector(`[data-target="${targetId}"]`);
    //         if (!container) return;

    //         const items = Array.from(container.querySelectorAll('[data-year]'));

    //         if (selected === '1' || selected.toLowerCase() === 'all') {

    //             items.sort((a, b) => (parseInt(b.dataset.year) || 0) - (parseInt(a.dataset.year) || 0));
    //             items.forEach(it => {
    //                 it.hidden = false;
    //                 it.setAttribute('aria-hidden', 'false');
    //                 container.appendChild(it);
    //             });
    //         } else {

    //             items.forEach(it => {
    //                 const match = it.dataset.year === selected;
    //                 it.hidden = !match;
    //                 it.setAttribute('aria-hidden', String(!match));
    //             });

    //             const matches = items.filter(i => i.dataset.year === selected);
    //             matches.forEach(m => container.appendChild(m));
    //         }
    //     };

    //     select.addEventListener('change', run);
    //     run();
    // });

    // document.addEventListener("click", (e) => {
    //     const commentBtn = e.target.closest(".comment");
    //     if (!commentBtn) return;
    //
    //     const wrapper = commentBtn.closest(".item-actions");
    //     if (!wrapper) return;
    //
    //     const nextForm = wrapper.nextElementSibling;
    //     if (nextForm && nextForm.classList.contains("comment-add")) {
    //         nextForm.style.display = "flex";
    //         nextForm.scrollIntoView({ behavior: "smooth", block: "center" });
    //     }
    // });

    const heads = document.querySelectorAll(".tab-head");
    const items = document.querySelectorAll(".tab-wrap .tab-item");

    // faqat ichida .inner-menu bo‘lmagan (leaf) tab-btn lar
    const leafHeads = Array.from(heads).filter(head => {
        const btn = head.closest(".tab-btn");
        return btn && !btn.querySelector(":scope > .inner-menu");
    });

    const removeActiveRecursive = (btn) => {
        if (!btn) return;
        btn.classList.remove("is-active");
        btn.querySelectorAll(".tab-btn.is-active").forEach(child => child.classList.remove("is-active"));
    };

    const addActive = (btn) => {
        if (!btn) return;
        btn.classList.add("is-active");

        const parent = btn.parentElement;
        if (!parent) return;

        parent.querySelectorAll(":scope > .tab-btn").forEach(sib => {
            if (sib !== btn) removeActiveRecursive(sib);
        });
    };

    heads.forEach(head => {
        head.addEventListener("click", (e) => {
            e.preventDefault();
            const btn = head.closest(".tab-btn");
            if (!btn) return;

            const isNested = !!btn.parentElement.closest(".tab-btn");
            const isLeaf = !btn.querySelector(":scope > .inner-menu");

            if (isNested) {
                if (!btn.classList.contains("is-active")) {
                    addActive(btn);

                    // faqat leaf bo‘lsa → tab-item bilan bog‘lash
                    if (isLeaf && leafHeads.includes(head)) {
                        const index = leafHeads.indexOf(head);
                        if (items[index]) {
                            items.forEach(it => it.classList.remove("is-active"));
                            items[index].classList.add("is-active");
                        }
                    }
                }
            } else {
                // top-level tab-btn toggle bo‘ladi
                if (btn.classList.contains("is-active")) {
                    removeActiveRecursive(btn);
                } else {
                    addActive(btn);
                }
            }
        });
    });

    document.querySelectorAll('.upload-block').forEach(block => {
        const fileInput = block.querySelector('input[type="file"]');
        const uploadBtn = block.querySelector('.upload-btn');
        const cancelBtn = block.querySelector('.cancel-btn');
        const desc = block.querySelector('.desc');

        const defaultText = desc.textContent;

        const toggleButtons = (enabled) => {
            if (enabled) {
                cancelBtn.removeAttribute('disabled');
            } else {
                cancelBtn.setAttribute('disabled', 'true');
            }
        };

        toggleButtons(false);

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                toggleButtons(true);
                desc.textContent = `Выбран файл: ${fileInput.files[0].name}`;
            } else {
                toggleButtons(false);
                desc.textContent = defaultText;
            }
        });

        cancelBtn.addEventListener('click', () => {
            fileInput.value = '';
            toggleButtons(false);
            desc.textContent = defaultText;
        });
    });

    const openBtns = document.querySelectorAll('.vacancy-main .btn-get');
    const modal = document.querySelector('.modal');
    const modalBack = document.querySelector('.modal-back');
    const closeBtns = document.querySelectorAll('.close-modal, .modal-back');

    openBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            modal.classList.add('is-active');
            modalBack.classList.add('is-active');
        });
    });

    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            modal.classList.remove('is-active');
            modalBack.classList.remove('is-active');
        });
    });

    document.querySelectorAll('.input-wrap').forEach(wrap => {
        const select = wrap.querySelector('select');
        const choise = wrap.querySelector('.choise');
        if (!select || !choise) return;

        const lis = Array.from(choise.querySelectorAll('li'));
        const options = Array.from(select.options);

        lis.forEach((li, i) => {
            if (options[i]) {
                li.dataset.value = options[i].value;
            }
        });

        lis.forEach(li => {
            li.addEventListener('click', (e) => {
                const value = li.dataset.value;
                const isCancel = e.target.closest('img');

                if (isCancel) {
                    li.classList.remove('selected');
                    select.value = "";
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    return;
                }

                lis.forEach(x => x.classList.remove('selected'));
                li.classList.add('selected');
                console.log(2);
                if (value !== undefined) {
                    select.value = value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });

        select.addEventListener('change', () => {
            const val = select.value;
            lis.forEach(li => {
                if (li.dataset.value === val) {
                    li.classList.add('selected');
                } else {
                    li.classList.remove('selected');
                }
            });
        });

        select.dispatchEvent(new Event('change'));
    });
      
    function checkWidth() {
        const main = document.querySelector('main');
        if (!main) return;
        console.log(window.innerWidth);
        if (window.innerWidth < 1200) {
            main.classList.add('min');
        } else {
            main.classList.remove('min');
        }
    }

    // sahifa yuklanganda tekshir
    checkWidth();

    // resize bo‘lganda ham tekshir
    window.addEventListener('resize', checkWidth);  

})


if (document.querySelectorAll('[data-fancybox]').length) {
    Fancybox.bind('[data-fancybox]', {
    });
}