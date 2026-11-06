<div class="hbk-faq">
  {foreach $section.items as $item}
    <details class="hbk-faq__item"{if $item@first} open{/if}>
      <summary class="hbk-faq__q">{$item.title}<span class="material-icons" aria-hidden="true">expand_more</span></summary>
      <div class="hbk-faq__a">{$item.text nofilter}</div>
    </details>
  {/foreach}
</div>
