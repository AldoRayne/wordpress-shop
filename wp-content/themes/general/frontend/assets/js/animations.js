/******/ (() => { // webpackBootstrap
/*!***********************************!*\
  !*** ./src/scripts/animations.js ***!
  \***********************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
window.addEventListener("DOMContentLoaded", function () {
  var _gsap;
  var root = document.documentElement;
  if (!root.classList.contains('has-anim')) {
    return;
  }
  if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
    root.classList.remove('has-anim');
    return;
  }
  var hasSplit = typeof SplitText !== 'undefined';
  (_gsap = gsap).registerPlugin.apply(_gsap, [ScrollTrigger].concat(_toConsumableArray(hasSplit ? [SplitText] : [])));
  window.animReady = true;
  var ease = 'power3.out';
  var start = 'top 85%';
  var all = function all(scope, selector) {
    return _toConsumableArray(scope.querySelectorAll(selector));
  };
  var rounded = function rounded(inset) {
    return "inset(".concat(inset, " round ").concat(rPx(20), "px)");
  };
  var fadeUp = function fadeUp(targets, trigger) {
    var vars = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
    return gsap.from(targets, _objectSpread({
      autoAlpha: 0,
      y: 40,
      duration: 0.9,
      ease: ease,
      stagger: 0.12,
      scrollTrigger: {
        trigger: trigger,
        start: start,
        once: true
      }
    }, vars));
  };
  var scrub = function scrub(target, trigger, from, to) {
    return gsap.fromTo(target, from, _objectSpread(_objectSpread({}, to), {}, {
      ease: 'none',
      scrollTrigger: {
        trigger: trigger,
        start: 'top bottom',
        end: 'bottom top',
        scrub: true
      }
    }));
  };
  var mm = gsap.matchMedia();
  mm.add({
    mobile: '(max-width: 767px)',
    desktop: '(min-width: 768px)'
  }, function (context) {
    var rich = context.conditions.desktop;
    all(document, '[data-anim="hero"]').forEach(function (hero) {
      var img = hero.querySelector('[data-anim-hero="img"]');
      var title = hero.querySelector('[data-anim-hero="title"]');
      var texts = all(hero, '[data-anim-hero="text"]');
      var content = hero.querySelector('[data-anim-hero="content"]');
      var intro = gsap.timeline({
        defaults: {
          ease: ease
        }
      });
      if (img) {
        intro.from(img, {
          scale: 1.1,
          duration: 1.6,
          ease: 'power2.out'
        }, 0);
      }
      if (title && rich && hasSplit) {
        gsap.set(title, {
          autoAlpha: 1
        });
        var split = SplitText.create(title, {
          type: 'words',
          mask: 'words'
        });
        intro.from(split.words, {
          yPercent: 110,
          duration: 1,
          stagger: 0.08,
          ease: 'power4.out'
        }, 0.3);
      } else if (title) {
        intro.from(title, {
          autoAlpha: 0,
          y: 40,
          duration: 0.9
        }, 0.3);
      }
      if (texts.length) {
        intro.from(texts, {
          autoAlpha: 0,
          y: 30,
          duration: 0.9,
          stagger: 0.15
        }, 0.6);
      }
      if (rich) {
        all(hero, '[data-anim-hero="media"]').forEach(function (media) {
          gsap.set(media, {
            scale: 1.12
          });
          gsap.fromTo(media, {
            yPercent: 0
          }, {
            yPercent: 8,
            ease: 'none',
            scrollTrigger: {
              trigger: hero,
              start: 'top top',
              end: 'bottom top',
              scrub: true
            }
          });
        });
        if (content) {
          gsap.to(content, {
            y: -80,
            opacity: 0,
            ease: 'none',
            scrollTrigger: {
              trigger: hero,
              start: 'top top',
              end: 'bottom 20%',
              scrub: true
            }
          });
        }
      }
    });
    all(document, '[data-anim="title"]').forEach(function (title) {
      if (rich && hasSplit) {
        SplitText.create(title, {
          type: 'lines',
          mask: 'lines',
          autoSplit: true,
          onSplit: function onSplit(self) {
            return gsap.from(self.lines, {
              yPercent: 110,
              duration: 1,
              stagger: 0.1,
              ease: 'power4.out',
              scrollTrigger: {
                trigger: title,
                start: start,
                once: true
              }
            });
          }
        });
        return;
      }
      fadeUp(title, title, {
        y: 30
      });
    });
    all(document, '[data-anim="cards"]').forEach(function (group) {
      var items = all(group, '[data-anim-item]');
      if (!items.length) {
        return;
      }
      gsap.set(items, {
        autoAlpha: 0,
        y: 40
      });
      ScrollTrigger.batch(items, {
        start: 'top 90%',
        once: true,
        onEnter: function onEnter(batch) {
          var tl = gsap.timeline();
          tl.to(batch, {
            autoAlpha: 1,
            y: 0,
            duration: 0.9,
            ease: ease,
            stagger: 0.12
          }, 0);
          if (!rich) {
            return;
          }
          batch.forEach(function (item, index) {
            var at = index * 0.12;
            var media = item.querySelector('[data-anim-media]');
            var pops = all(item, '[data-anim-pop]');
            if (media) {
              var type = media.getAttribute('data-anim-media');
              var img = media.querySelector('img');
              if (type.includes('clip')) {
                tl.fromTo(media, {
                  clipPath: rounded('0 100% 0 0')
                }, {
                  clipPath: rounded('0 0% 0 0'),
                  duration: 1.1,
                  ease: 'power4.inOut',
                  clearProps: 'clipPath'
                }, at);
              }
              if (type.includes('zoom') && img) {
                tl.from(img, {
                  scale: 1.18,
                  duration: 1.4,
                  ease: 'power2.out',
                  clearProps: 'transform'
                }, at);
              }
            }
            if (pops.length) {
              tl.from(pops, {
                scale: 0,
                rotate: -25,
                duration: 0.6,
                ease: 'back.out(2.5)',
                stagger: 0.07,
                clearProps: 'transform'
              }, at + 0.35);
            }
          });
        }
      });
    });
    all(document, '[data-anim="curtain"]').forEach(function (group) {
      var items = all(group, '[data-anim-item]');
      if (!rich) {
        fadeUp(items, group);
        return;
      }
      items.forEach(function (item, index) {
        var media = item.querySelector('[data-anim-media]');
        var text = item.querySelector('[data-anim-text]');
        var img = media && media.querySelector('img');
        var from = index % 2 ? '0 0 0 100%' : '0 100% 0 0';
        gsap.timeline({
          scrollTrigger: {
            trigger: group,
            start: start,
            once: true
          }
        }).fromTo(item, {
          clipPath: rounded(from)
        }, {
          clipPath: rounded('0 0% 0 0%'),
          duration: 1.3,
          ease: 'power4.inOut',
          clearProps: 'clipPath'
        }, index * 0.15).from(text, {
          autoAlpha: 0,
          y: 30,
          duration: 0.9,
          ease: ease
        }, index * 0.15 + 0.7);
        if (img) {
          scrub(img, item, {
            scale: 1.2,
            yPercent: -6
          }, {
            scale: 1.2,
            yPercent: 6
          });
        }
      });
    });
    all(document, '[data-anim="parallax"]').forEach(function (block) {
      var texts = all(block, '[data-anim-text]');
      if (!rich) {
        fadeUp(block, block);
        return;
      }
      var img = block.querySelector('[data-anim-media] img');
      if (img) {
        scrub(img, block, {
          scale: 1.25,
          yPercent: -10
        }, {
          scale: 1.25,
          yPercent: 10
        });
      }
      fadeUp(texts, block, {
        y: 30
      });
    });
    all(document, '[data-anim="drift"]').forEach(function (group) {
      var items = all(group, '[data-anim-item]');
      fadeUp(items, group, {
        stagger: 0.1
      });
      if (rich) {
        scrub(group, group, {
          x: rPx(40)
        }, {
          x: -rPx(40)
        });
      }
    });
  });
});
/******/ })()
;