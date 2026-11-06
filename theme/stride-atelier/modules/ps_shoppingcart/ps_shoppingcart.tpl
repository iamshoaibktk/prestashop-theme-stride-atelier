{**
 * Stride Atelier: the header link says "Bag" with a bag icon and the count.
 * Based on Hummingbird's template. @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}

<div id="_desktop_ps_shoppingcart">
  <div class="ps-shoppingcart">
    <div class="header-block d-flex align-items-center blockcart cart-preview {if $cart.products_count> 0}header-block--active{else}inactive{/if}" data-refresh-url="{$refresh_url}">
      {if $cart.products_count> 0}
        <a class="header-block__action-btn pe-md-0" rel="nofollow" href="{$cart_url}" aria-label="{l s='View cart (%d products)' d='Shop.Theme.Checkout' sprintf=[$cart.products_count]}">
      {else}
        <span class="header-block__action-btn pe-md-0">
      {/if}

      <svg class="header-block__icon ls-bag" width="20" height="20" viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M4 7h12l-1 10H5L4 7z" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M7 7a3 3 0 0 1 6 0" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>
      <span class="header-block__title">{l s='Bag' d='Modules.Hbkit.Shop'}</span>
      <span class="header-block__badge">{$cart.products_count}</span>

      {if $cart.products_count> 0}
        </a>
      {else}
        </span>
      {/if}
    </div>
  </div>
</div>
