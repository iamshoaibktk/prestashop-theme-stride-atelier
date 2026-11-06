{**
 * Theme Kit > Design: one group of tokens. Layout choices are picture cards.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
<form id="hbbo-design" method="post" action="{$hbk_urls.self}" class="js-k-form" data-msg-unsaved="{l s='You have unsaved changes. Leave anyway?' d='Modules.Hbkit.Admin'}">
  <input type="hidden" name="hbkit_action" value="save_options">
  <div class="hbbo-main__head">
    <div>
      <h2 class="hbbo-main__title">{$hbk_group.title|escape:'html':'UTF-8'}</h2>
      <p class="hbbo-main__intro">{$hbk_group.intro|escape:'html':'UTF-8'}</p>
    </div>
    <a class="hbbo-btn hbbo-btn--small" href="{$hbk_preview}" target="_blank" rel="noopener"><i class="material-icons" aria-hidden="true">open_in_new</i>{l s='Open an example page' d='Modules.Hbkit.Admin'}</a>
  </div>

  {$simple = []}
  {foreach $hbk_group.fields as $f}
    {if $f.type == 'choice' && $f.visual}
      <section class="hbbo-card hbbo-group">
        <h3 class="hbbo-group__title">{$f.label|escape:'html':'UTF-8'}</h3>
        {if $f.help}<p class="hbbo-group__help">{$f.help|escape:'html':'UTF-8'}</p>{/if}
        <div class="hbbo-choices" role="radiogroup" aria-label="{$f.label|escape:'html':'UTF-8'}">
          {foreach $f.choices as $c}
            <label class="hbbo-choice">
              <input type="radio" name="tokens[{$f.key}]" value="{$c.value|escape:'html':'UTF-8'}"{if $f.value == $c.value} checked{/if}>
              <span class="hbbo-choice__pic">{$c.svg nofilter}</span>
              <span class="hbbo-choice__name">{$c.label|escape:'html':'UTF-8'}</span>
              {if $c.help}<span class="hbbo-choice__help">{$c.help|escape:'html':'UTF-8'}</span>{/if}
              <span class="hbbo-choice__tick material-icons" aria-hidden="true">check_circle</span>
            </label>
          {/foreach}
        </div>
      </section>
    {else}
      {$simple[] = $f}
    {/if}
  {/foreach}

  {if $hbk_group.colour_groups}
    <div class="k-split" style="margin-top:20px">
      <div>
        {foreach $hbk_group.colour_groups as $g}
          <section class="hbbo-card"{if !$g@first} style="margin-top:20px"{/if}>
            <div class="k-card__head"><h2>{$g.title}</h2></div>
            <div class="k-card__body">
              {foreach $g.fields as $f}
                <div class="k-crow js-k-color" data-key="{$f.key}">
                  <input type="color" value="{$f.value|escape:'html':'UTF-8'}" aria-label="{$f.label|escape:'html':'UTF-8'}">
                  <input class="k-inp" type="text" name="tokens[{$f.key}]" value="{$f.value|upper|escape:'html':'UTF-8'}" pattern="#[0-9A-Fa-f]{literal}{6}{/literal}" aria-label="{$f.label|escape:'html':'UTF-8'}">
                  <span><b>{$f.label}</b>{if $f.help}<small>{$f.help}</small>{/if}</span>
                </div>
              {/foreach}
            </div>
          </section>
        {/foreach}
      </div>
      <div>
        <section class="hbbo-card k-preview">
          <h2 style="margin:0 0 12px;font-size:15px;font-weight:700">{l s='Preview' d='Modules.Hbkit.Admin'}</h2>
          <div style="padding:16px;border-radius:8px;background:{$hbk_tokens.bg}" data-pv="bg" data-pv-prop="background-color">
            <div class="k-pv-img" style="background:{$hbk_tokens.surface}" data-pv="surface" data-pv-prop="background-color"></div>
            <div class="k-pv-row" style="color:{$hbk_tokens.text}" data-pv="text"><span>{l s='Product name' d='Modules.Hbkit.Admin'}</span><span>€89</span></div>
            <span class="k-pv-tag" style="border-color:{$hbk_tokens.text};border-left-color:{$hbk_tokens.accent};color:{$hbk_tokens.text}" data-pv="accent" data-pv-prop="border-left-color">{l s='Label' d='Modules.Hbkit.Admin'}</span>
            <p style="margin:10px 0 0;font-size:12px;color:{$hbk_tokens.muted}" data-pv="muted">{l s='Soft text' d='Modules.Hbkit.Admin'} <s style="color:{$hbk_tokens.muted}">€99</s> <span style="color:{$hbk_tokens.sale}" data-pv="sale">€79</span></p>
            <span class="k-pv-btn" style="background:{$hbk_tokens.primary};color:{$hbk_tokens.primary_contrast}" data-pv="primary" data-pv-prop="background-color">{l s='Add to cart' d='Modules.Hbkit.Admin'}</span>
          </div>
          <div style="margin-top:12px;padding:12px;border-radius:8px;background:{$hbk_tokens.dark};color:{$hbk_tokens.dark_text};font-size:12px" data-pv="dark" data-pv-prop="background-color">{l s='Dark footer' d='Modules.Hbkit.Admin'}</div>
        </section>
      </div>
    </div>
  {elseif $simple}
    <section class="hbbo-card hbbo-group">
      <div class="hbbo-fields">
        {foreach $simple as $f}
          <div class="k-f">
            {if $f.type == 'choice'}
              <span class="k-lbl" id="hbk-tok-{$f.key}-l">{$f.label}</span>
              <div class="k-seg" role="radiogroup" aria-labelledby="hbk-tok-{$f.key}-l">{foreach $f.choices as $c}<label><input type="radio" name="tokens[{$f.key}]" value="{$c.value|escape:'html':'UTF-8'}"{if $f.value == $c.value} checked{/if}><span>{$c.label|escape:'html':'UTF-8'}</span></label>{/foreach}</div>
            {elseif $f.type == 'color'}
              <label class="k-lbl" for="hbk-tok-{$f.key}">{$f.label}</label>
              <div class="k-crow js-k-color" data-key="{$f.key}" style="grid-template-columns:40px 1fr;padding:0"><input type="color" value="{$f.value|escape:'html':'UTF-8'}" aria-label="{$f.label|escape:'html':'UTF-8'}"><input class="k-inp" id="hbk-tok-{$f.key}" name="tokens[{$f.key}]" value="{$f.value|upper|escape:'html':'UTF-8'}"></div>
            {else}
              <label class="k-lbl" for="hbk-tok-{$f.key}">{$f.label}</label>
              {if $f.unit}
                <div class="k-unit"><input type="text" inputmode="decimal" class="k-inp" id="hbk-tok-{$f.key}" name="tokens[{$f.key}]" value="{$f.value|escape:'html':'UTF-8'}"><span>{$f.unit}</span></div>
              {else}
                <input type="text" class="k-inp" id="hbk-tok-{$f.key}" name="tokens[{$f.key}]" value="{$f.value|escape:'html':'UTF-8'}">
              {/if}
            {/if}
            {if $f.help}<p class="k-help">{$f.help|escape:'html':'UTF-8'}</p>{/if}
          </div>
        {/foreach}
      </div>
    </section>
  {/if}

  <div class="k-savebar"><span class="k-savebar__state js-k-dirty" hidden>{l s='Unsaved changes' d='Modules.Hbkit.Admin'}</span><button type="submit" class="hbbo-btn hbbo-btn--primary">{l s='Save' d='Modules.Hbkit.Admin'}</button></div>
</form>
