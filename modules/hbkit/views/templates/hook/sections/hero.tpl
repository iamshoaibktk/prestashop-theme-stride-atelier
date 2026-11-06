<div class="hbk-hero hbk-hero--{$s.variant} hbk-hero--h-{$s.height} js-hbk-slider" data-autoplay="{$s.autoplay|intval}" style="--hbk-overlay:{($s.overlay|intval)/100}">
  <div class="hbk-hero__track">
    {foreach $section.items as $slide}
      <div class="hbk-hero__slide hbk-tone-{$slide.tone|default:'light'} hbk-align-{$slide.align|default:'left'}{if $slide@first} is-active{/if}" {if !$slide@first}aria-hidden="true"{/if}>
        {if !empty($slide.image_url)}
          <picture class="hbk-hero__media">
            {if !empty($slide.image_mobile_url)}<source media="(max-width: 767px)" srcset="{$slide.image_mobile_url}">{/if}
            <img src="{$slide.image_url}" alt="{$slide.title|escape:'html':'UTF-8'}" {if $slide.image_w}width="{$slide.image_w}" height="{$slide.image_h}"{/if} {if $slide@first}fetchpriority="high"{else}loading="lazy"{/if}>
          </picture>
        {/if}
        <div class="hbk-hero__content">
          <div class="hbk-hero__box">
            {if $slide.subtitle}<p class="hbk-eyebrow">{$slide.subtitle}</p>{/if}
            {if $slide.title}{if $slide@first && !empty($modules.hbkit.hero_h1)}<h1 class="hbk-hero__title">{$slide.title}</h1>{elseif $slide@first}<h2 class="hbk-hero__title">{$slide.title}</h2>{else}<p class="hbk-hero__title">{$slide.title}</p>{/if}{/if}
            {if $slide.text}<div class="hbk-hero__text">{$slide.text nofilter}</div>{/if}
            {if $slide.button && $slide.href}<a class="btn btn-primary hbk-btn" href="{$slide.href}">{$slide.button}</a>{/if}
          </div>
          {if $slide@first && !empty($section.card)}{assign var=card value=$section.card}
            <aside class="hbk-hcard">
              {if $card.label}<p class="hbk-hcard__label">{$card.label}</p>{/if}
              {if $card.value || $card.text}<p class="hbk-hcard__now">{if $card.value}<b>{$card.value}</b>{/if}{if $card.text}<span>{$card.text}</span>{/if}</p>{/if}
              {if $card.product}
                <a class="hbk-hcard__product" href="{$card.product.url}">
                  {if $card.product.cover}<img src="{$card.product.cover.bySize.small_default.url}" alt="" width="{$card.product.cover.bySize.small_default.width}" height="{$card.product.cover.bySize.small_default.height}" loading="lazy">{/if}
                  <span><span class="hbk-hcard__name">{$card.product.name}</span>{if $card.tag}<span class="hbk-ftag">{$card.tag}</span>{/if}</span>
                </a>
              {/if}
            </aside>
          {/if}
        </div>
      </div>
    {/foreach}
  </div>
  {if $section.items|count > 1}
    <button type="button" class="hbk-hero__nav hbk-hero__nav--prev js-hbk-prev" aria-label="{l s='Previous slide' d='Modules.Hbkit.Shop'}"><span class="material-icons" aria-hidden="true">chevron_left</span></button>
    <button type="button" class="hbk-hero__nav hbk-hero__nav--next js-hbk-next" aria-label="{l s='Next slide' d='Modules.Hbkit.Shop'}"><span class="material-icons" aria-hidden="true">chevron_right</span></button>
    <div class="hbk-hero__dots">
      {foreach $section.items as $slide}<button type="button" class="js-hbk-dot{if $slide@first} is-active{/if}" aria-label="{l s='Go to slide %d' sprintf=[$slide@iteration] d='Modules.Hbkit.Shop'}"></button>{/foreach}
    </div>
  {/if}
</div>
