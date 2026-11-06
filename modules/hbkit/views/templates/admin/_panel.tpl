{**
 * Side panel with an item's fields. $n = index or __N__, $item, $is_new.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
  <aside class="k-panel js-k-panel" data-n="{$n}" role="dialog" aria-modal="true" aria-labelledby="k-panel-{$n}-t" hidden>
    <div class="k-panel__head"><h2 id="k-panel-{$n}-t">{if !empty($is_new)}{$hbk_info.items.add}{else}{l s='Edit %item%' sprintf=['%item%' => $hbk_info.items.one|lower] d='Modules.Hbkit.Admin'}{/if}</h2>
      <button type="button" class="hbbo-icon js-k-panel-close" aria-label="{l s='Close' d='Modules.Hbkit.Admin'}"><span class="material-icons">close</span></button></div>
    {if $hbk_item_def.lang && $hbk_languages|count > 1}{include file='./_langbar.tpl'}{/if}
    <div class="k-panel__body">
      {foreach $hbk_item_def.data as $c}
        {if $c.ctl == 'image' || $c.ctl == 'category' || $c.ctl == 'manufacturer' || $c.ctl == 'icon' || ($c.ctl == 'select' && $c.key == 'source')}
          {include file='./_ctl.tpl' c=$c name="items[{$n}][data][{$c.key}]" id="k-{$n}-{$c.key}" value=$item.data[$c.key]|default:''}
        {/if}
      {/foreach}
      {foreach $hbk_item_def.lang as $f}
        <div class="k-f">
          <label class="k-lbl" for="k-{$n}-{$f.key}-{$hbk_id_lang}">{$f.label}{if $hbk_languages|count > 1} <span class="k-lang js-k-lang-tag">{$hbk_languages[0].iso_code|upper}</span>{/if}{if $f.optional} <span class="k-opt">({l s='optional' d='Modules.Hbkit.Admin'})</span>{/if}</label>
          {foreach $hbk_languages as $lang}
            {if $f.kind == 'textarea'}
              <textarea class="k-ta hbk-l{if $f.key == 'title'} js-k-item-title{/if}" rows="3" data-lang="{$lang.id_lang}" id="k-{$n}-{$f.key}-{$lang.id_lang}" name="items[{$n}][{$f.key}][{$lang.id_lang}]"{if $lang.id_lang != $hbk_id_lang} hidden{/if}>{$item[$f.key][$lang.id_lang]|default:''|escape:'html':'UTF-8'}</textarea>
            {else}
              <input type="text" class="k-inp hbk-l{if $f.key == 'title'} js-k-item-title{/if}" data-lang="{$lang.id_lang}" id="k-{$n}-{$f.key}-{$lang.id_lang}" name="items[{$n}][{$f.key}][{$lang.id_lang}]" value="{$item[$f.key][$lang.id_lang]|default:''|escape:'html':'UTF-8'}"{if $lang.id_lang != $hbk_id_lang} hidden{/if}>
            {/if}
          {/foreach}
          {if $f.help}<p class="k-help">{$f.help}</p>{/if}
        </div>
      {/foreach}
      {foreach $hbk_item_def.data as $c}
        {if !($c.ctl == 'image' || $c.ctl == 'category' || $c.ctl == 'manufacturer' || $c.ctl == 'icon' || ($c.ctl == 'select' && $c.key == 'source'))}
          {include file='./_ctl.tpl' c=$c name="items[{$n}][data][{$c.key}]" id="k-{$n}-{$c.key}" value=$item.data[$c.key]|default:''}
        {/if}
      {/foreach}
    </div>
    <div class="k-panel__foot"><button type="button" class="hbbo-btn hbbo-btn--primary js-k-panel-close">{l s='Done' d='Modules.Hbkit.Admin'}</button></div>
  </aside>
