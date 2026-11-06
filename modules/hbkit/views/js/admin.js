/* Hummingbird Theme Kit — back office behaviour. @author iamshoaibktk@gmail.com @license AFL-3.0 */
document.addEventListener('DOMContentLoaded', function () {
  'use strict';
  var root = document.querySelector('.hbbo');
  if (!root) return;
  var form = root.querySelector('.js-k-form');
  var dirty = false;

  /* ---------- helpers ---------- */
  function $(sel, el) { return (el || root).querySelector(sel); }
  function $$(sel, el) { return [].slice.call((el || root).querySelectorAll(sel)); }
  function markDirty() {
    if (!form) return;
    dirty = true;
    var s = $('.js-k-dirty'); if (s) s.hidden = false;
  }
  function toast(text, isError) {
    var t = $('.js-k-toast');
    if (!t) return;
    t.textContent = text; t.hidden = false;
    t.classList.toggle('is-error', !!isError);
    t.classList.add('is-visible');
    clearTimeout(t._t); t._t = setTimeout(function () { t.classList.remove('is-visible'); }, 2200);
  }
  function post(url, data) {
    var body = new URLSearchParams(data);
    return fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body }).then(function (r) { return r.json(); });
  }

  /* ---------- layers: panels, dialogs, menus ---------- */
  var shade = $('.js-k-shade');
  var lastFocus = null;
  function openPanel(n) {
    var p = $('.js-k-panel[data-n="' + n + '"]');
    if (!p) return;
    lastFocus = document.activeElement;
    p.hidden = false; if (shade) shade.hidden = false;
    var first = p.querySelector('input:not([type=hidden]):not([type=radio]), textarea, select');
    if (first) first.focus();
  }
  function closePanels() {
    $$('.js-k-panel').forEach(function (p) { p.hidden = true; });
    if (shade) shade.hidden = true;
    $$('[data-layer]').forEach(function (l) { l.hidden = true; });
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closePanels(); });
  root.addEventListener('click', function (e) {
    var t = e.target;
    var open = t.closest('.js-k-open');
    if (open) { lastFocus = open; $$('[data-layer="' + open.getAttribute('data-layer') + '"]').forEach(function (l) { l.hidden = false; }); var f = $('[data-layer="' + open.getAttribute('data-layer') + '"] input:not([type=hidden])'); if (f) f.focus(); return; }
    if (t.closest('.js-k-close') || t.closest('.js-k-panel-close') || t === shade) { closePanels(); return; }
    var menu = t.closest('.js-k-menu');
    $$('.k-menu__list').forEach(function (m) { if (!menu || m !== menu.nextElementSibling) { m.hidden = true; } });
    if (menu) { var list = menu.nextElementSibling; list.hidden = !list.hidden; menu.setAttribute('aria-expanded', list.hidden ? 'false' : 'true'); return; }
    var confirmLink = t.closest('.js-hbk-confirm');
    if (confirmLink && !window.confirm(confirmLink.getAttribute('data-confirm'))) { e.preventDefault(); }
  });
  if (shade) shade.addEventListener('click', closePanels);
  $$('.hbbo-layer-shade').forEach(function (s) { s.addEventListener('click', closePanels); });

  /* ---------- languages ---------- */
  function currentLang() { var b = $('.js-k-lang.is-on'); return b ? b.getAttribute('data-lang') : null; }
  function showLang(id) {
    $$('.hbk-l').forEach(function (el) { el.hidden = el.getAttribute('data-lang') !== String(id); });
    var on = $('.js-k-lang[data-lang="' + id + '"]');
    $$('.js-k-lang').forEach(function (b) { b.classList.toggle('is-on', b.getAttribute('data-lang') === String(id)); });
    if (on) {
      $$('.js-k-lang-tag').forEach(function (t) { t.textContent = on.getAttribute('data-iso'); });
      $$('.js-k-lang-name').forEach(function (t) { t.textContent = on.getAttribute('data-name'); });
    }
  }
  function markMissing() {
    var langs = $$('.k-langbar').length ? $$('.js-k-lang').map(function (b) { return b.getAttribute('data-lang'); }) : [];
    var base = langs.length ? langs[0] : null;
    var defaultBtn = $('.js-k-lang.is-on'); if (defaultBtn) base = base || defaultBtn.getAttribute('data-lang');
    var missing = {};
    var groups = {};
    $$('.hbk-l').forEach(function (el) { if (!el.name) return; var k = el.name.replace(/\[\d+\]$|_\d+$/, ''); (groups[k] = groups[k] || []).push(el); });
    Object.keys(groups).forEach(function (k) {
      var filled = groups[k].some(function (el) { return el.value.trim() !== ''; });
      if (!filled) return;
      groups[k].forEach(function (el) { if (el.value.trim() === '') missing[el.getAttribute('data-lang')] = true; });
    });
    $$('.js-k-lang').forEach(function (b) { b.classList.toggle('is-missing', !!missing[b.getAttribute('data-lang')]); });
  }
  root.addEventListener('click', function (e) {
    var b = e.target.closest('.js-k-lang');
    if (b) { showLang(b.getAttribute('data-lang')); return; }
    if (e.target.closest('.js-k-copy-lang')) {
      var from = currentLang();
      var groups = {};
      $$('.hbk-l').forEach(function (el) { if (!el.name) return; var k = el.name.replace(/\[\d+\]$|_\d+$/, ''); (groups[k] = groups[k] || []).push(el); });
      Object.keys(groups).forEach(function (k) {
        var src = groups[k].filter(function (el) { return el.getAttribute('data-lang') === from; })[0];
        if (!src || src.value.trim() === '') return;
        groups[k].forEach(function (el) { if (el !== src && el.value.trim() === '') { el.value = src.value; } });
      });
      markMissing(); markDirty();
    }
  });
  if ($('.js-k-lang')) { showLang(currentLang()); markMissing(); }

  /* ---------- dirty state ---------- */
  if (form) {
    form.addEventListener('input', function () { markDirty(); markMissing(); });
    form.addEventListener('change', markDirty);
    form.addEventListener('submit', function () { dirty = false; });
    window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = form.getAttribute('data-msg-unsaved'); } });
  }

  /* ---------- range outputs ---------- */
  root.addEventListener('input', function (e) {
    if (e.target.type === 'range') {
      var o = e.target.parentNode.querySelector('output');
      if (o) o.textContent = e.target.value + (o.textContent.match(/[^\d]+$/) || [''])[0];
    }
  });

  /* ---------- show_if fields and the split heading label ---------- */
  function applyShowIf(scope) {
    $$('[data-show-if]', scope).forEach(function (f) {
      var key = f.getAttribute('data-show-if');
      var holder = f.closest('.js-k-panel, form') || root;
      var ctl = holder.querySelector('[name$="[' + key + ']"]:checked, select[name$="[' + key + ']"]');
      f.hidden = !ctl || ctl.value !== f.getAttribute('data-show-value');
    });
    var align = $('input[name="settings[heading_align]"]:checked');
    var split = align && align.value === 'split';
    $$('.js-k-label[data-label-split]').forEach(function (l) { l.textContent = split ? l.getAttribute('data-label-split') : l.getAttribute('data-label'); });
    var hl = $('[data-key="head_link"]'); if (hl && align) hl.hidden = !split;
  }
  root.addEventListener('change', function () { applyShowIf(root); });
  applyShowIf(root);

  /* ---------- link picker ---------- */
  function initLink(p) {
    var hidden = p.querySelector('.js-k-link-value');
    var kind = p.querySelector('.js-k-link-kind');
    var parts = (hidden.value || '').split(':');
    kind.value = parts[0] || '';
    if (kind.selectedIndex < 0) kind.value = '';
    function sync(fromKind) {
      $$('.js-k-link-target', p).forEach(function (s) { s.hidden = s.getAttribute('data-kind') !== kind.value; });
      var t = p.querySelector('.js-k-link-target[data-kind="' + kind.value + '"]');
      if (t && !fromKind && parts[1] !== undefined && kind.value === parts[0]) t.value = parts[1];
      hidden.value = kind.value && t ? kind.value + ':' + t.value : '';
    }
    kind.addEventListener('change', function () { sync(true); });
    $$('.js-k-link-target', p).forEach(function (s) { s.addEventListener('change', function () { sync(true); }); });
    sync(false);
  }
  $$('.js-k-link').forEach(initLink);

  /* ---------- image drop zones ---------- */
  function initDrop(d) {
    var file = d.querySelector('.js-k-drop-file');
    var nameEl = d.querySelector('.js-k-drop-name');
    function show(f) {
      if (!f) return;
      nameEl.textContent = f.name;
      var r = new FileReader();
      r.onload = function () {
        var img = d.querySelector('.js-k-drop-img');
        if (img.tagName !== 'IMG') { var n = document.createElement('img'); n.className = 'js-k-drop-img'; img.replaceWith(n); img = n; }
        img.src = r.result;
        var row = d.closest('.js-k-panel');
        if (row) { var th = $('.js-k-item[data-n="' + row.getAttribute('data-n') + '"] .js-k-item-thumb'); if (th && th.tagName === 'IMG') th.src = r.result; }
      };
      r.readAsDataURL(f);
      d.classList.remove('is-removed');
      var rm = d.querySelector('.js-k-drop-removed'); if (rm) rm.value = '';
      markDirty();
    }
    file.addEventListener('change', function () { show(file.files[0]); });
    d.addEventListener('dragover', function (e) { e.preventDefault(); d.classList.add('is-over'); });
    d.addEventListener('dragleave', function () { d.classList.remove('is-over'); });
    d.addEventListener('drop', function (e) {
      e.preventDefault(); d.classList.remove('is-over');
      if (e.dataTransfer.files.length) { file.files = e.dataTransfer.files; show(file.files[0]); }
    });
    var rmBtn = d.querySelector('.js-k-drop-remove');
    if (rmBtn) rmBtn.addEventListener('click', function () {
      var rm = d.querySelector('.js-k-drop-removed');
      var on = !d.classList.contains('is-removed');
      d.classList.toggle('is-removed', on); rm.value = on ? '1' : '';
      rmBtn.textContent = on ? rmBtn.getAttribute('data-undo') || '↺' : rmBtn.getAttribute('data-label') || rmBtn.textContent;
      markDirty();
    });
  }
  $$('.js-k-drop').forEach(initDrop);

  /* ---------- items: edit, add, delete, reorder, live name ---------- */
  var list = $('.js-k-items');
  function renumber() {
    $$('.js-k-item', list).forEach(function (li, i) { var p = li.querySelector('.js-k-item-pos'); if (p) p.value = i + 1; });
  }
  if (list) {
    root.addEventListener('click', function (e) {
      var ed = e.target.closest('.js-k-item-edit');
      if (ed) { openPanel(ed.closest('.js-k-item').getAttribute('data-n')); return; }
      var del = e.target.closest('.js-k-item-delete');
      if (del) {
        var li = del.closest('.js-k-item');
        var flag = li.querySelector('.js-k-item-del');
        var on = !li.classList.contains('is-deleted');
        li.classList.toggle('is-deleted', on); flag.value = on ? '1' : '';
        del.querySelector('.material-icons').textContent = on ? 'undo' : 'delete_outline';
        del.title = on ? form.getAttribute('data-msg-delete') : del.getAttribute('aria-label');
        markDirty();
        return;
      }
      if (e.target.closest('.js-k-add-item')) {
        var key = 'new' + Date.now();
        var rowT = $('.js-k-item-template'), panT = $('.js-k-panel-template');
        var tmp = document.createElement('div');
        tmp.innerHTML = rowT.innerHTML.replace(/__N__/g, key);
        var row = tmp.firstElementChild;
        list.appendChild(row); list.hidden = false;
        tmp.innerHTML = panT.innerHTML.replace(/__N__/g, key);
        var panel = tmp.firstElementChild;
        $('.js-k-panels').appendChild(panel);
        $$('.js-k-link', panel).forEach(initLink);
        $$('.js-k-drop', panel).forEach(initDrop);
        renumber(); applyShowIf(panel);
        if ($('.js-k-lang')) showLang(currentLang());
        markDirty();
        openPanel(key);
      }
    });
    root.addEventListener('input', function (e) {
      if (!e.target.classList.contains('js-k-item-title')) return;
      var p = e.target.closest('.js-k-panel');
      if (!p || e.target.getAttribute('data-lang') !== (currentLang() || e.target.getAttribute('data-lang'))) return;
      var nm = $('.js-k-item[data-n="' + p.getAttribute('data-n') + '"] .js-k-item-name');
      if (nm && e.target.value.trim()) nm.textContent = e.target.value.trim();
    });
    root.addEventListener('change', function (e) {
      if (e.target.classList.contains('js-k-item-active')) e.target.closest('.js-k-item').classList.toggle('is-off', !e.target.checked);
    });
    sortable(list, '.js-k-item', function () { renumber(); markDirty(); });
  }

  /* ---------- sections list: drag order and switches save at once ---------- */
  var sections = $('.js-k-sections');
  if (sections) {
    var ajax = sections.getAttribute('data-ajax');
    sortable(sections, '.js-k-section', function () {
      var ids = $$('.js-k-section', sections).map(function (li) { return li.getAttribute('data-id'); }).join(',');
      post(ajax, { hbkit_ajax: 'order', ids: ids }).then(function (r) { toast(r.ok ? sections.getAttribute('data-saved') : sections.getAttribute('data-error'), !r.ok); }).catch(function () { toast(sections.getAttribute('data-error'), true); });
    });
    sections.addEventListener('change', function (e) {
      if (!e.target.classList.contains('js-k-section-toggle')) return;
      var li = e.target.closest('.js-k-section');
      li.classList.toggle('is-off', !e.target.checked);
      post(ajax, { hbkit_ajax: 'toggle', id_section: li.getAttribute('data-id'), active: e.target.checked ? 1 : 0 }).then(function (r) { toast(r.ok ? sections.getAttribute('data-saved') : sections.getAttribute('data-error'), !r.ok); }).catch(function () { toast(sections.getAttribute('data-error'), true); });
    });
  }

  /* ---------- drag to reorder (only from the grip) ---------- */
  function sortable(container, sel, done) {
    var dragging = null;
    container.addEventListener('pointerdown', function (e) { var g = e.target.closest('.k-grip'); if (g) { var li = g.closest(sel); if (li) li.setAttribute('draggable', 'true'); } });
    container.addEventListener('pointerup', function () { $$(sel, container).forEach(function (li) { li.removeAttribute('draggable'); }); });
    container.addEventListener('dragstart', function (e) { dragging = e.target.closest(sel); if (!dragging) return; dragging.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', ''); });
    container.addEventListener('dragend', function () { if (!dragging) return; dragging.classList.remove('is-dragging'); dragging.removeAttribute('draggable'); dragging = null; done(); });
    container.addEventListener('dragover', function (e) {
      if (!dragging) return;
      e.preventDefault();
      var over = e.target.closest(sel);
      if (!over || over === dragging) return;
      var r = over.getBoundingClientRect();
      container.insertBefore(dragging, (e.clientY - r.top) > r.height / 2 ? over.nextSibling : over);
    });
    // keyboard: Alt+Up / Alt+Down on a focused row moves it
    container.addEventListener('keydown', function (e) {
      if (!e.altKey || (e.key !== 'ArrowUp' && e.key !== 'ArrowDown')) return;
      var li = e.target.closest(sel); if (!li) return;
      e.preventDefault();
      if (e.key === 'ArrowUp' && li.previousElementSibling) container.insertBefore(li, li.previousElementSibling);
      if (e.key === 'ArrowDown' && li.nextElementSibling) container.insertBefore(li.nextElementSibling, li);
      e.target.focus(); done();
    });
  }

  /* ---------- colours: swatch and hex stay in step, preview updates ---------- */
  $$('.js-k-color').forEach(function (row) {
    var pick = row.querySelector('input[type=color]'), hex = row.querySelector('.k-inp');
    function apply(v) {
      $$('[data-pv="' + row.getAttribute('data-key') + '"]').forEach(function (el) { el.style.setProperty(el.getAttribute('data-pv-prop') || 'color', v); });
    }
    pick.addEventListener('input', function () { hex.value = pick.value.toUpperCase(); apply(pick.value); });
    hex.addEventListener('input', function () { if (/^#[0-9a-f]{6}$/i.test(hex.value)) { pick.value = hex.value; apply(hex.value); } });
  });

  /* ---------- top bar: one field per message ---------- */
  $$('.js-k-msgs').forEach(function (box) {
    function sync() {
      $$('.js-k-msg-list', box).forEach(function (ul) {
        var out = $$('.js-k-msg', ul).map(function (i) { return i.value.trim(); }).filter(Boolean).join(' | ');
        box.querySelector('.js-k-msg-value[data-lang="' + ul.getAttribute('data-lang') + '"]').value = out;
        if (ul.getAttribute('data-lang') === currentLang()) { var pv = $('.js-k-topbar-preview'); if (pv) pv.textContent = out.split(' | ')[0] || ''; }
      });
    }
    box.addEventListener('input', sync);
    box.addEventListener('click', function (e) {
      if (e.target.closest('.js-k-msg-add')) {
        var ul = box.querySelector('.js-k-msg-list:not([hidden])');
        var li = ul.querySelector('.js-k-msg-row').cloneNode(true);
        li.querySelector('.js-k-msg').value = ''; ul.appendChild(li); li.querySelector('.js-k-msg').focus(); sync();
      }
      if (e.target.closest('.js-k-msg-remove')) {
        var row = e.target.closest('.js-k-msg-row'), l = row.parentNode;
        if (l.children.length > 1) row.remove(); else row.querySelector('.js-k-msg').value = '';
        sync(); markDirty();
      }
    });
    $$('.js-k-msg-list', box).forEach(function (ul) { sortable(ul, '.js-k-msg-row', function () { sync(); markDirty(); }); });
    sync();
  });

  /* ---------- demo import: confirm dialog ---------- */
  $$('.js-k-import').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var d = $('.js-k-import-dialog');
      d.querySelector('.js-k-import-name').textContent = btn.getAttribute('data-name');
      d.querySelector('.js-k-import-name2').textContent = btn.getAttribute('data-name');
      d.querySelector('[name=pack]').value = btn.getAttribute('data-pack');
      d.querySelector('.js-k-import-what').textContent = btn.getAttribute('data-what');
      d.hidden = false; $('.js-k-import-shade').hidden = false;
      var c = d.querySelector('[name=confirm]'); c.value = ''; c.focus();
      d.querySelector('.js-k-import-go').disabled = true;
    });
  });
  var imp = $('.js-k-import-dialog');
  if (imp) imp.querySelector('[name=confirm]').addEventListener('input', function (e) { imp.querySelector('.js-k-import-go').disabled = e.target.value.trim().toUpperCase() !== 'IMPORT'; });
});
