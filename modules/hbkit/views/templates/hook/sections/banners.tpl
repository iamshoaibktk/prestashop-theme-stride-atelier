{if $s.style == 'ruler'}
<div class="hbk-ruler" id="hbk-ruler" style="--hbk-stops:{$section.items|count}">
  {foreach $section.items as $item}
    <a class="hbk-ruler__stop" href="{$item.href|default:'#'}">
      <span class="hbk-ruler__label">
        {if $item.title}<span class="hbk-ruler__age">{$item.title}</span>{/if}
        {if $item.subtitle}<span class="hbk-ruler__note">{$item.subtitle}</span>{/if}
      </span>
      <span class="hbk-ruler__ticks" aria-hidden="true"></span>
    </a>
  {/foreach}
</div>
{else}
<div class="hbk-banners hbk-banners--l{$s.layout|replace:'-':'_'} hbk-banners--{$s.style} hbk-banners--h-{$s.height}">
  {foreach $section.items as $item}
    <a class="hbk-banner hbk-tone-{$item.tone|default:'light'} hbk-align-{$item.align|default:'left'}" href="{$item.href|default:'#'}">
      {if !empty($item.image_url)}<span class="hbk-banner__media"><img src="{$item.image_url}" alt="" loading="lazy" {if !empty($item.image_w)}width="{$item.image_w}" height="{$item.image_h}"{/if}></span>{/if}
      <span class="hbk-banner__body">
        {if $item.subtitle}<span class="hbk-eyebrow">{$item.subtitle}</span>{/if}
        {if $item.title}<span class="hbk-banner__title">{$item.title}</span>{/if}
        {if $item.text}<span class="hbk-banner__text">{$item.text|strip_tags}</span>{/if}
        {if $item.button}<span class="hbk-banner__btn">{$item.button}</span>{/if}
      </span>
    </a>
  {/foreach}
</div>
{/if}
