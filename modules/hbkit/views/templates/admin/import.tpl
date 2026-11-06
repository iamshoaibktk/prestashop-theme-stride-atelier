{**
 * Theme Kit > Demo import: one card per demo, one confirm dialog.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
<div class="hbbo-main__head">
  <div><h2 class="hbbo-main__title">{l s='Demo import' d='Modules.Hbkit.Admin'}</h2><p class="hbbo-main__intro">{l s='Load a complete demo: products, categories, pages, menu, footer links, homepage and design. Orders and customers are never touched.' d='Modules.Hbkit.Admin'}</p></div>
</div>
{if $hbk_import_log}
  <section class="hbbo-card" style="margin-top:20px"><div class="k-card__body"><p class="k-note" style="border-color:#15803d;background:#f0fdf4"><b>{l s='Import finished' d='Modules.Hbkit.Admin'}</b></p><ul class="k-help" style="margin:10px 0 0">{foreach $hbk_import_log as $line}<li>{$line}</li>{/foreach}</ul></div></section>
{/if}
{if !$hbk_packs}
  <section class="hbbo-card" style="margin-top:20px"><div class="k-card__body"><p class="k-help">{l s='No demo found. A demo lives in themes/<theme>/demo/demo.json.' d='Modules.Hbkit.Admin'}</p></div></section>
{/if}
<div class="k-demos" style="margin-top:20px">
  {foreach $hbk_packs as $pack}
    {assign var=installed value=($hbk_demo && $hbk_demo == $pack.theme)}
    <article class="k-demo">
      {if $pack.preview}<img src="{$pack.preview}" alt="" loading="lazy">{/if}
      <div class="k-demo__body">
        <h3>{$pack.name}{if $installed} <span class="k-badge">{l s='Installed' d='Modules.Hbkit.Admin'}</span>{/if}</h3>
        <p>{$pack.description}</p>
        <button type="button" class="hbbo-btn hbbo-btn--small{if !$installed} hbbo-btn--primary{/if} js-k-import" data-pack="{$pack.theme}" data-name="{$pack.name|escape:'html':'UTF-8'}" data-what="{$pack.summary|default:''|escape:'html':'UTF-8'}">{if $installed}{l s='Import again…' d='Modules.Hbkit.Admin'}{else}{l s='Import…' d='Modules.Hbkit.Admin'}{/if}</button>
      </div>
    </article>
  {/foreach}
</div>
<div class="hbbo-layer-shade js-k-import-shade" data-layer="import" hidden></div>
<form class="k-dialog js-k-import-dialog" data-layer="import" method="post" action="{$hbk_urls.import}" role="dialog" aria-modal="true" aria-labelledby="k-im-t" hidden>
  <input type="hidden" name="hbkit_action" value="import"><input type="hidden" name="pack" value="">
  <div class="k-dialog__head"><h2 id="k-im-t">{l s='Import' d='Modules.Hbkit.Admin'} <span class="js-k-import-name"></span>?</h2></div>
  <div class="k-dialog__body">
    <p class="js-k-import-what" style="margin-top:0"></p>
    <label class="k-sw" style="margin:6px 0 12px"><input type="checkbox" name="wipe" value="1" checked><i></i>{l s='Remove existing products, categories and pages first' d='Modules.Hbkit.Admin'}</label>
    <p class="k-note k-note--danger">{l s='Your current products, categories, brands, pages, menu and homepage sections will be deleted. Orders and customers stay.' d='Modules.Hbkit.Admin'}</p>
    <div class="k-f" style="margin-top:16px"><label class="k-lbl" for="k-im-confirm">{l s='Type IMPORT to confirm' d='Modules.Hbkit.Admin'}</label><input class="k-inp" id="k-im-confirm" name="confirm" autocomplete="off"></div>
  </div>
  <div class="k-dialog__foot"><button type="button" class="hbbo-btn hbbo-btn--ghost js-k-close">{l s='Cancel' d='Modules.Hbkit.Admin'}</button><button type="submit" class="hbbo-btn hbbo-btn--danger js-k-import-go" disabled>{l s='Import' d='Modules.Hbkit.Admin'} <span class="js-k-import-name2"></span></button></div>
</form>
