{* Language switcher for translatable fields. @author iamshoaibktk@gmail.com @license AFL-3.0 *}
<div class="k-langbar">
  <p class="k-help">{l s='Texts in' d='Modules.Hbkit.Admin'} <b class="js-k-lang-name">{$hbk_languages[0].name|regex_replace:'/\s*\(.*\)$/':''}</b>. {l s='A dot marks a language with empty fields.' d='Modules.Hbkit.Admin'} <button type="button" class="k-linkbtn js-k-copy-lang">{l s='Copy to empty languages' d='Modules.Hbkit.Admin'}</button></p>
  <div class="k-langs" role="group" aria-label="{l s='Language' d='Modules.Hbkit.Admin'}">
    {foreach $hbk_languages as $lang}<button type="button" class="js-k-lang{if $lang.id_lang == $hbk_id_lang} is-on{/if}" data-lang="{$lang.id_lang}" data-name="{$lang.name|regex_replace:'/\s*\(.*\)$/':''}" data-iso="{$lang.iso_code|upper}">{$lang.iso_code|upper}</button>{/foreach}
  </div>
</div>
