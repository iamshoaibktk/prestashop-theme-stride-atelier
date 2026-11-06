<div class="hbk-deal">
  <div class="hbk-deal__media">
    {if $section.image_url}<img src="{$section.image_url}" alt="" loading="lazy">{elseif $section.product && $section.product.cover}<img src="{$section.product.cover.bySize.large_default.url}" alt="{$section.product.name|escape:'html':'UTF-8'}" loading="lazy">{/if}
  </div>
  <div class="hbk-deal__body">
    {if $section.subtitle}<p class="hbk-eyebrow">{$section.subtitle}</p>{/if}
    {if $section.title}<h2 class="hbk-title">{$section.title}</h2>{/if}
    {if $section.content}<div class="hbk-deal__text">{$section.content nofilter}</div>{/if}
    {if $section.product}
      <p class="hbk-deal__product"><a href="{$section.product.url}">{$section.product.name}</a></p>
      <p class="hbk-deal__price">{if $section.product.has_discount}<s>{$section.product.regular_price}</s> {/if}<strong>{$section.product.price}</strong></p>
    {/if}
    <div class="hbk-countdown js-hbk-countdown" data-to="{$section.date_to|escape:'html':'UTF-8'}">
      <span><b data-u="d">0</b>{l s='days' d='Modules.Hbkit.Shop'}</span>
      <span><b data-u="h">0</b>{l s='hours' d='Modules.Hbkit.Shop'}</span>
      <span><b data-u="m">0</b>{l s='min' d='Modules.Hbkit.Shop'}</span>
      <span><b data-u="s">0</b>{l s='sec' d='Modules.Hbkit.Shop'}</span>
    </div>
    {if $section.href}<a class="btn btn-primary hbk-btn" href="{$section.href}">{l s='Shop the offer' d='Modules.Hbkit.Shop'}</a>{/if}
  </div>
</div>
