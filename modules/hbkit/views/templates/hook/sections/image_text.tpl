<div class="hbk-it hbk-it--{$s.style} hbk-it--{$s.ratio}">
  {foreach $section.items as $item}
    <div class="hbk-it__row">
      <div class="hbk-it__media">{if !empty($item.image_url)}<img src="{$item.image_url}" alt="{$item.title|escape:'html':'UTF-8'}" loading="lazy" {if !empty($item.image_w)}width="{$item.image_w}" height="{$item.image_h}"{/if}>{/if}</div>
      <div class="hbk-it__body">
        {if $item.subtitle}<p class="hbk-eyebrow">{$item.subtitle}</p>{/if}
        {if $item.title}<h3 class="hbk-it__title">{$item.title}</h3>{/if}
        {if $item.text}<div class="hbk-it__text">{$item.text nofilter}</div>{/if}
        {if $item.button && $item.href}<a class="btn btn-outline-primary hbk-btn" href="{$item.href}">{$item.button}</a>{/if}
      </div>
    </div>
  {/foreach}
</div>
