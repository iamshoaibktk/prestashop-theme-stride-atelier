{**
 * Theme Kit > Homepage sections: drag to reorder, switch to show or hide (both save at once).
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
<div class="hbbo-main__head">
  <div>
    <h2 class="hbbo-main__title">{l s='Homepage sections' d='Modules.Hbkit.Admin'}</h2>
    <p class="hbbo-main__intro">{l s='The homepage, top to bottom. Drag a section to move it; the switch hides it without deleting anything.' d='Modules.Hbkit.Admin'}</p>
  </div>
  <div class="hbbo-head__actions">
    <a class="hbbo-btn hbbo-btn--ghost" href="{$hbk_urls.front}" target="_blank" rel="noopener"><span class="material-icons" aria-hidden="true">visibility</span>{l s='View homepage' d='Modules.Hbkit.Admin'}</a>
    <button type="button" class="hbbo-btn hbbo-btn--primary js-k-open" data-layer="add"><span class="material-icons" aria-hidden="true">add</span>{l s='Add section' d='Modules.Hbkit.Admin'}</button>
  </div>
</div>

{if !$hbk_sections}
  <section class="hbbo-card" style="margin-top:20px"><div class="k-card__body"><p class="k-help">{l s='No sections yet. Add one, or import a demo from Demo import.' d='Modules.Hbkit.Admin'}</p></div></section>
{else}
  <ul class="k-items hbbo-card js-k-sections" style="margin-top:20px" data-ajax="{$hbk_ajax}" data-saved="{l s='Saved' d='Modules.Hbkit.Admin'}" data-error="{l s='Could not save. Reload the page and try again.' d='Modules.Hbkit.Admin'}">
    {foreach $hbk_sections as $row}
      <li class="k-it js-k-section{if !$row.active} is-off{/if}" data-id="{$row.id}">
        <span class="k-grip" aria-hidden="true"><span class="material-icons">drag_indicator</span></span>
        {if $row.thumb}<img class="k-thumb" src="{$row.thumb}" alt="" loading="lazy">{else}<span class="k-thumb k-thumb--empty"><span class="material-icons" aria-hidden="true">view_agenda</span></span>{/if}
        <span style="min-width:0"><a class="k-it__name" href="{$row.edit}">{$row.title}</a><span class="k-it__meta">{$row.meta}</span></span>
        <label class="k-sw" title="{l s='Show on the homepage' d='Modules.Hbkit.Admin'}"><input type="checkbox" class="js-k-section-toggle"{if $row.active} checked{/if}><i></i><span class="sr-only">{l s='Show on the homepage' d='Modules.Hbkit.Admin'}: {$row.title}</span></label>
        <a class="hbbo-btn hbbo-btn--small" href="{$row.edit}">{l s='Edit' d='Modules.Hbkit.Admin'}</a>
        <div class="k-menu">
          <button type="button" class="hbbo-icon js-k-menu" aria-haspopup="true" aria-expanded="false" aria-label="{l s='More actions' d='Modules.Hbkit.Admin'}"><span class="material-icons">more_horiz</span></button>
          <div class="k-menu__list" hidden>
            <a href="{$row.duplicate}"><span class="material-icons" aria-hidden="true">content_copy</span>{l s='Duplicate' d='Modules.Hbkit.Admin'}</a>
            <a href="{$row.delete}" class="is-danger js-hbk-confirm" data-confirm="{l s='Delete this section? This cannot be undone.' d='Modules.Hbkit.Admin'}"><span class="material-icons" aria-hidden="true">delete_outline</span>{l s='Delete' d='Modules.Hbkit.Admin'}</a>
          </div>
        </div>
      </li>
    {/foreach}
  </ul>
  <p class="k-help" style="margin-top:10px">{l s='Order and visibility save straight away. "More actions" holds Duplicate and Delete.' d='Modules.Hbkit.Admin'}</p>
{/if}

<div class="hbbo-layer-shade js-k-close" data-layer="add" hidden></div>
<form class="k-dialog k-dialog--wide" method="post" action="{$hbk_urls.sections}" data-layer="add" role="dialog" aria-modal="true" aria-labelledby="k-add-t" hidden>
  <input type="hidden" name="hbkit_action" value="add_section">
  <div class="k-dialog__head"><h2 id="k-add-t">{l s='Add a section' d='Modules.Hbkit.Admin'}</h2><p class="k-help">{l s='Pick what it shows. It is added hidden at the bottom, so nothing changes on the shop until you switch it on.' d='Modules.Hbkit.Admin'}</p></div>
  <div class="k-dialog__body">
    <div class="hbbo-choices" role="radiogroup" aria-labelledby="k-add-t" style="grid-template-columns:repeat(auto-fill,minmax(150px,1fr));margin:0">
      {foreach $hbk_add_types as $tp}
        <label class="hbbo-choice"><input type="radio" name="type" value="{$tp.key}"{if $tp@first} checked{/if}>
          <span class="hbbo-choice__pic">{$tp.svg nofilter}</span><span class="hbbo-choice__name">{$tp.name}</span><span class="hbbo-choice__help">{$tp.blurb}</span>
          <span class="hbbo-choice__tick material-icons" aria-hidden="true">check_circle</span></label>
      {/foreach}
    </div>
  </div>
  <div class="k-dialog__foot"><button type="button" class="hbbo-btn hbbo-btn--ghost js-k-close">{l s='Cancel' d='Modules.Hbkit.Admin'}</button><button type="submit" class="hbbo-btn hbbo-btn--primary">{l s='Add section' d='Modules.Hbkit.Admin'}</button></div>
</form>
<div class="hbbo-toast js-k-toast" role="status" aria-live="polite" hidden></div>
