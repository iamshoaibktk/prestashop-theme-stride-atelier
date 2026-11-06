{**
 * One item: a row in the list and its side panel. $n = index or __N__, $item, $is_new.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
<li class="k-it js-k-item{if !$item.active} is-off{/if}" data-n="{$n}">
  <span class="k-grip" aria-hidden="true"><span class="material-icons">drag_indicator</span></span>
  {if $item.thumb}<img class="k-thumb js-k-item-thumb" src="{$item.thumb}" alt="">{else}<span class="k-thumb k-thumb--empty js-k-item-thumb"><span class="material-icons" aria-hidden="true">{if $hbk_section.type == 'faq'}help_outline{elseif $hbk_section.type == 'testimonials'}format_quote{elseif $hbk_section.type == 'products'}grid_view{elseif $hbk_section.type == 'usp'}{$item.data.icon|default:'star'}{else}image{/if}</span></span>{/if}
  <span style="min-width:0">
    <span class="k-it__name js-k-item-name">{if $item.name}{$item.name}{else}{l s='New %item%' sprintf=['%item%' => $hbk_info.items.one|lower] d='Modules.Hbkit.Admin'}{/if}</span>
    <span class="k-it__meta js-k-item-meta">{$item.meta}</span>
  </span>
  <label class="k-sw" title="{l s='Show on the homepage' d='Modules.Hbkit.Admin'}"><input type="checkbox" name="items[{$n}][active]" value="1" class="js-k-item-active"{if $item.active} checked{/if}><i></i><span class="sr-only">{l s='Show on the homepage' d='Modules.Hbkit.Admin'}</span></label>
  <button type="button" class="hbbo-btn hbbo-btn--small js-k-item-edit">{l s='Edit' d='Modules.Hbkit.Admin'}</button>
  <button type="button" class="hbbo-icon js-k-item-delete" title="{l s='Delete' d='Modules.Hbkit.Admin'}" aria-label="{l s='Delete' d='Modules.Hbkit.Admin'}"><span class="material-icons">delete_outline</span></button>
  <input type="hidden" name="items[{$n}][id]" value="{$item.id|intval}">
  <input type="hidden" name="items[{$n}][position]" value="{$item.position|intval}" class="js-k-item-pos">
  <input type="hidden" name="items[{$n}][delete]" value="" class="js-k-item-del">
  {if !empty($is_new)}<input type="hidden" name="items[{$n}][is_new]" value="1">{/if}

</li>
