<div class="hbk-quotes hbk-quotes--{$s.style}">
  {foreach $section.items as $item}
    <figure class="hbk-quote">
      {if $item.rating|default:0}<div class="hbk-stars" aria-label="{l s='%d out of 5 stars' sprintf=[$item.rating|intval] d='Modules.Hbkit.Shop'}">{for $i=1 to 5}<span class="material-icons{if $i > $item.rating} is-off{/if}" aria-hidden="true">star</span>{/for}</div>{/if}
      <blockquote class="hbk-quote__text">{$item.text nofilter}</blockquote>
      <figcaption class="hbk-quote__who"><strong>{$item.title}</strong>{if $item.subtitle}<span>{$item.subtitle}</span>{/if}</figcaption>
    </figure>
  {/foreach}
</div>
