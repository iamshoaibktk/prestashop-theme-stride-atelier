{**
 * One setting control. $c = field definition (HbkitForm), $name, $id, $value.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
{assign var=ctl value=$c.ctl}
<div class="k-f"{if !empty($c.show_if)} data-show-if="{$c.show_if[0]}" data-show-value="{$c.show_if[1]}"{/if} data-key="{$c.key}">
  {if $ctl == 'switch'}
    <label class="k-sw"><input type="hidden" name="{$name}" value="0"><input type="checkbox" name="{$name}" id="{$id}" value="1"{if $value} checked{/if}><i></i>{$c.label}</label>
  {else}
    {if $ctl == 'seg' || $ctl == 'pics' || $ctl == 'swatches' || $ctl == 'icon'}
      <span class="k-lbl" id="{$id}-l">{$c.label}{if !empty($c.optional)} <span class="k-opt">({l s='optional' d='Modules.Hbkit.Admin'})</span>{/if}</span>
    {else}
      <label class="k-lbl" for="{$id}">{$c.label}{if !empty($c.optional)} <span class="k-opt">({l s='optional' d='Modules.Hbkit.Admin'})</span>{/if}</label>
    {/if}
    {if $ctl == 'seg'}
      <div class="k-seg" role="radiogroup" aria-labelledby="{$id}-l">
        {foreach $c.options as $val => $lab}<label><input type="radio" name="{$name}" value="{$val}"{if (string)$value == (string)$val} checked{/if}><span>{$lab}</span></label>{/foreach}
      </div>
    {elseif $ctl == 'pics'}
      <div class="k-pics" role="radiogroup" aria-labelledby="{$id}-l">
        {foreach $c.options as $val => $o}
          <label class="hbbo-choice"><input type="radio" name="{$name}" value="{$val}"{if (string)$value == (string)$val} checked{/if}>
            <span class="hbbo-choice__pic">{$o[2] nofilter}</span><span class="hbbo-choice__name">{$o[0]}</span>{if $o[1]}<span class="hbbo-choice__help">{$o[1]}</span>{/if}
            <span class="hbbo-choice__tick material-icons" aria-hidden="true">check_circle</span></label>
        {/foreach}
      </div>
    {elseif $ctl == 'swatches'}
      <div class="k-swatches" role="radiogroup" aria-labelledby="{$id}-l">
        {foreach $c.options as $val => $o}
          <label class="k-swatch"><input type="radio" name="{$name}" value="{$val}"{if (string)$value == (string)$val} checked{/if}><i style="background:{if isset($hbk_swatch[$val])}{$hbk_swatch[$val]}{else}#fff{/if}"></i><span>{$o[0]}</span></label>
        {/foreach}
      </div>
    {elseif $ctl == 'select'}
      <select class="k-sel" name="{$name}" id="{$id}">{foreach $c.options as $val => $lab}<option value="{$val}"{if (string)$value == (string)$val} selected{/if}>{$lab}</option>{/foreach}</select>
    {elseif $ctl == 'range'}
      <div class="k-range"><input type="range" name="{$name}" id="{$id}" min="{$c.min}" max="{$c.max}" step="{$c.step}" value="{$value|intval}"><output for="{$id}">{$value|intval}{$c.unit}</output></div>
    {elseif $ctl == 'category'}
      <select class="k-sel" name="{$name}" id="{$id}"><option value="0">{l s='Choose a category' d='Modules.Hbkit.Admin'}</option>{foreach $hbk_lists.category as $cid => $cname}<option value="{$cid}"{if $value == $cid} selected{/if}>{$cname}</option>{/foreach}</select>
    {elseif $ctl == 'manufacturer'}
      <select class="k-sel" name="{$name}" id="{$id}"><option value="0">{l s='Choose a brand' d='Modules.Hbkit.Admin'}</option>{foreach $hbk_lists.manufacturer as $mid => $mname}<option value="{$mid}"{if $value == $mid} selected{/if}>{$mname}</option>{/foreach}</select>
    {elseif $ctl == 'product'}
      <select class="k-sel" name="{$name}" id="{$id}"><option value="0">{l s='Choose a product' d='Modules.Hbkit.Admin'}</option>{foreach $hbk_lists.product as $pid => $pname}<option value="{$pid}"{if $value == $pid} selected{/if}>{$pname}</option>{/foreach}</select>
    {elseif $ctl == 'date'}
      <input class="k-inp" type="datetime-local" name="{$name}" id="{$id}" value="{$value|replace:' ':'T'|truncate:16:'':true|escape:'html':'UTF-8'}">
    {elseif $ctl == 'icon'}
      <div class="k-icons" role="radiogroup" aria-labelledby="{$id}-l">
        {foreach $hbk_icons as $icon}<label title="{$icon}"><input type="radio" name="{$name}" value="{$icon}"{if $value == $icon} checked{/if}><span class="material-icons" aria-hidden="true">{$icon}</span><span class="sr-only">{$icon}</span></label>{/foreach}
      </div>
    {elseif $ctl == 'link'}
      <div class="k-picker js-k-link">
        <input type="hidden" name="{$name}" value="{$value|escape:'html':'UTF-8'}" class="js-k-link-value">
        <select class="k-sel js-k-link-kind" id="{$id}" aria-label="{l s='Link type' d='Modules.Hbkit.Admin'}">{foreach $hbk_lists.types as $k => $lab}<option value="{$k}">{$lab}</option>{/foreach}</select>
        {foreach ['category', 'cms', 'product', 'manufacturer', 'page', 'attribute'] as $k}
          <select class="k-sel js-k-link-target" data-kind="{$k}" aria-label="{$hbk_lists.types[$k]}" hidden>
            {foreach $hbk_lists[$k] as $tid => $tname}<option value="{$tid}">{$tname}</option>{/foreach}
          </select>
        {/foreach}
      </div>
    {elseif $ctl == 'image'}
      <div class="k-drop js-k-drop{if !$value} is-empty{/if}">
        {if $value}<img src="{$hbk_img_base}{$value}" alt="" class="js-k-drop-img">{else}<span class="k-drop__empty js-k-drop-img"><span class="material-icons" aria-hidden="true">image</span></span>{/if}
        <div>
          <div class="k-drop__name js-k-drop-name">{if $value}{$value|regex_replace:'/^.*\//':''}{else}{l s='Drop an image here' d='Modules.Hbkit.Admin'}{/if}</div>
          <div class="k-drop__btns">
            <label class="hbbo-btn hbbo-btn--small" style="margin:0">{if $value}{l s='Replace' d='Modules.Hbkit.Admin'}{else}{l s='Choose a file' d='Modules.Hbkit.Admin'}{/if}<input type="file" name="{$name}" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" class="js-k-drop-file"></label>
            {if $value}<button type="button" class="hbbo-btn hbbo-btn--small hbbo-btn--quiet js-k-drop-remove" style="color:#b42318!important">{l s='Remove' d='Modules.Hbkit.Admin'}</button><input type="hidden" name="{$name|regex_replace:'/\]$/':'_remove]'}" value="" class="js-k-drop-removed">{/if}
          </div>
        </div>
      </div>
    {else}
      <input class="k-inp" type="text" name="{$name}" id="{$id}" value="{$value|escape:'html':'UTF-8'}">
    {/if}
  {/if}
  {if !empty($c.help)}<p class="k-help">{$c.help}</p>{/if}
</div>
