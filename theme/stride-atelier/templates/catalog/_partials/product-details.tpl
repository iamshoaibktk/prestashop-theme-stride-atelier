{**
 * Stride Atelier: the features are shown as the shoebox label under the photo (catalog/product.tpl), so this part only adds
 * "Delivery and returns". Keeps .js-product-details, which the theme refreshes on combination change.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
<div class="js-product-details" data-product="{$product.embedded_attributes|json_encode}">
  {if !empty($modules.hbkit.texts.delivery)}
    <div class="accordion-item" id="product_delivery">
      <h2 class="accordion-header" id="product_delivery_heading">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#product_delivery_collapse" aria-expanded="false" aria-controls="product_delivery_collapse">
          {l s='Delivery and returns' d='Modules.Hbkit.Shop'}
        </button>
      </h2>
      <div id="product_delivery_collapse" class="accordion-collapse collapse" aria-labelledby="product_delivery_heading">
        <div class="accordion-body">{$modules.hbkit.texts.delivery}</div>
      </div>
    </div>
  {/if}
</div>
