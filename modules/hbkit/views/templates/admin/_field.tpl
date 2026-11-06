{* $f = [kind, options], $name, $value, $id *}
{assign var=kind value=$f[0]}
{if $kind == 'select'}
  <select class="form-control custom-select" name="{$name}" id="{$id}">{foreach $f[1] as $opt}<option value="{$opt}"{if $value == $opt} selected{/if}>{$opt}</option>{/foreach}</select>
{elseif $kind == 'bool'}
  <span class="ps-switch ps-switch-sm"><input type="checkbox" name="{$name}" id="{$id}" value="1"{if $value} checked{/if}> <label for="{$id}">{l s='Yes' d='Modules.Hbkit.Admin'}</label></span>
{elseif $kind == 'int'}
  <input class="form-control" type="number" name="{$name}" id="{$id}" value="{$value|intval}">
{elseif $kind == 'color'}
  <input class="form-control hbk-color" type="color" name="{$name}" id="{$id}" value="{$value|escape:'html':'UTF-8'}">
{elseif $kind == 'date'}
  <input class="form-control" type="datetime-local" name="{$name}" id="{$id}" value="{$value|replace:' ':'T'|escape:'html':'UTF-8'}">
{elseif $kind == 'category'}
  <select class="form-control custom-select" name="{$name}" id="{$id}"><option value="0">—</option>{foreach $hbk_categories as $cid => $cname}<option value="{$cid}"{if $value == $cid} selected{/if}>{$cname}</option>{/foreach}</select>
{elseif $kind == 'manufacturer'}
  <select class="form-control custom-select" name="{$name}" id="{$id}"><option value="0">—</option>{foreach $hbk_manufacturers as $m}<option value="{$m.id_manufacturer}"{if $value == $m.id_manufacturer} selected{/if}>{$m.name}</option>{/foreach}</select>
{elseif $kind == 'product'}
  <input class="form-control" type="number" name="{$name}" id="{$id}" value="{$value|intval}" placeholder="{l s='Product ID' d='Modules.Hbkit.Admin'}">
{elseif $kind == 'icon'}
  <select class="form-control custom-select" name="{$name}" id="{$id}"><option value="">—</option>{foreach $hbk_icons as $icon}<option value="{$icon}"{if $value == $icon} selected{/if}>{$icon}</option>{/foreach}</select>
{elseif $kind == 'link'}
  <input class="form-control" type="text" name="{$name}" id="{$id}" value="{$value|escape:'html':'UTF-8'}" placeholder="category:3 · cms:4 · product:12 · manufacturer:2 · page:new-products">
{elseif $kind == 'image'}
  <div class="hbk-image">
    {if $value}<img src="{$hbk_img_base}{$value}" alt="" class="hbk-image__thumb"><label class="hbk-image__remove"><input type="checkbox" name="{$name|regex_replace:'/\]$/':'_remove]'}" value="1"> {l s='Remove' d='Modules.Hbkit.Admin'}</label>{/if}
    <input class="form-control-file" type="file" name="{$name}" accept="image/*">
  </div>
{else}
  <input class="form-control" type="text" name="{$name}" id="{$id}" value="{$value|escape:'html':'UTF-8'}">
{/if}
