/**
 * Hummingbird Theme Kit — front behaviour: hero slider, product tabs, countdown, side cart.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 */
(function () {
  'use strict';

  function slider(root) {
    var slides = root.querySelectorAll('.hbk-hero__slide');
    var dots = root.querySelectorAll('.js-hbk-dot');
    if (slides.length < 2) return;
    var index = 0;
    var timer = null;
    var delay = (parseInt(root.getAttribute('data-autoplay'), 10) || 0) * 1000;
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function go(n) {
      index = (n + slides.length) % slides.length;
      slides.forEach(function (s, i) {
        s.classList.toggle('is-active', i === index);
        if (i === index) { s.removeAttribute('aria-hidden'); } else { s.setAttribute('aria-hidden', 'true'); }
      });
      dots.forEach(function (d, i) { d.classList.toggle('is-active', i === index); });
    }
    function play() {
      if (!delay || reduced) return;
      stop();
      timer = window.setInterval(function () { go(index + 1); }, delay);
    }
    function stop() { if (timer) { window.clearInterval(timer); timer = null; } }

    var prev = root.querySelector('.js-hbk-prev');
    var next = root.querySelector('.js-hbk-next');
    if (prev) prev.addEventListener('click', function () { go(index - 1); play(); });
    if (next) next.addEventListener('click', function () { go(index + 1); play(); });
    dots.forEach(function (d, i) { d.addEventListener('click', function () { go(i); play(); }); });
    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', play);
    root.addEventListener('focusin', stop);

    var startX = null;
    root.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    root.addEventListener('touchend', function (e) {
      if (startX === null) return;
      var dx = e.changedTouches[0].clientX - startX;
      if (Math.abs(dx) > 40) { go(index + (dx < 0 ? 1 : -1)); play(); }
      startX = null;
    });
    play();
  }

  function tabs(root) {
    var buttons = root.querySelectorAll('.js-hbk-tab');
    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        buttons.forEach(function (b) {
          var on = b === btn;
          b.classList.toggle('is-active', on);
          b.setAttribute('aria-selected', on ? 'true' : 'false');
          var panel = document.getElementById(b.getAttribute('aria-controls'));
          if (panel) { panel.hidden = !on; panel.classList.toggle('is-active', on); }
        });
      });
    });
  }

  function countdown(root) {
    var to = new Date(root.getAttribute('data-to').replace(' ', 'T')).getTime();
    if (isNaN(to)) return;
    var cells = {};
    root.querySelectorAll('[data-u]').forEach(function (el) { cells[el.getAttribute('data-u')] = el; });
    function tick() {
      var left = Math.max(0, Math.floor((to - Date.now()) / 1000));
      cells.d.textContent = Math.floor(left / 86400);
      cells.h.textContent = ('0' + Math.floor(left % 86400 / 3600)).slice(-2);
      cells.m.textContent = ('0' + Math.floor(left % 3600 / 60)).slice(-2);
      cells.s.textContent = ('0' + left % 60).slice(-2);
    }
    tick();
    window.setInterval(tick, 1000);
  }


  /* ---------------- Side cart, add-to-cart notice, product tabs ---------------- */
  var F = window.hbkitFront || {};

  function post(url, body) {
    return fetch(url, {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
      body: body || ''
    }).then(function (r) { return r.json(); });
  }

  var drawer = { el: null, last: null };
  function drawerLoad() {
    var body = drawer.el.querySelector('.js-hbk-drawer-body');
    drawer.el.classList.add('is-busy');
    return fetch(F.cart_url + (F.cart_url.indexOf('?') < 0 ? '?' : '&') + 'ajax=1&_=' + Date.now(), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (typeof d.html !== 'string') return;
        body.innerHTML = d.html;
        var c = drawer.el.querySelector('.js-hbk-drawer-count');
        if (c) c.textContent = d.count ? '(' + d.count + ')' : '';
      })
      .catch(function () {})
      .then(function () {
        drawer.el.classList.remove('is-busy');
        var loading = drawer.el.querySelector('.hbk-drawer__loading');
        if (loading) loading.remove();
        if (!body.children.length) body.innerHTML = '<div class="hbk-drawer__empty"><i class="material-icons" aria-hidden="true">shopping_bag</i><p class="hbk-drawer__empty-title">' + esc((F.labels || {}).empty) + '</p></div>';
      });
  }
  function drawerOpen() {
    if (!drawer.el) return false;
    drawer.last = document.activeElement;
    drawer.el.hidden = false;
    document.documentElement.classList.add('hbk-noscroll');
    window.requestAnimationFrame(function () { drawer.el.classList.add('is-open'); });
    drawer.el.querySelector('.hbk-drawer__panel').focus();
    drawerLoad();
    return true;
  }
  function drawerClose() {
    if (!drawer.el || drawer.el.hidden) return;
    drawer.el.classList.remove('is-open');
    document.documentElement.classList.remove('hbk-noscroll');
    window.setTimeout(function () { drawer.el.hidden = true; }, 300);
    if (drawer.last && drawer.last.focus) drawer.last.focus();
  }
  function drawerInit() {
    drawer.el = document.getElementById('hbk-cart-drawer');
    if (!drawer.el) return;
    drawer.el.addEventListener('click', function (e) {
      if (e.target.closest('.js-hbk-drawer-close')) { drawerClose(); return; }
      var btn = e.target.closest('.js-hbk-line');
      if (!btn) return;
      e.preventDefault();
      drawer.el.classList.add('is-busy');
      post(btn.getAttribute('data-remove') || btn.getAttribute('data-url'), 'ajax=1&action=update').then(function (resp) {
        if (window.prestashop && prestashop.emit) prestashop.emit('updateCart', { reason: { linkAction: 'hbk-drawer' }, resp: resp });
        return drawerLoad();
      }).catch(function () { drawer.el.classList.remove('is-busy'); });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !drawer.el.hidden) drawerClose();
    });
    // The header cart opens the side cart instead of the cart page.
    document.addEventListener('click', function (e) {
      var a = e.target.closest('.blockcart a, .blockcart .header-block__action-btn');
      if (!a || e.metaKey || e.ctrlKey || e.shiftKey) return;
      e.preventDefault();
      drawerOpen();
    }, true);
  }

  function toast(html) {
    var box = document.getElementById('hbk-cart-toast');
    if (!box) return false;
    var tmp = document.createElement('div');
    tmp.innerHTML = html;
    var img = tmp.querySelector('.blockcart-modal__image');
    var name = tmp.querySelector('.blockcart-modal__name');
    var title = tmp.querySelector('.blockcart-modal__title');
    var price = tmp.querySelector('.blockcart-modal__price');
    var meta = Array.prototype.map.call(tmp.querySelectorAll('.blockcart-modal__property'), function (el) { return el.textContent.replace(/\s+/g, ' ').trim(); });
    if (price && price.textContent.trim()) meta.push(price.textContent.trim());
    var L = F.labels || {};
    box.innerHTML = '<div class="hbk-toast__main">' + (img ? '<img class="hbk-toast__img" src="' + img.getAttribute('src') + '" alt="">' : '') +
      '<div class="hbk-toast__text"><strong><span class="hbk-toast__icon material-icons" aria-hidden="true">check_circle</span>' + esc(title ? title.textContent.replace(/^\W+/, '').trim() : (L.added || '')) + '</strong>' +
      '<span class="hbk-toast__name">' + esc(name ? name.textContent.trim() : '') + '</span>' +
      (meta.length ? '<span class="hbk-toast__meta">' + esc(meta.join(', ')) + '</span>' : '') + '</div></div>' +
      '<div class="hbk-toast__btns"><a class="btn btn-outline-primary hbk-toast__link" href="' + F.cart_page + '">' + esc(F.view_cart || '') + '</a>' +
      '<a class="btn btn-primary hbk-toast__go" href="' + (F.checkout_url || F.cart_page) + '">' + esc(L.checkout || '') + '</a></div>' +
      '<button type="button" class="hbk-toast__close" aria-label="' + (F.close || 'Close') + '"><i class="material-icons" aria-hidden="true">close</i></button>';
    box.hidden = false;
    box.classList.remove('is-open');
    void box.offsetWidth;
    box.classList.add('is-open');
    box.querySelector('.hbk-toast__close').onclick = function () { box.classList.remove('is-open'); };
    window.clearTimeout(box._t);
    box._t = window.setTimeout(function () { box.classList.remove('is-open'); }, 6000);
    return true;
  }

  // Static preview (GitHub Pages): show the side cart or notice with the product the visitor picked.
  function esc(t) { var d = document.createElement('div'); d.textContent = t || ''; return d.innerHTML; }
  window.hbkitDemo = function (p) {
    var L = F.labels || {};
    if (F.cart_style === 'toast') {
      return toast('<div class="blockcart-modal"><p class="blockcart-modal__title">' + esc(L.added) + '</p><img class="blockcart-modal__image" src="' + esc(p.img) + '"><div class="blockcart-modal__name">' + esc(p.name) + '</div></div>');
    }
    if (F.cart_style !== 'drawer' || !document.getElementById('hbk-cart-drawer')) return false;
    drawer.el = document.getElementById('hbk-cart-drawer');
    var body = drawer.el.querySelector('.js-hbk-drawer-body');
    drawer.el.hidden = false;
    document.documentElement.classList.add('hbk-noscroll');
    window.requestAnimationFrame(function () { drawer.el.classList.add('is-open'); });
    body.innerHTML = '<ul class="hbk-drawer__lines"><li class="hbk-line"><span class="hbk-line__img">' + (p.img ? '<img src="' + esc(p.img) + '" alt="">' : '') + '</span>' +
      '<div class="hbk-line__body"><span class="hbk-line__name">' + esc(p.name) + '</span><div class="hbk-line__row hbk-line__ctl"><div class="hbk-qty"><button type="button" disabled>&minus;</button><span>1</span><button type="button" disabled>+</button></div></div></div>' +
      '<span class="hbk-line__price">' + esc(p.price) + '</span></li></ul>' +
      '<footer class="hbk-drawer__foot"><div class="hbk-drawer__total"><span>' + esc(L.subtotal) + '</span><strong>' + esc(p.price) + '</strong></div><p class="hbk-drawer__note">' + esc(L.note) + '</p>' +
      '<a class="btn btn-primary hbk-drawer__btn" href="#live-only">' + esc(L.checkout) + '</a><a class="btn btn-outline-primary hbk-drawer__btn" href="#live-only">' + esc(F.view_cart) + '</a></footer>';
    var c = drawer.el.querySelector('.js-hbk-drawer-count'); if (c) c.textContent = '(1)';
    return true;
  };

  // Replace the add-to-cart pop-up of ps_shoppingcart, whatever order the scripts run in.
  function hookModal() {
    if (!window.prestashop || !F.cart_style || F.cart_style === 'modal') return;
    var bc = prestashop.blockcart = prestashop.blockcart || {};
    var original = bc.showModal;
    function show(html) {
      if (F.cart_style === 'drawer' && drawerOpen()) return;
      if (F.cart_style === 'toast' && toast(html)) return;
      if (F.cart_style === 'page' && F.cart_page) { window.location.href = F.cart_page; return; }
      if (original) original(html);
    }
    try {
      Object.defineProperty(bc, 'showModal', { configurable: true, enumerable: true, get: function () { return show; }, set: function (f) { original = f; } });
    } catch (e) { bc.showModal = show; }
  }
  hookModal();

  // Product page: turn the accordion into tabs.
  function productTabs() {
    var acc = document.getElementById('product_accordion');
    if (!acc || F.product_tabs !== 'tabs') return;
    var items = [].slice.call(acc.querySelectorAll(':scope > .accordion-item, :scope > * > .accordion-item'));
    if (items.length < 2) return;
    var list = document.createElement('div');
    list.className = 'hbk-ptabs';
    list.setAttribute('role', 'tablist');
    items.forEach(function (item, i) {
      var head = item.querySelector('.accordion-button');
      var panel = item.querySelector('.accordion-collapse');
      if (!head || !panel) return;
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'hbk-ptabs__tab';
      b.id = 'hbk-ptab-' + i;
      b.setAttribute('role', 'tab');
      b.setAttribute('aria-controls', panel.id);
      b.textContent = head.textContent.trim();
      panel.setAttribute('role', 'tabpanel');
      panel.setAttribute('aria-labelledby', b.id);
      b.addEventListener('click', function () { select(i); });
      list.appendChild(b);
    });
    function select(n) {
      items.forEach(function (item, i) {
        var on = i === n;
        var panel = item.querySelector('.accordion-collapse');
        item.classList.toggle('is-current', on);
        if (panel) panel.classList.toggle('show', on);
        var t = list.children[i];
        if (t) { t.classList.toggle('is-active', on); t.setAttribute('aria-selected', on ? 'true' : 'false'); t.tabIndex = on ? 0 : -1; }
      });
    }
    list.addEventListener('keydown', function (e) {
      var tabs = [].slice.call(list.children), i = tabs.indexOf(document.activeElement);
      if (i < 0 || (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft')) return;
      var n = (i + (e.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
      tabs[n].focus(); select(n);
    });
    acc.parentNode.insertBefore(list, acc);
    acc.classList.add('hbk-ptabs-panels');
    select(0);
  }

  // Category pages with the filter button: keep the filters in the slide-in panel on every screen size.
  function filterDrawer() {
    if (!document.body.classList.contains('hbk-listing-drawer')) return;
    function move() {
      var from = document.getElementById('_desktop_faceted');
      var to = document.getElementById('_mobile_faceted');
      if (!from || !to) return;
      while (from.firstChild) to.appendChild(from.firstChild);
    }
    move();
    var t;
    window.addEventListener('resize', function () { window.clearTimeout(t); t = window.setTimeout(move, 150); });
    if (window.prestashop && prestashop.on) prestashop.on('updateProductList', function () { window.setTimeout(move, 0); });
  }

  // Search panel: opens from the header "Search", shows popular links, then live results.
  function searchPanel() {
    var el = document.getElementById('hbk-search');
    if (!el || !F.search) return;
    var input = el.querySelector('.js-hbk-search-input');
    var idle = el.querySelector('.js-hbk-search-idle');
    var out = el.querySelector('.js-hbk-search-results');
    var last = null, timer = null, opener = null;
    function open(e) {
      if (e) e.preventDefault();
      opener = document.activeElement;
      el.hidden = false;
      document.documentElement.classList.add('hbk-noscroll');
      window.requestAnimationFrame(function () { el.classList.add('is-open'); input.focus(); });
    }
    function close() {
      el.classList.remove('is-open');
      document.documentElement.classList.remove('hbk-noscroll');
      window.setTimeout(function () { el.hidden = true; }, 250);
      if (opener && opener.focus) opener.focus();
    }
    function run() {
      var q = input.value.trim();
      if (q.length < 2) { out.hidden = true; idle.hidden = false; last = null; return; }
      if (q === last) return;
      last = q;
      fetch(F.search.url + (F.search.url.indexOf('?') < 0 ? '?' : '&') + 's=' + encodeURIComponent(q), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (d) { if (q !== last || typeof d.html !== 'string') return; out.innerHTML = d.html; out.hidden = false; idle.hidden = true; })
        .catch(function () {});
    }
    document.addEventListener('click', function (e) {
      if (e.target.closest('.js-hbk-search-open')) { open(e); return; }
      if (e.target.closest('.js-hbk-search-close')) close();
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !el.hidden) close(); });
    input.addEventListener('input', function () { window.clearTimeout(timer); timer = window.setTimeout(run, 220); });
  }

  // Add to cart, everywhere: the button shows "Adding…" then "Added", the photo flies to the cart and the
  // cart count bumps. On product cards a product with sizes first shows its sizes over the photo (quick add);
  // choosing one adds it by AJAX. Nothing moves for visitors who ask for reduced motion.
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var pending = null;
  function cartTarget() {
    var list = document.querySelectorAll('#_desktop_ps_shoppingcart .header-block__action-btn, #_mobile_ps_shoppingcart .header-block__action-btn, .blockcart');
    for (var i = 0; i < list.length; i++) { var r = list[i].getBoundingClientRect(); if (r.width && r.bottom > 0) return list[i]; }
    return null;
  }
  function fly(img) {
    var to = cartTarget();
    if (reduceMotion || !img || !to || !img.getBoundingClientRect || !document.body.animate) return;
    var a = img.getBoundingClientRect(), b = to.getBoundingClientRect();
    if (!a.width || a.bottom < 0 || a.top > window.innerHeight) return;
    var c = document.createElement('img');
    c.src = img.currentSrc || img.src;
    c.alt = '';
    c.className = 'hbk-fly';
    c.style.cssText = 'left:' + a.left + 'px;top:' + a.top + 'px;width:' + a.width + 'px;height:' + a.height + 'px';
    document.body.appendChild(c);
    var dx = b.left + b.width / 2 - (a.left + a.width / 2), dy = b.top + b.height / 2 - (a.top + a.height / 2), s = Math.max(.08, 40 / a.width);
    var anim = c.animate([
      { transform: 'translate(0,0) scale(1)', opacity: 1 },
      { transform: 'translate(' + dx * .55 + 'px,' + (dy * .55 - 60) + 'px) scale(' + (s * 2.5) + ')', opacity: .95, offset: .6 },
      { transform: 'translate(' + dx + 'px,' + dy + 'px) scale(' + s + ')', opacity: .2 }
    ], { duration: 700, easing: 'cubic-bezier(.5,0,.2,1)' });
    anim.onfinish = function () { c.remove(); };
  }
  function bump() {
    if (reduceMotion) return;
    document.querySelectorAll('.header-block__badge, .blockcart .cart-products-count').forEach(function (el) {
      el.classList.remove('hbk-bump'); void el.offsetWidth; el.classList.add('hbk-bump');
    });
  }
  function setState(btn, state) {
    if (!btn) return;
    btn.classList.remove('is-loading', 'is-added', 'is-error');
    if (state) btn.classList.add('is-' + state);
    btn.setAttribute('aria-busy', state === 'loading' ? 'true' : 'false');
    window.clearTimeout(btn._hbkT);
    if (state === 'added' || state === 'error') btn._hbkT = window.setTimeout(function () { setState(btn, null); }, 1800);
  }
  function photoFor(btn) {
    var card = btn.closest('.product-miniature');
    if (card) return card.querySelector('.product-miniature__image, .product-miniature__top img');
    var scope = btn.closest('.quickview, .product__container, .js-product-container, #main') || document;
    return scope.querySelector('.product__left .carousel-item.active img, .product__left img, .product-cover img');
  }
  function addFeedback() {
    if (!window.prestashop || !prestashop.on) return;
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-button-action="add-to-cart"]');
      if (!btn || btn.disabled || btn.getAttribute('aria-disabled') === 'true') return;
      pending = { btn: btn, img: photoFor(btn) };
      setState(btn, 'loading');
    }, true); // capture: before Hummingbird disables the button
    prestashop.on('updateCart', function (ev) {
      if (!pending || !ev || !ev.reason || ev.reason.linkAction !== 'add-to-cart') return;
      var p = pending; pending = null;
      setState(p.btn, 'added');
      // The side cart opens by itself: no photo flying to the header then.
      if (F.drawer) { if (p.done) p.done(); return; }
      // A header that slid away while scrolling comes back so the photo can fly to the cart.
      var h = document.getElementById('header'), away = h && h.classList.contains('hbk-header-away');
      if (away) h.classList.remove('hbk-header-away');
      window.setTimeout(function () { fly(p.img); window.setTimeout(bump, reduceMotion ? 0 : 650); }, away && !reduceMotion ? 280 : 0);
      if (p.done) p.done();
    });
    prestashop.on('handleError', function (ev) {
      if (!pending || (ev && ev.eventType && ev.eventType !== 'addProductToCart')) return;
      setState(pending.btn, 'error'); pending = null;
    });
  }

  function quickAdd() {
    if (!F.options_url) return;
    var cache = {};
    function close(card) { var q = card.querySelector('.hbk-qa'); if (q) q.remove(); card.classList.remove('has-qa'); }
    var loading = {};
    function load(id) {
      if (cache[id]) return Promise.resolve(cache[id]);
      if (!loading[id]) {
        loading[id] = fetch(F.options_url + (F.options_url.indexOf('?') < 0 ? '?' : '&') + 'id_product=' + encodeURIComponent(id), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
          .then(function (r) { return r.json(); })
          .then(function (d) { cache[id] = d; return d; })
          .finally(function () { delete loading[id]; });
      }
      return loading[id];
    }
    // Load a card's sizes when the pointer or keyboard reaches its button, so the panel opens at once.
    function prefetch(e) {
      var btn = e.target.closest && e.target.closest('.product-miniature [data-button-action="add-to-cart"]');
      var card = btn && btn.closest('.product-miniature');
      var ipa = card && card.getAttribute('data-id-product-attribute');
      if (ipa && ipa !== '0') load(card.getAttribute('data-id-product')).catch(function () {});
    }
    document.addEventListener('pointerover', prefetch);
    document.addEventListener('focusin', prefetch);
    function go(btn, ipa) {
      var form = btn.closest('form');
      var input = form && form.querySelector('input[name="id_product_attribute"]');
      if (input) input.value = ipa;
      btn._hbkGo = true;
      btn.click();
    }
    function panel(card, btn, group) {
      // One size panel at a time.
      document.querySelectorAll('.product-miniature.has-qa').forEach(close);
      var L = F.labels || {};
      var box = document.createElement('div');
      box.className = 'hbk-qa';
      box.setAttribute('role', 'group');
      box.setAttribute('aria-label', group.name);
      var html = '<div class="hbk-qa__head"><span class="hbk-qa__title">' + esc(group.name) + '</span><button type="button" class="hbk-qa__close" aria-label="' + esc(F.close || 'Close') + '">&times;</button></div><div class="hbk-qa__opts">';
      group.values.forEach(function (v) {
        html += '<button type="button" class="hbk-qa__opt" data-ipa="' + v.ipa + '"' + (v.available ? '' : ' disabled aria-disabled="true" title="' + esc(L.soldout || '') + '"') + '>' + esc(v.label) + '</button>';
      });
      box.innerHTML = html + '</div>';
      (card.querySelector('.product-miniature__top') || card).appendChild(box);
      card.classList.add('has-qa');
      var first = box.querySelector('.hbk-qa__opt:not([disabled])');
      if (first) first.focus();
      box.addEventListener('click', function (e) {
        if (e.target.closest('.hbk-qa__close')) { close(card); btn.focus(); return; }
        var o = e.target.closest('.hbk-qa__opt');
        if (!o || o.disabled) return;
        box.querySelectorAll('.hbk-qa__opt').forEach(function (x) { x.classList.toggle('is-picked', x === o); });
        box.classList.add('is-busy');
        go(btn, o.getAttribute('data-ipa'));
        pending && (pending.done = function () { window.setTimeout(function () { close(card); }, 700); });
      });
      box.addEventListener('keydown', function (e) { if (e.key === 'Escape') { close(card); btn.focus(); } });
    }
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.product-miniature [data-button-action="add-to-cart"]');
      if (!btn) return;
      if (btn._hbkGo) { btn._hbkGo = false; return; }
      var card = btn.closest('.product-miniature');
      if (!card || !card.getAttribute('data-id-product-attribute') || card.getAttribute('data-id-product-attribute') === '0') return;
      e.preventDefault();
      e.stopImmediatePropagation();
      if (card.classList.contains('has-qa')) { close(card); return; }
      // One size panel at a time, closed at once (not when the next one has loaded).
      document.querySelectorAll('.product-miniature.has-qa').forEach(close);
      var id = card.getAttribute('data-id-product');
      var use = function (d) {
        if (!d || !d.ok) { window.location.href = (card.querySelector('a') || {}).href || '#'; return; }
        if (d.group && d.group.values.length > 1) panel(card, btn, d.group);
        else go(btn, d.group && d.group.values[0] ? d.group.values[0].ipa : d.default);
      };
      if (cache[id]) { use(cache[id]); return; }
      // Usually already loaded on hover; show the spinner only when the wait is noticeable.
      var slow = window.setTimeout(function () { setState(btn, 'loading'); }, 150);
      load(id)
        .then(function (d) { window.clearTimeout(slow); setState(btn, null); use(d); })
        .catch(function () { window.clearTimeout(slow); setState(btn, null); window.location.href = (card.querySelector('a') || {}).href || '#'; });
    }, true);
    document.addEventListener('click', function (e) {
      document.querySelectorAll('.product-miniature.has-qa').forEach(function (card) { if (!card.contains(e.target)) close(card); });
    });
  }

  // Header while scrolling = "up": the header slides away while reading down and comes back on scroll up.
  function headerScroll() {
    var h = document.getElementById('header');
    if (!h || !document.body.classList.contains('hbk-hsticky-up')) return;
    var last = window.pageYOffset, ticking = false;
    function busy() { return h.contains(document.activeElement) || document.querySelector('.hbk-sfield.is-open, .offcanvas.show, .dropdown-menu.show'); }
    function update() {
      ticking = false;
      var y = window.pageYOffset, dy = y - last;
      if (Math.abs(dy) < 6) return;
      if (y > h.offsetHeight && dy > 0 && !busy()) h.classList.add('hbk-header-away');
      else if (dy < 0 || y <= h.offsetHeight) h.classList.remove('hbk-header-away');
      last = y;
    }
    window.addEventListener('scroll', function () { if (!ticking) { ticking = true; window.requestAnimationFrame(update); } }, { passive: true });
    h.addEventListener('focusin', function () { h.classList.remove('hbk-header-away'); });
  }

  // Header search field (search style "field"): the first results in a list under the field.
  function searchField() {
    var form = document.querySelector('.js-hbk-sfield');
    if (!form || !F.search) return;
    var wrap = form.parentNode;
    var input = form.querySelector('.js-hbk-sfield-input');
    var drop = form.querySelector('.js-hbk-sfield-drop');
    var last = null, timer = null;
    function show(on) { drop.hidden = !on; input.setAttribute('aria-expanded', on ? 'true' : 'false'); }
    function run() {
      var q = input.value.trim();
      if (q.length < 2) { show(false); last = null; return; }
      if (q === last) { show(true); return; }
      last = q;
      fetch(F.search.url + (F.search.url.indexOf('?') < 0 ? '?' : '&') + 's=' + encodeURIComponent(q), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (d) { if (q !== last || typeof d.html !== 'string') return; drop.innerHTML = d.html; show(true); })
        .catch(function () {});
    }
    input.addEventListener('input', function () { window.clearTimeout(timer); timer = window.setTimeout(run, 220); });
    input.addEventListener('focus', function () { if (input.value.trim().length >= 2) run(); });
    input.addEventListener('keydown', function (e) {
      var links = Array.prototype.slice.call(drop.querySelectorAll('a'));
      if (e.key === 'Escape') { show(false); return; }
      if (e.key === 'ArrowDown' && links.length && !drop.hidden) { e.preventDefault(); links[0].focus(); }
    });
    drop.addEventListener('keydown', function (e) {
      var links = Array.prototype.slice.call(drop.querySelectorAll('a'));
      var i = links.indexOf(document.activeElement);
      if (e.key === 'ArrowDown' && i < links.length - 1) { e.preventDefault(); links[i + 1].focus(); }
      if (e.key === 'ArrowUp') { e.preventDefault(); (i > 0 ? links[i - 1] : input).focus(); }
      if (e.key === 'Escape') { show(false); input.focus(); }
    });
    document.addEventListener('click', function (e) {
      var open = e.target.closest('.hbk-sfield__open');
      if (open && wrap.contains(open)) { e.preventDefault(); wrap.classList.toggle('is-open'); if (wrap.classList.contains('is-open')) input.focus(); return; }
      if (!form.contains(e.target)) show(false);
    });
  }

  // Product page: "Size guide" next to the size label.
  function sizeGuide() {
    if (!F.size_guide || !F.size_name) return;
    function add() {
      document.querySelectorAll('.product-variant__legend').forEach(function (lg) {
        if (lg.textContent.trim() !== F.size_name || lg.parentNode.querySelector('.hbk-sizeguide')) return;
        var a = document.createElement('a');
        a.className = 'hbk-sizeguide';
        a.href = F.size_guide.url;
        a.textContent = F.size_guide.label;
        lg.parentNode.insertBefore(a, lg.nextSibling);
        if (F.size_hint) {
          var p = document.createElement('p');
          p.className = 'hbk-sizehint';
          p.textContent = F.size_hint;
          (lg.closest('.product-variant') || lg.parentNode).appendChild(p);
        }
      });
    }
    add();
    if (window.prestashop && prestashop.on) prestashop.on('updatedProduct', function () { window.setTimeout(add, 0); });
    // Quick view opens in a modal after the page has loaded.
    document.addEventListener('shown.bs.modal', function () { window.setTimeout(add, 0); });
  }

  // Category pages: one filter group (for example Size) as quick buttons in the toolbar.
  function quickFacet() {
    var box = document.querySelector('.js-hbk-chips');
    if (!box || !F.quick_facet) return;
    function build() {
      box = document.querySelector('.js-hbk-chips');
      if (!box) return;
      var sec = null;
      document.querySelectorAll('section[data-type="' + F.quick_facet.type + '"]').forEach(function (s) { if (!sec && s.getAttribute('data-name') === F.quick_facet.name) sec = s; });
      box.innerHTML = '';
      if (!sec) return;
      box.setAttribute('aria-label', F.quick_facet.name);
      var order = F.quick_facet.order || [];
      var inputs = Array.prototype.slice.call(sec.querySelectorAll('input[data-search-url]'));
      function nameOf(inp) {
        var l = sec.querySelector('label[for="' + inp.id + '"] a, label[for="' + inp.id + '"]');
        var c = l ? l.cloneNode(true) : null;
        if (c) c.querySelectorAll('.search-filters__magnitude, .magnitude').forEach(function (m) { m.remove(); });
        return c ? c.textContent.trim() : '';
      }
      if (order.length) inputs.sort(function (a, b) { var x = order.indexOf(nameOf(a)), y = order.indexOf(nameOf(b)); return (x < 0 ? 999 : x) - (y < 0 ? 999 : y); });
      // Same order in the filter list itself.
      if (order.length) inputs.forEach(function (inp) { var li = inp.closest('li'); if (li && li.parentNode) li.parentNode.appendChild(li); });
      inputs.forEach(function (inp) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'hbk-chip' + (inp.checked ? ' is-on' : '');
        b.setAttribute('aria-pressed', inp.checked ? 'true' : 'false');
        b.textContent = nameOf(inp);
        b.addEventListener('click', function () { prestashop.emit('updateFacets', inp.getAttribute('data-search-url')); });
        box.appendChild(b);
      });
    }
    build();
    if (window.prestashop && prestashop.on) prestashop.on('updateProductList', function () { window.setTimeout(build, 50); });
  }

  // Cart page: keep the free delivery bar in step with the cart.
  function cartBar() {
    var bar = document.querySelector('.js-hbk-cartbar');
    if (!bar || !window.prestashop || !prestashop.on) return;
    prestashop.on('updatedCart', function () {
      fetch(F.cart_url + (F.cart_url.indexOf('?') < 0 ? '?' : '&') + 'ajax=1&_=' + Date.now(), { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          var t = document.createElement('div'); t.innerHTML = d.html || '';
          var fresh = t.querySelector('.hbk-freebar');
          bar.innerHTML = fresh ? fresh.outerHTML : '';
        }).catch(function () {});
    });
  }

  // Category index: the picture follows the hovered or focused category.
  function categoryIndex(root) {
    var pic = root.querySelector('.hbk-index__pic img');
    if (!pic) return;
    function show(a) {
      var src = a.getAttribute('data-img');
      if (!src) return;
      root.querySelectorAll('.is-on').forEach(function (x) { x.classList.remove('is-on'); });
      a.classList.add('is-on');
      pic.src = src;
    }
    root.querySelectorAll('.hbk-index__link').forEach(function (a) {
      a.addEventListener('mouseenter', function () { show(a); });
      a.addEventListener('focus', function () { show(a); });
    });
  }

  // "Add to bag" instead of "Add to cart" when the shop says bag (token cart_word).
  function bagWord() {
    var L = F.labels || {};
    if (!L.add) return;
    function apply() {
      document.querySelectorAll('[data-button-action="add-to-cart"]').forEach(function (b) {
        var nodes = Array.prototype.filter.call(b.childNodes, function (n) { return n.nodeType === 3 && n.textContent.trim(); });
        if (nodes.length) nodes[nodes.length - 1].textContent = ' ' + L.add + ' ';
        b.querySelectorAll('.product-miniature__add-text').forEach(function (t) { t.textContent = L.add; });
      });
    }
    apply();
    if (window.prestashop && prestashop.on) {
      prestashop.on('updatedProduct', function () { window.setTimeout(apply, 0); });
      prestashop.on('updateProductList', function () { window.setTimeout(apply, 50); });
    }
    document.addEventListener('shown.bs.modal', function () { window.setTimeout(apply, 0); });
  }

  function init() {
    document.querySelectorAll('.js-hbk-slider').forEach(slider);
    document.querySelectorAll('.hbk-products').forEach(tabs);
    document.querySelectorAll('.js-hbk-countdown').forEach(countdown);
    document.querySelectorAll('.hbk-index').forEach(categoryIndex);
    drawerInit();
    productTabs();
    filterDrawer();
    headerScroll();
    quickAdd();
    addFeedback();
    searchPanel();
    searchField();
    sizeGuide();
    quickFacet();
    cartBar();
    bagWord();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
