<ul class="hbk-brands{if $s.grayscale} hbk-brands--gray{/if}">
  {foreach $section.brands as $brand}
    <li><a href="{$brand.href}" title="{$brand.name|escape:'html':'UTF-8'}">{if $brand.image}<img src="{$brand.image}" alt="{$brand.name|escape:'html':'UTF-8'}" loading="lazy">{else}<span>{$brand.name}</span>{/if}</a></li>
  {/foreach}
</ul>
