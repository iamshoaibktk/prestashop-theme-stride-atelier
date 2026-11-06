{**
 * Theme Kit > Top bar: one field per message, per language, with a preview.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
<form method="post" action="{$hbk_urls.topbar}" class="js-k-form" data-msg-unsaved="{l s='You have unsaved changes. Leave anyway?' d='Modules.Hbkit.Admin'}">
  <input type="hidden" name="hbkit_action" value="save_topbar">
  <div class="hbbo-main__head">
    <div><h2 class="hbbo-main__title">{l s='Top bar' d='Modules.Hbkit.Admin'}</h2><p class="hbbo-main__intro">{l s='The thin strip above the header. With more than one message, they take turns.' d='Modules.Hbkit.Admin'}</p></div>
    <label class="k-sw"><input type="hidden" name="topbar_active" value="0"><input type="checkbox" name="topbar_active" value="1"{if $hbk_topbar.active} checked{/if}><i></i>{l s='Show the top bar' d='Modules.Hbkit.Admin'}</label>
  </div>
  <section class="hbbo-card" style="margin-top:20px"><div class="k-card__head"><h2>{l s='Preview' d='Modules.Hbkit.Admin'}</h2></div>
    <div class="k-card__body"><div class="k-topbar-preview js-k-topbar-preview" style="background:{$hbk_tokens.dark};color:{$hbk_tokens.dark_text}"></div></div></section>
  <section class="hbbo-card js-k-msgs" style="margin-top:20px">
    {if $hbk_languages|count > 1}{include file='./_langbar.tpl'}{/if}
    <div class="k-card__head"><div><h2>{l s='Messages' d='Modules.Hbkit.Admin'}</h2><p>{l s='Keep each message short: one line on a phone is about 40 characters.' d='Modules.Hbkit.Admin'}</p></div></div>
    <div class="k-card__body">
      {foreach $hbk_languages as $lang}
        <input type="hidden" class="js-k-msg-value" data-lang="{$lang.id_lang}" name="topbar_text_{$lang.id_lang}" value="{$hbk_topbar.text[$lang.id_lang]|escape:'html':'UTF-8'}">
        <ul class="k-items js-k-msg-list hbk-l" data-lang="{$lang.id_lang}"{if $lang.id_lang != $hbk_id_lang} hidden{/if}>
          {foreach $hbk_topbar.msgs[$lang.id_lang] as $m}
            <li class="k-it k-msg-row js-k-msg-row"><span class="k-grip" aria-hidden="true"><span class="material-icons">drag_indicator</span></span>
              <input class="k-inp js-k-msg" type="text" value="{$m|trim|escape:'html':'UTF-8'}" aria-label="{l s='Message' d='Modules.Hbkit.Admin'}" maxlength="120">
              <button type="button" class="hbbo-icon js-k-msg-remove" aria-label="{l s='Remove message' d='Modules.Hbkit.Admin'}"><span class="material-icons">close</span></button></li>
          {/foreach}
        </ul>
      {/foreach}
      <div class="k-addrow"><button type="button" class="hbbo-btn hbbo-btn--small js-k-msg-add"><span class="material-icons" aria-hidden="true">add</span>{l s='Add a message' d='Modules.Hbkit.Admin'}</button></div>
      <div class="k-f" style="margin-top:22px">
        <label class="k-lbl" for="k-tb-link-{$hbk_id_lang}">{l s='Link of the first message' d='Modules.Hbkit.Admin'} <span class="k-lang js-k-lang-tag"></span> <span class="k-opt">({l s='optional' d='Modules.Hbkit.Admin'})</span></label>
        {foreach $hbk_languages as $lang}
          <input type="url" class="k-inp hbk-l" data-lang="{$lang.id_lang}" id="k-tb-link-{$lang.id_lang}" name="topbar_link_{$lang.id_lang}" value="{$hbk_topbar.link[$lang.id_lang]|escape:'html':'UTF-8'}" placeholder="https://"{if $lang.id_lang != $hbk_id_lang} hidden{/if}>
        {/foreach}
        <p class="k-help">{l s='A full web address, for example your delivery page.' d='Modules.Hbkit.Admin'}</p>
      </div>
    </div>
  </section>
  <div class="k-savebar"><span class="k-savebar__state js-k-dirty" hidden>{l s='Unsaved changes' d='Modules.Hbkit.Admin'}</span><button type="submit" class="hbbo-btn hbbo-btn--primary">{l s='Save' d='Modules.Hbkit.Admin'}</button></div>
</form>
