{**
 * Stride Atelier product card: the photo ends in a kraft label strip (reference and fit), then the name,
 * the price with the colours, and the material. The strip is drawn over the photo by custom.css.
 * The cart button block is Hummingbird's own; the Theme Kit card style shows it as a square "+".
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
{extends file='parent:catalog/_partials/miniatures/product.tpl'}

{* Colours are shown at the end of the price row (see product_discount_price). *}
{block name='product_variants'}{/block}

{block name='product_discount_price'}
  {if $product.has_discount}
    <span class="product-miniature__regular-price" aria-label="{l s='Regular price' d='Shop.Theme.Catalog'}">{$product.regular_price}</span>
  {/if}
  {capture name='sa_tags'}{hook h='displayProductListReviews' product=$product}{/capture}
  <span class="sa-strip" aria-hidden="true"><span>{$product.reference|regex_replace:'/^([A-Za-z]+-[0-9]+)-[0-9]+$/':'$1'}</span>{if $smarty.capture.sa_tags|trim}<span class="sa-strip__tag">{$smarty.capture.sa_tags nofilter}</span>{/if}</span>
  {if $product.main_variants}<span class="product-miniature__variants sa-card__dots">{include file='catalog/_partials/variant-links.tpl' variants=$product.main_variants}</span>{/if}
{/block}

{* The material (second Theme Kit card label). *}
{block name='product_reviews'}
  {if $smarty.capture.sa_tags|default:''|trim}<div class="sa-card__mat">{$smarty.capture.sa_tags nofilter}</div>{/if}
{/block}
