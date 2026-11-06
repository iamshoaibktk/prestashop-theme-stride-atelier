<ul class="hbk-usp hbk-usp--{$s.style}" style="--hbk-cols:{$section.items|count}">
  {foreach $section.items as $item}
    <li class="hbk-usp__item">
      {if !empty($item.icon)}<span class="hbk-usp__icon material-icons" aria-hidden="true">{$item.icon}</span>{/if}
      <span class="hbk-usp__body">
        <strong class="hbk-usp__title">{$item.title}</strong>
        {if $item.text}<span class="hbk-usp__text">{$item.text|strip_tags}</span>{/if}
      </span>
    </li>
  {/foreach}
</ul>
