<div class="hbk-text hbk-head--{$s.heading_align}">
  {if $section.subtitle}<p class="hbk-eyebrow">{$section.subtitle}</p>{/if}
  {if $section.title}<h2 class="hbk-title">{$section.title}</h2>{/if}
  {if $section.content}<div class="hbk-text__body">{$section.content nofilter}</div>{/if}
  {if $section.href}<a class="btn btn-outline-primary hbk-btn" href="{$section.href}">{l s='Discover more' d='Modules.Hbkit.Shop'}</a>{/if}
</div>
