{**
 * Side cart contents (returned as HTML by the "cart" controller).
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
{if $hbk.cart.products_count > 0}
  {include file='module:hbkit/views/templates/hook/freebar.tpl'}
  <ul class="hbk-drawer__lines">
    {foreach from=$hbk.cart.products item=product}
      {$img = $product.cover|default:$product.default_image}
      <li class="hbk-line">
        <a class="hbk-line__img" href="{$product.url}">
          {if $img}<img src="{$img.bySize.cart_default.url|default:$img.small.url}" alt="{$product.name|escape:'html':'UTF-8'}" loading="lazy" width="96" height="96">{/if}
        </a>
        <div class="hbk-line__body">
          <a class="hbk-line__name" href="{$product.url}">{$product.name}</a>
          {if $product.attributes}
            <p class="hbk-line__attrs">{foreach from=$product.attributes key=k item=v name=a}{$v|trim}{if !$smarty.foreach.a.last}, {/if}{/foreach}</p>
          {/if}
          <div class="hbk-line__row hbk-line__ctl">
            <div class="hbk-qty" role="group" aria-label="{l s='Quantity' d='Modules.Hbkit.Shop'}">
              <button type="button" class="js-hbk-line" data-url="{$product.down_quantity_url}" aria-label="{l s='Decrease quantity' d='Modules.Hbkit.Shop'}"{if $product.quantity <= 1} data-remove="{$product.remove_from_cart_url}"{/if}>&minus;</button>
              <span>{$product.quantity|intval}</span>
              <button type="button" class="js-hbk-line" data-url="{$product.up_quantity_url}" aria-label="{l s='Increase quantity' d='Modules.Hbkit.Shop'}">+</button>
            </div>
            <button type="button" class="hbk-line__rm js-hbk-line" data-url="{$product.remove_from_cart_url}" aria-label="{l s='Remove %name%' sprintf=['%name%' => $product.name] d='Modules.Hbkit.Shop'}">{l s='Remove' d='Modules.Hbkit.Shop'}</button>
          </div>
        </div>
        <span class="hbk-line__price">{$product.total}</span>
      </li>
    {/foreach}
  </ul>
  <footer class="hbk-drawer__foot">
    <div class="hbk-drawer__total">
      <span>{l s='Subtotal' d='Modules.Hbkit.Shop'}</span>
      <strong>{$hbk.cart.subtotals.products.value}</strong>
    </div>
    <p class="hbk-drawer__note">{l s='Taxes and delivery are calculated at checkout.' d='Modules.Hbkit.Shop'}</p>
    <a class="btn btn-primary hbk-drawer__btn" href="{$hbk.urls.checkout}">{l s='Checkout' d='Modules.Hbkit.Shop'}</a>
    <a class="btn btn-outline-primary hbk-drawer__btn" href="{$hbk.urls.cart}">{if $hbk.word == 'bag'}{l s='View bag' d='Modules.Hbkit.Shop'}{else}{l s='View cart' d='Modules.Hbkit.Shop'}{/if}</a>
  </footer>
{else}
  <div class="hbk-drawer__empty">
    <i class="material-icons" aria-hidden="true">shopping_bag</i>
    <p class="hbk-drawer__empty-title">{if $hbk.word == 'bag'}{l s='Your bag is empty' d='Modules.Hbkit.Shop'}{else}{l s='Your cart is empty' d='Modules.Hbkit.Shop'}{/if}</p>
    <p>{l s='Find something you love and it will wait for you here.' d='Modules.Hbkit.Shop'}</p>
    <a class="btn btn-primary" href="{$hbk.urls.home}">{l s='Continue shopping' d='Modules.Hbkit.Shop'}</a>
  </div>
{/if}
