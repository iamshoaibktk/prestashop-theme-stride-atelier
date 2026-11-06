<div class="hbk-gallery" style="--hbk-cols:{$s.columns|intval}">
  {foreach $section.items as $item}
    <a class="hbk-gallery__item" href="{$item.href|default:'#'}">
      {if !empty($item.image_url)}<img src="{$item.image_url}" alt="{$item.title|escape:'html':'UTF-8'}" loading="lazy" {if !empty($item.image_w)}width="{$item.image_w}" height="{$item.image_h}"{/if}>{/if}
      {if $item.title}<span class="hbk-gallery__cap">{$item.title}</span>{/if}
    </a>
  {/foreach}
</div>
