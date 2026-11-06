{**
 * Section editor: only the fields this section type uses, in plain words.
 * Items are a list (drag to reorder, switch to hide) with a side panel each.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
<form method="post" action="{$hbk_urls.self}" enctype="multipart/form-data" class="js-k-form" id="hbbo-edit"
  data-msg-unsaved="{l s='You have unsaved changes. Leave anyway?' d='Modules.Hbkit.Admin'}"
  data-msg-delete="{l s='Undo' d='Modules.Hbkit.Admin'}">
  <input type="hidden" name="hbkit_action" value="save_section">
  <input type="hidden" name="id_section" value="{$hbk_section.id}">

  <div class="hbbo-main__head">
    <div>
      <a class="k-back" href="{$hbk_urls.sections}"><span class="material-icons" aria-hidden="true">arrow_back</span>{l s='Homepage sections' d='Modules.Hbkit.Admin'}</a>
      <h2 class="hbbo-main__title">{$hbk_info.name}</h2>
      <p class="hbbo-main__intro">{$hbk_info.intro}</p>
    </div>
    <label class="k-sw"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1"{if $hbk_section.active} checked{/if}><i></i>{l s='Visible on the homepage' d='Modules.Hbkit.Admin'}</label>
  </div>

  <div class="k-split" style="margin-top:20px">
    <div>
      {if $hbk_heading.fields}
        <section class="hbbo-card">
          {if $hbk_languages|count > 1}{include file='./_langbar.tpl'}{/if}
          <div class="k-card__head"><h2>{$hbk_heading.card}</h2></div>
          <div class="k-card__body">
            {if $hbk_heading.help}<p class="k-help" style="margin:0 0 14px">{$hbk_heading.help}</p>{/if}
            {foreach $hbk_heading.fields as $f}
              <div class="k-f">
                <label class="k-lbl" for="k-h-{$f.key}-{$hbk_id_lang}"><span class="js-k-label"{if !empty($f.label_split)} data-label="{$f.label|escape:'html':'UTF-8'}" data-label-split="{$f.label_split|escape:'html':'UTF-8'}"{/if}>{$f.label}</span>{if $hbk_languages|count > 1} <span class="k-lang js-k-lang-tag"></span>{/if}{if $f.optional} <span class="k-opt">({l s='optional' d='Modules.Hbkit.Admin'})</span>{/if}</label>
                {foreach $hbk_languages as $lang}
                  {if $f.kind == 'textarea'}
                    <textarea class="k-ta hbk-l" rows="3" data-lang="{$lang.id_lang}" id="k-h-{$f.key}-{$lang.id_lang}" name="{$f.key}_{$lang.id_lang}"{if $lang.id_lang != $hbk_id_lang} hidden{/if}>{$hbk_section[$f.key][$lang.id_lang]|default:''|escape:'html':'UTF-8'}</textarea>
                  {else}
                    <input type="text" class="k-inp hbk-l" data-lang="{$lang.id_lang}" id="k-h-{$f.key}-{$lang.id_lang}" name="{$f.key}_{$lang.id_lang}" value="{$hbk_section[$f.key][$lang.id_lang]|default:''|escape:'html':'UTF-8'}"{if $lang.id_lang != $hbk_id_lang} hidden{/if}>
                  {/if}
                {/foreach}
              </div>
              {if $f.key == 'subtitle' && $hbk_heading.link}
                {include file='./_ctl.tpl' c=$hbk_heading.link name="settings[head_link]" id="k-head-link" value=$hbk_section.settings.head_link|default:''}
              {/if}
            {/foreach}
          </div>
        </section>
      {/if}

      {if $hbk_has_items}
        <section class="hbbo-card">
          <div class="k-card__head"><div><h2>{$hbk_info.items.many}</h2>{if $hbk_info.items.help}<p>{$hbk_info.items.help}</p>{/if}</div></div>
          <div class="k-card__body">
            <ul class="k-items js-k-items"{if !$hbk_items} hidden{/if}>
              {foreach $hbk_items as $item}{include file='./_item.tpl' n=$item@index item=$item}{/foreach}
            </ul>
            <div class="k-addrow"><button type="button" class="hbbo-btn hbbo-btn--small js-k-add-item"><span class="material-icons" aria-hidden="true">add</span>{$hbk_info.items.add}</button></div>
          </div>
        </section>
      {/if}

      {if !$hbk_heading.fields && !$hbk_has_items}
        <section class="hbbo-card"><div class="k-card__body"><p class="k-help">{l s='This section has no texts of its own.' d='Modules.Hbkit.Admin'}</p></div></section>
      {/if}
    </div>

    <div>
      {if $hbk_settings.layout || $hbk_heading.align}
        <section class="hbbo-card">
          <div class="k-card__head"><h2>{l s='Layout' d='Modules.Hbkit.Admin'}</h2></div>
          <div class="k-card__body">
            {foreach $hbk_settings.layout as $c}
              {include file='./_ctl.tpl' c=$c name="settings[{$c.key}]" id="k-set-{$c.key}" value=$hbk_section.settings[$c.key]|default:''}
            {/foreach}
            {if $hbk_heading.align}
              {include file='./_ctl.tpl' c=$hbk_heading.align name="settings[heading_align]" id="k-set-heading_align" value=$hbk_section.settings.heading_align|default:'center'}
            {/if}
          </div>
        </section>
      {/if}
      <section class="hbbo-card"{if $hbk_settings.layout || $hbk_heading.align} style="margin-top:20px"{/if}>
        <div class="k-card__head"><h2>{l s='Section look' d='Modules.Hbkit.Admin'}</h2></div>
        <div class="k-card__body">
          {foreach $hbk_settings.look as $c}
            {include file='./_ctl.tpl' c=$c name="settings[{$c.key}]" id="k-set-{$c.key}" value=$hbk_section.settings[$c.key]|default:''}
          {/foreach}
        </div>
      </section>
    </div>
  </div>

  {if $hbk_has_items}
    <div class="js-k-panels">
      {foreach $hbk_items as $item}{include file='./_panel.tpl' n=$item@index item=$item}{/foreach}
    </div>
    <template class="js-k-item-template">{include file='./_item.tpl' n='__N__' item=$hbk_blank is_new=1}</template>
    <template class="js-k-panel-template">{include file='./_panel.tpl' n='__N__' item=$hbk_blank is_new=1}</template>
  {/if}

  <div class="k-savebar">
    <span class="k-savebar__state js-k-dirty" hidden>{l s='Unsaved changes' d='Modules.Hbkit.Admin'}</span>
    <a class="hbbo-btn hbbo-btn--quiet" href="{$hbk_urls.sections}">{l s='Cancel' d='Modules.Hbkit.Admin'}</a>
    <button type="submit" class="hbbo-btn hbbo-btn--primary">{l s='Save' d='Modules.Hbkit.Admin'}</button>
  </div>
</form>
<div class="hbbo-layer-shade js-k-shade" hidden></div>
