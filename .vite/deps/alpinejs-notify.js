import "./chunk-PZ5AY32C.js";

// node_modules/alpinejs-notify/dist/notifications.esm.js
function n(t, e) {
  setTimeout(function() {
    t.setAttribute("data-notify-show", false);
  }, e);
}
function a(t, e) {
  if (typeof e == "boolean") {
    t.addEventListener("animationend", function() {
      JSON.parse(t.getAttribute("data-notify-show")) === false && t.remove();
    });
    return;
  }
  setTimeout(function() {
    t.remove();
  }, e);
}
function m(t, e) {
  t.split(" ").forEach((r) => e.classList.add(r));
}
function i(t, e) {
  async function o() {
    let l = await (await fetch(e.templateFile)).text(), s = await new DOMParser().parseFromString(l.replace("{notificationText}", t), "text/html").body.firstChild;
    notificationWrapper.appendChild(s), s.setAttribute("data-notify-show", true), e.autoClose && n(s, e.autoClose), e.autoRemove && a(s, e.autoRemove), e.classNames && m(e.classNames, s);
  }
  o();
}
function p(t, e, o) {
  let r = document.getElementById(t), l = document.getElementById(e), s = new DOMParser().parseFromString(l.innerHTML.replace("{notificationText}", o), "text/html").body.firstChild;
  return r.appendChild(s), s.setAttribute("data-notify-show", true), s;
}
function f(t, e) {
  let o = p(e.wrapperId, e.templateId, t);
  e.autoClose && n(o, e.autoClose), e.autoRemove && a(o, e.autoRemove), e.classNames && m(e.classNames, o);
}
function u(t) {
  t.magic("notify", () => (e, o) => {
    let r = (o == null ? void 0 : o.wrapperId) ? o : window.notificationOptions;
    if (r.templateFile) return i(e, r);
    if (r.templateId) return f(e, r);
  });
}
var L = u;
export {
  L as default
};
//# sourceMappingURL=alpinejs-notify.js.map
