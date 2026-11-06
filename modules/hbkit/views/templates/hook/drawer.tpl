{**
 * Side cart shell. Lines are loaded by hbkit.js from the module's "cart" controller.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
{if $hbkit_front.drawer}
  <div class="hbk-drawer" id="hbk-cart-drawer" hidden>
    <div class="hbk-drawer__backdrop js-hbk-drawer-close"></div>
    <aside class="hbk-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="hbk-drawer-title" tabindex="-1">
      <header class="hbk-drawer__head">
        <p class="hbk-drawer__title" id="hbk-drawer-title">{if $hbkit_cart_word == 'bag'}{l s='Bag' d='Modules.Hbkit.Shop'}{else}{l s='Your cart' d='Modules.Hbkit.Shop'}{/if} <span class="hbk-drawer__count js-hbk-drawer-count"></span></p>
        <button type="button" class="hbk-drawer__close js-hbk-drawer-close" aria-label="{l s='Close' d='Modules.Hbkit.Shop'}"><i class="material-icons" aria-hidden="true">close</i></button>
      </header>
      <div class="hbk-drawer__body js-hbk-drawer-body" aria-live="polite">
        <div class="hbk-drawer__loading"><span class="hbk-spinner" aria-hidden="true"></span></div>
      </div>
    </aside>
  </div>
{/if}
{if $hbkit_front.cart_style == 'toast'}
  <div class="hbk-toast" id="hbk-cart-toast" role="status" aria-live="polite" hidden></div>
{/if}
{if $hbkit_search}
  <div class="hbk-search" id="hbk-search" role="dialog" aria-modal="true" aria-label="{l s='Search' d='Modules.Hbkit.Shop'}" hidden>
    <div class="hbk-search__shade js-hbk-search-close"></div>
    <div class="hbk-search__panel">
      <div class="container">
        <form class="hbk-search__row" action="{$hbkit_search.url}" method="get" role="search">
          {if !$configuration.is_friendly_url|default:true}<input type="hidden" name="controller" value="search">{/if}
          <label class="visually-hidden" for="hbk-search-q">{l s='Search' d='Modules.Hbkit.Shop'}</label>
          <input class="hbk-search__input js-hbk-search-input" id="hbk-search-q" name="s" type="search" autocomplete="off" spellcheck="false"
            placeholder="{if $hbkit_texts.search_placeholder}{$hbkit_texts.search_placeholder}{else}{l s='Search products' d='Modules.Hbkit.Shop'}{/if}">
          <button type="button" class="hbk-search__close js-hbk-search-close">{l s='Close' d='Modules.Hbkit.Shop'}</button>
        </form>
        <div class="hbk-search__idle js-hbk-search-idle">
          {if $hbkit_texts.search_popular}
            <div>
              <p class="hbk-search__h">{l s='Popular' d='Modules.Hbkit.Shop'}</p>
              <ul class="hbk-search__links">
                {foreach $hbkit_texts.search_popular as $term}
                  <li><a href="{$hbkit_search.url}{if strpos($hbkit_search.url, '?') !== false}&amp;{else}?{/if}s={$term|urlencode}">{$term}</a></li>
                {/foreach}
              </ul>
            </div>
          {/if}
          {if $hbkit_search.categories}
            <div>
              <p class="hbk-search__h">{l s='Categories' d='Modules.Hbkit.Shop'}</p>
              <ul class="hbk-search__links">
                {foreach $hbkit_search.categories as $c}<li><a href="{$c.url}">{$c.name}</a></li>{/foreach}
              </ul>
            </div>
          {/if}
        </div>
        <div class="hbk-search__results js-hbk-search-results" aria-live="polite" hidden></div>
      </div>
    </div>
  </div>
{/if}
