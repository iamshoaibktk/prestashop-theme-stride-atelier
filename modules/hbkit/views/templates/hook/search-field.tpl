{**
 * Header: a search field that lists the first results while you type (search style "field").
 * Without JavaScript it submits to the search page.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
<div class="hbk-sfield header-block col">
  <form class="hbk-sfield__form js-hbk-sfield" method="get" action="{$hbkit_search_url}" role="search">
    <label class="visually-hidden" for="hbk-sfield-q">{l s='Search' d='Modules.Hbkit.Shop'}</label>
    <svg class="hbk-sfield__icon" width="18" height="18" viewBox="0 0 18 18" aria-hidden="true" focusable="false"><circle cx="7.5" cy="7.5" r="5.75" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M11.75 11.75L16.5 16.5" stroke="currentColor" stroke-width="1.8"/></svg>
    <input class="hbk-sfield__input js-hbk-sfield-input" id="hbk-sfield-q" type="search" name="s" value="{$hbkit_search_query|escape:'html':'UTF-8'}" placeholder="{if $hbkit_texts.search_placeholder}{$hbkit_texts.search_placeholder}{else}{l s='Search products' d='Modules.Hbkit.Shop'}{/if}" autocomplete="off" aria-controls="hbk-sfield-list" aria-expanded="false">
    <div class="hbk-sfield__drop js-hbk-sfield-drop" id="hbk-sfield-list" aria-live="polite" hidden></div>
  </form>
  <a class="hbk-sfield__open header-block__action-btn" href="{$hbkit_search_url}" aria-label="{l s='Search' d='Modules.Hbkit.Shop'}"><svg width="20" height="20" viewBox="0 0 18 18" aria-hidden="true" focusable="false"><circle cx="7.5" cy="7.5" r="5.75" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M11.75 11.75L16.5 16.5" stroke="currentColor" stroke-width="1.8"/></svg></a>
</div>
