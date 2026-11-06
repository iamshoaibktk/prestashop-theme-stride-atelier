/* Static live preview (GitHub Pages): actions that need a real PrestaShop show a notice instead. */
(function () {
  'use strict';
  var lang = (document.documentElement.lang || 'en').slice(0, 2);
  var T = {
    en: ['Live preview', 'This is a static preview. Cart, account, search and filters work once the theme is installed on PrestaShop.'],
    fr: ['Aperçu en ligne', "Ceci est un aperçu statique. Le panier, le compte, la recherche et les filtres fonctionnent une fois le thème installé sur PrestaShop."],
    es: ['Vista previa', 'Esta es una vista previa estática. El carrito, la cuenta, la búsqueda y los filtros funcionan al instalar el tema en PrestaShop.'],
    it: ['Anteprima', "Questa è un'anteprima statica. Carrello, account, ricerca e filtri funzionano installando il tema su PrestaShop."],
    pl: ['Podgląd', 'To jest statyczny podgląd. Koszyk, konto, wyszukiwarka i filtry działają po instalacji motywu w PrestaShop.'],
    de: ['Live-Vorschau', 'Dies ist eine statische Vorschau. Warenkorb, Konto, Suche und Filter funktionieren nach der Installation des Themes in PrestaShop.'],
    cs: ['Náhled', 'Toto je statický náhled. Košík, účet, vyhledávání a filtry fungují po instalaci šablony do PrestaShopu.'],
    nl: ['Live preview', 'Dit is een statische preview. Winkelwagen, account, zoeken en filters werken zodra het thema in PrestaShop is geïnstalleerd.'],
    pt: ['Pré-visualização', 'Esta é uma pré-visualização estática. Carrinho, conta, pesquisa e filtros funcionam depois de instalar o tema no PrestaShop.'],
    ru: ['Предпросмотр', 'Это статический предпросмотр. Корзина, аккаунт, поиск и фильтры работают после установки темы в PrestaShop.']
  }[lang] || null;
  T = T || ['Live preview', 'This is a static preview. Cart, account, search and filters work once the theme is installed on PrestaShop.'];

  var toast;
  function notice() {
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'static-demo-toast';
      toast.setAttribute('role', 'status');
      document.body.appendChild(toast);
    }
    toast.innerHTML = '<strong>' + T[0] + '</strong><br>' + T[1];
    toast.classList.add('is-on');
    clearTimeout(toast._t);
    toast._t = setTimeout(function () { toast.classList.remove('is-on'); }, 3500);
  }

  var LIVE = '[data-button-action="add-to-cart"], .js-quickview, .quick-view, .js-product-miniature [type=submit], #search_filters a, #search_filters input, .js-search-link, .facet a, a[href="#live-only"], a[href^="#live-only"], .product-miniature__quickview, .js-product-add-to-cart';
  // Add to cart: show the theme's side cart or notice with this product, if the theme uses one.
  function productInfo(el) {
    var card = el.closest('.product-miniature, .js-product-miniature');
    var root = card || document;
    var q = function (s) { return root.querySelector(s); };
    var name = q(card ? '.product-miniature__title' : 'h1.product__name, h1');
    var img = q(card ? 'img' : '.product__images .carousel-item.active img, .product__images img');
    var price = q(card ? '.product-miniature__price, .price' : '.product__current-price, .current-price-value, .product__price .price, .product__price, .current-price');
    var text = function (el) { if (!el) return ''; var c = el.cloneNode(true); c.querySelectorAll('.visually-hidden, .sr-only').forEach(function (x) { x.remove(); }); return c.textContent.replace(/\s+/g, ' ').trim(); };
    return { name: text(name), img: img ? (img.currentSrc || img.src) : '', price: text(price) };
  }
  document.addEventListener('click', function (e) {
    var el = e.target.closest(LIVE);
    if (!el) return;
    e.preventDefault(); e.stopImmediatePropagation();
    if (el.matches('[data-button-action="add-to-cart"], .js-product-miniature [type=submit], .js-product-add-to-cart') && window.hbkitDemo && window.hbkitDemo(productInfo(el))) return;
    notice();
  }, true);
  document.addEventListener('submit', function (e) { e.preventDefault(); e.stopImmediatePropagation(); notice(); }, true);
  document.addEventListener('change', function (e) {
    if (e.target.closest('#search_filters, .products-sort-order')) { e.preventDefault(); e.stopImmediatePropagation(); notice(); }
  }, true);

  // Silence background AJAX calls the theme makes to a live shop.
  if (window.fetch) {
    var f = window.fetch;
    window.fetch = function (u) {
      var s = String(u && u.url || u);
      if (/[?&](ajax|action|id_products(%5B%5D|\[\])?)=|\/module\/|\/cart\b|controller=|live-only/.test(s)) { return Promise.resolve(new Response('{}', { status: 200, headers: { 'Content-Type': 'application/json' } })); }
      return f.apply(this, arguments);
    };
  }

  document.addEventListener('DOMContentLoaded', function () {
    var bar = document.createElement('div');
    bar.className = 'static-demo-bar';
    bar.innerHTML = '<span>' + T[0] + ' · Hummingbird theme for PrestaShop 9</span>';
    document.body.appendChild(bar);
  });
})();
