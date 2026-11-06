{if $s.style == 'index'}
  <div class="hbk-index">
    <ul class="hbk-index__list">
      {foreach $section.items as $item}
        <li><a class="hbk-index__link{if $item@first} is-on{/if}" href="{$item.href}"{if !empty($item.image_url)} data-img="{$item.image_url}"{/if}>
          <span class="hbk-index__name">{$item.title}</span>
          {if !empty($item.count)}<span class="hbk-index__count">{l s='%count% products' sprintf=['%count%' => $item.count] d='Modules.Hbkit.Shop'}</span>{/if}
        </a></li>
      {/foreach}
    </ul>
    {$first = reset($section.items)}
    <div class="hbk-index__pic">{if !empty($first.image_url)}<img src="{$first.image_url}" alt="" loading="lazy">{/if}</div>
  </div>
{else}
<div class="hbk-cats hbk-cats--{$s.style}" style="--hbk-cols:{$s.columns|intval}">
  {foreach $section.items as $item}
    <a class="hbk-cats__item" href="{$item.href}">
      <span class="hbk-cats__media">{if !empty($item.image_url)}<img src="{$item.image_url}" alt="" loading="lazy" {if !empty($item.image_w)}width="{$item.image_w}" height="{$item.image_h}"{/if}>{/if}</span>
      <span class="hbk-cats__body">
        <span class="hbk-cats__title">{$item.title}</span>
        {if $item.subtitle}<span class="hbk-cats__sub">{$item.subtitle}</span>{/if}
        {if $item.button}<span class="hbk-cats__cta">{$item.button}</span>{/if}
      </span>
    </a>
  {/foreach}
</div>
{/if}
