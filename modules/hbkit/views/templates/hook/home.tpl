<div class="hbk-home">
  {foreach $hbkit_sections as $section}
    {assign var=s value=$section.settings}
    <section id="hbk-s{$section.id}" class="hbk-section hbk-s-{$section.type|replace:'_':'-'} hbk-bg-{$s.background} hbk-space-{$s.spacing} hbk-w-{$s.width}{if !empty($s.css)} {$s.css|escape:'html':'UTF-8'}{/if}">
      <div class="{if $s.width == 'full'}hbk-full{elseif $s.width == 'narrow'}container hbk-narrow{else}container{/if}">
        {if $section.type != 'hero' && $section.type != 'countdown' && $section.type != 'newsletter' && $section.type != 'text' && ($section.title || $section.subtitle)}
          <header class="hbk-head hbk-head--{$s.heading_align}">
            {if $s.heading_align == 'split'}
              {if $section.title}<h2 class="hbk-title">{$section.title}</h2>{/if}
              {if $section.subtitle && $section.head_href}<a class="hbk-head__link" href="{$section.head_href}">{$section.subtitle}</a>{/if}
            {else}
              {if $section.subtitle}<p class="hbk-eyebrow">{$section.subtitle}</p>{/if}
              {if $section.title}<h2 class="hbk-title">{$section.title}</h2>{/if}
            {/if}
            {if $section.content && $section.type != 'image_text'}<div class="hbk-intro">{$section.content nofilter}</div>{/if}
          </header>
        {/if}
        {include file="module:hbkit/views/templates/hook/sections/{$section.type}.tpl" section=$section s=$s}
      </div>
    </section>
  {/foreach}
</div>
