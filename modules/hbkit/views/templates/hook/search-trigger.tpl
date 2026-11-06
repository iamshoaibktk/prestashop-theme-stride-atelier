{**
 * Header: opens the search panel. Without JavaScript it is a plain link to the search page.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
<div class="hbk-search-trigger header-block d-flex align-items-center col-auto">
  <a class="header-block__action-btn js-hbk-search-open" href="{$hbkit_search_url}" aria-haspopup="dialog" aria-controls="hbk-search">
    <svg class="hbk-search-trigger__icon" width="18" height="18" viewBox="0 0 18 18" aria-hidden="true" focusable="false"><circle cx="7.5" cy="7.5" r="5.75" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M11.75 11.75L16.5 16.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/></svg>
    <span class="hbk-search-trigger__label">{l s='Search' d='Modules.Hbkit.Shop'}</span>
  </a>
</div>
