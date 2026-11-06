{**
 * Stride Atelier: the shoebox label. Reference, name and brand, then every feature as a ruled row.
 * The feature set as the stamp (Theme Kit HBKIT_STAMP_FEATURE, here "stitched sole") shows as the
 * "Resolable" stamp instead of a row. The fit feature (HBKIT_FIT_FEATURE) also gives the fit note that
 * custom.js moves under the sizes.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
{if $product.features}
  {assign var=sa_stamp value=false}
  <section class="sa-label" aria-label="{l s='What it is made of' d='Modules.Hbkit.Shop'}">
    <div class="sa-label__head">
      <b>{if $product.reference}{$product.reference} · {/if}{$product.name}</b>
      {if !empty($product_manufacturer->name)}<span>{$product_manufacturer->name}</span>{/if}
    </div>
    <dl class="sa-label__rows">
      {foreach from=$product.features item=feature}
        {if $feature.id_feature == $modules.hbkit.stamp_feature}
          {assign var=sa_stamp value=true}
        {else}
          <div class="sa-label__row"><dt>{$feature.name}</dt><dd>{$feature.value}</dd></div>
        {/if}
      {/foreach}
    </dl>
    {if $sa_stamp}<span class="sa-label__stamp">{l s='Resolable in our workshop' d='Modules.Hbkit.Shop'}</span>{/if}
    {foreach from=$product.features item=feature}
      {if $modules.hbkit.fit.feature && $feature.id_feature == $modules.hbkit.fit.feature}
        <p class="sa-fitnote js-sa-fitnote" hidden data-step="{$modules.hbkit.fit.steps[$feature.id_feature_value]|default:0}">
          <span class="sa-fitnote__scale" aria-hidden="true">{section name=s loop=$modules.hbkit.fit.count}<i{if $smarty.section.s.iteration == $modules.hbkit.fit.steps[$feature.id_feature_value]|default:0} class="is-on"{/if}></i>{/section}</span>
          <b>{$feature.value}.</b>
        </p>
      {/if}
    {/foreach}
  </section>
{/if}
