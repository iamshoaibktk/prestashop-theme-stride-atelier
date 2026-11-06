<div class="hbk-news{if $section.image_url} hbk-news--image{/if}">
  {if $section.image_url}<div class="hbk-news__media"><img src="{$section.image_url}" alt="" loading="lazy"></div>{/if}
  <div class="hbk-news__body">
    {if $section.subtitle}<p class="hbk-eyebrow">{$section.subtitle}</p>{/if}
    {if $section.title}<h2 class="hbk-title">{$section.title}</h2>{/if}
    {if $section.content}<div class="hbk-news__text">{$section.content nofilter}</div>{/if}
    {if $section.has_module}{widget name='ps_emailsubscription' hook='displayHome'}{/if}
  </div>
</div>
