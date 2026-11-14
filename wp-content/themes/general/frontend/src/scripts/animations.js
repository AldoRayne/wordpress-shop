window.addEventListener("DOMContentLoaded", () => {
    const root = document.documentElement;

    if (!root.classList.contains('has-anim')) {
        return;
    }

    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
        root.classList.remove('has-anim');
        return;
    }

    const hasSplit = typeof SplitText !== 'undefined';
    gsap.registerPlugin(ScrollTrigger, ...(hasSplit ? [SplitText] : []));
    window.animReady = true;

    const ease = 'power3.out';
    const start = 'top 85%';
    const all = (scope, selector) => [...scope.querySelectorAll(selector)];
    const rounded = (inset) => `inset(${inset} round ${rPx(20)}px)`;

    const fadeUp = (targets, trigger, vars = {}) => gsap.from(targets, {
        autoAlpha: 0,
        y: 40,
        duration: 0.9,
        ease,
        stagger: 0.12,
        scrollTrigger: { trigger, start, once: true },
        ...vars,
    });

    const scrub = (target, trigger, from, to) => gsap.fromTo(target, from, {
        ...to,
        ease: 'none',
        scrollTrigger: { trigger, start: 'top bottom', end: 'bottom top', scrub: true },
    });

    const mm = gsap.matchMedia();

    mm.add({
        mobile: '(max-width: 767px)',
        desktop: '(min-width: 768px)',
    }, (context) => {
        const rich = context.conditions.desktop;

        all(document, '[data-anim="hero"]').forEach((hero) => {
            const img = hero.querySelector('[data-anim-hero="img"]');
            const title = hero.querySelector('[data-anim-hero="title"]');
            const texts = all(hero, '[data-anim-hero="text"]');
            const content = hero.querySelector('[data-anim-hero="content"]');
            const intro = gsap.timeline({ defaults: { ease } });

            if (img) {
                intro.from(img, { scale: 1.1, duration: 1.6, ease: 'power2.out' }, 0);
            }

            if (title && rich && hasSplit) {
                gsap.set(title, { autoAlpha: 1 });
                const split = SplitText.create(title, { type: 'words', mask: 'words' });
                intro.from(split.words, { yPercent: 110, duration: 1, stagger: 0.08, ease: 'power4.out' }, 0.3);
            } else if (title) {
                intro.from(title, { autoAlpha: 0, y: 40, duration: 0.9 }, 0.3);
            }

            if (texts.length) {
                intro.from(texts, { autoAlpha: 0, y: 30, duration: 0.9, stagger: 0.15 }, 0.6);
            }

            if (rich) {
                all(hero, '[data-anim-hero="media"]').forEach((media) => {
                    gsap.set(media, { scale: 1.12 });
                    gsap.fromTo(media, { yPercent: 0 }, {
                        yPercent: 8,
                        ease: 'none',
                        scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true },
                    });
                });

                if (content) {
                    gsap.to(content, {
                        y: -80,
                        opacity: 0,
                        ease: 'none',
                        scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom 20%', scrub: true },
                    });
                }
            }
        });

        all(document, '[data-anim="title"]').forEach((title) => {
            if (rich && hasSplit) {
                SplitText.create(title, {
                    type: 'lines',
                    mask: 'lines',
                    autoSplit: true,
                    onSplit: (self) => gsap.from(self.lines, {
                        yPercent: 110,
                        duration: 1,
                        stagger: 0.1,
                        ease: 'power4.out',
                        scrollTrigger: { trigger: title, start, once: true },
                    }),
                });
                return;
            }

            fadeUp(title, title, { y: 30 });
        });

        all(document, '[data-anim="cards"]').forEach((group) => {
            const items = all(group, '[data-anim-item]');

            if (!items.length) {
                return;
            }

            gsap.set(items, { autoAlpha: 0, y: 40 });

            ScrollTrigger.batch(items, {
                start: 'top 90%',
                once: true,
                onEnter: (batch) => {
                    const tl = gsap.timeline();
                    tl.to(batch, { autoAlpha: 1, y: 0, duration: 0.9, ease, stagger: 0.12 }, 0);

                    if (!rich) {
                        return;
                    }

                    batch.forEach((item, index) => {
                        const at = index * 0.12;
                        const media = item.querySelector('[data-anim-media]');
                        const pops = all(item, '[data-anim-pop]');

                        if (media) {
                            const type = media.getAttribute('data-anim-media');
                            const img = media.querySelector('img');

                            if (type.includes('clip')) {
                                tl.fromTo(media, { clipPath: rounded('0 100% 0 0') }, {
                                    clipPath: rounded('0 0% 0 0'),
                                    duration: 1.1,
                                    ease: 'power4.inOut',
                                    clearProps: 'clipPath',
                                }, at);
                            }

                            if ((type.includes('zoom')) && img) {
                                tl.from(img, { scale: 1.18, duration: 1.4, ease: 'power2.out', clearProps: 'transform' }, at);
                            }
                        }

                        if (pops.length) {
                            tl.from(pops, {
                                scale: 0,
                                rotate: -25,
                                duration: 0.6,
                                ease: 'back.out(2.5)',
                                stagger: 0.07,
                                clearProps: 'transform',
                            }, at + 0.35);
                        }
                    });
                },
            });
        });

        all(document, '[data-anim="curtain"]').forEach((group) => {
            const items = all(group, '[data-anim-item]');

            if (!rich) {
                fadeUp(items, group);
                return;
            }

            items.forEach((item, index) => {
                const media = item.querySelector('[data-anim-media]');
                const text = item.querySelector('[data-anim-text]');
                const img = media && media.querySelector('img');
                const from = index % 2 ? '0 0 0 100%' : '0 100% 0 0';

                gsap.timeline({ scrollTrigger: { trigger: group, start, once: true } })
                    .fromTo(item, { clipPath: rounded(from) }, {
                        clipPath: rounded('0 0% 0 0%'),
                        duration: 1.3,
                        ease: 'power4.inOut',
                        clearProps: 'clipPath',
                    }, index * 0.15)
                    .from(text, { autoAlpha: 0, y: 30, duration: 0.9, ease }, index * 0.15 + 0.7);

                if (img) {
                    scrub(img, item, { scale: 1.2, yPercent: -6 }, { scale: 1.2, yPercent: 6 });
                }
            });
        });

        all(document, '[data-anim="parallax"]').forEach((block) => {
            const texts = all(block, '[data-anim-text]');

            if (!rich) {
                fadeUp(block, block);
                return;
            }

            const img = block.querySelector('[data-anim-media] img');

            if (img) {
                scrub(img, block, { scale: 1.25, yPercent: -10 }, { scale: 1.25, yPercent: 10 });
            }

            fadeUp(texts, block, { y: 30 });
        });

        all(document, '[data-anim="drift"]').forEach((group) => {
            const items = all(group, '[data-anim-item]');

            fadeUp(items, group, { stagger: 0.1 });

            if (rich) {
                scrub(group, group, { x: rPx(40) }, { x: -rPx(40) });
            }
        });
    });
});
