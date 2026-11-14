(() => {
    function e(e, t) {
        (null == t || t > e.length) && (t = e.length);
        for (var r = 0, n = Array(t); r < t; r++) n[r] = e[r];
        return n;
    }
    var t, r, n, c, a, l, o, i, u, f;
    (t = window.wp),
        (r = t.blockEditor.InspectorControls),
        (n = t.components.PanelBody),
        (c = t.data.select),
        (a = t.element),
        (l = a.Fragment),
        (o = a.useState),
        (i = a.useEffect),
        (u = {}),
        (f = function (t) {
            var a,
                l,
                f =
                    ((a = o(!1)),
                    (l = 2),
                    (function (e) {
                        if (Array.isArray(e)) return e;
                    })(a) ||
                        (function (e, t) {
                            var r =
                                null == e
                                    ? null
                                    : ("undefined" != typeof Symbol && e[Symbol.iterator]) || e["@@iterator"];
                            if (null != r) {
                                var n,
                                    c,
                                    a,
                                    l,
                                    o = [],
                                    i = !0,
                                    u = !1;
                                try {
                                    if (((a = (r = r.call(e)).next), 0 === t)) {
                                        if (Object(r) !== r) return;
                                        i = !1;
                                    } else
                                        for (
                                            ;
                                            !(i = (n = a.call(r)).done) && (o.push(n.value), o.length !== t);
                                            i = !0
                                        );
                                } catch (e) {
                                    (u = !0), (c = e);
                                } finally {
                                    try {
                                        if (!i && null != r.return && ((l = r.return()), Object(l) !== l)) return;
                                    } finally {
                                        if (u) throw c;
                                    }
                                }
                                return o;
                            }
                        })(a, l) ||
                        (function (t, r) {
                            if (t) {
                                if ("string" == typeof t) return e(t, r);
                                var n = {}.toString.call(t).slice(8, -1);
                                return (
                                    "Object" === n && t.constructor && (n = t.constructor.name),
                                    "Map" === n || "Set" === n
                                        ? Array.from(t)
                                        : "Arguments" === n || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)
                                        ? e(t, r)
                                        : void 0
                                );
                            }
                        })(a, l) ||
                        (function () {
                            throw new TypeError(
                                "Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."
                            );
                        })()),
                s = f[0],
                m = f[1],
                h = c("core/block-editor").getSelectedBlock(),
                d = h && h.name.startsWith("acf/") ? h.name.replace("acf/", "") : "",
                p = "".concat(che_acf_block_preview_vars.acfBlockPath, "/").concat(d, "/preview.jpg");
            return (
                i(
                    function () {
                        d &&
                            (d in u
                                ? m(u[d])
                                : fetch(p)
                                      .then(function (e) {
                                          var t = e.ok;
                                          (u[d] = t), m(t);
                                      })
                                      .catch(function (e) {
                                          console.error("Error checking preview image:", e), (u[d] = !1), m(!1);
                                      }));
                    },
                    [p, d]
                ),
                d && !1 !== s
                    ? null === s
                        ? null
                        : React.createElement(
                              r,
                              null,
                              React.createElement(
                                  n,
                                  { title: "Preview Image", initialOpen: !0 },
                                  React.createElement("img", { src: p, alt: "Preview for the block" })
                              )
                          )
                    : null
            );
        }),
        t.hooks.addFilter("editor.BlockEdit", "acf-preview-image/with-inspector-controls", function (e) {
            return function (t) {
                return React.createElement(l, null, React.createElement(e, t), React.createElement(f, t));
            };
        });
})();
