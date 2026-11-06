/**
 * Stride Atelier:
 * - the fit note from the shoebox label goes under the size buttons on the product page (and in quick view);
 * - the EU size bar on category pages shows only when the quick filter has sizes;
 * - the resoling band on the home page gets the anchor the hero's second button points to.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 */
(function () {
  var F = window.hbkitFront || {};

  function fitNote(root) {
    (root || document).querySelectorAll('.js-sa-fitnote').forEach(function (note) {
      var scope = note.closest('.product, .quickview, #main') || document;
      var target = null;
      scope.querySelectorAll('.product-variant').forEach(function (v) {
        var lg = v.querySelector('.product-variant__legend');
        if (!target && v.querySelector('.btn-check') && (!F.size_name || (lg && lg.textContent.trim() === F.size_name))) target = v;
      });
      if (!target || target.querySelector('.sa-fitnote')) return;
      var copy = note.cloneNode(true);
      copy.hidden = false;
      copy.classList.remove('js-sa-fitnote');
      var hint = target.querySelector('.hbk-sizehint');
      target.insertBefore(copy, hint || null);
    });
  }

  function sizeBar() {
    var bar = document.querySelector('.js-sa-sizes');
    if (!bar) return;
    var chips = bar.querySelectorAll('.hbk-chip');
    bar.hidden = chips.length === 0;
    var box = bar.querySelector('.js-hbk-chips');
    var label = bar.querySelector('.js-sa-sizes-label');
    if (label && box) label.textContent = (box.getAttribute('aria-label') || '') + ' · EU';
  }

  function anchors() {
    var band = document.querySelector('.hbk-s-image-text.hbk-bg-dark');
    if (band && !document.getElementById('resoling')) band.id = 'resoling';
  }

  var queued = false;
  function run() { queued = false; fitNote(); sizeBar(); }
  function queue() { if (!queued) { queued = true; window.requestAnimationFrame(run); } }

  function init() {
    anchors();
    run();
    new MutationObserver(queue).observe(document.body, { childList: true, subtree: true });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
