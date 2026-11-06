{**
 * Theme Kit back office shell: header, menu on the left, the chosen page on the right.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
<div class="hbbo">
  {include file='./author.tpl'}

  <div class="hbbo-head">
    <div>
      <h1 class="hbbo-head__title">{l s='Theme Kit' d='Modules.Hbkit.Admin'}{if $hbk_demo} <span class="hbbo-head__demo">{$hbk_demo|escape:'html':'UTF-8'}</span>{/if}</h1>
      <p class="hbbo-head__lead">{l s='Choose a page on the left, change what you need and press Save. Changes show on the shop straight away.' d='Modules.Hbkit.Admin'}</p>
    </div>
    <div class="hbbo-head__actions">
      <a class="hbbo-btn hbbo-btn--ghost" href="{$hbk_urls.front}" target="_blank" rel="noopener"><i class="material-icons" aria-hidden="true">visibility</i>{l s='View shop' d='Modules.Hbkit.Admin'}</a>
      {if $hbk_view == 'options'}
        <button type="submit" form="hbbo-design" class="hbbo-btn hbbo-btn--primary">{l s='Save' d='Modules.Hbkit.Admin'}</button>
      {/if}
    </div>
  </div>

  <div class="hbbo-body">
    <nav class="hbbo-nav" aria-label="{l s='Theme Kit' d='Modules.Hbkit.Admin'}">
      <div class="hbbo-card hbbo-nav__card">
        <p class="hbbo-nav__label">{l s='Homepage' d='Modules.Hbkit.Admin'}</p>
        <ul class="hbbo-nav__list">
          <li class="hbbo-nav__item{if $hbk_view == 'sections' || $hbk_view == 'edit'} is-current{/if}"><i class="material-icons" aria-hidden="true">dashboard_customize</i><a class="hbbo-nav__link" href="{$hbk_urls.sections}">{l s='Homepage sections' d='Modules.Hbkit.Admin'}</a></li>
          <li class="hbbo-nav__item{if $hbk_view == 'topbar'} is-current{/if}"><i class="material-icons" aria-hidden="true">campaign</i><a class="hbbo-nav__link" href="{$hbk_urls.topbar}">{l s='Top bar' d='Modules.Hbkit.Admin'}</a></li>
        </ul>
        <p class="hbbo-nav__label">{l s='Design' d='Modules.Hbkit.Admin'}</p>
        <ul class="hbbo-nav__list">
          {foreach $hbk_design_nav as $gkey => $g}
            <li class="hbbo-nav__item{if $hbk_view == 'options' && $hbk_group_key == $gkey} is-current{/if}"><i class="material-icons" aria-hidden="true">{$g.icon}</i><a class="hbbo-nav__link" href="{$hbk_urls.design[$gkey]}">{$g.title|escape:'html':'UTF-8'}</a></li>
          {/foreach}
        </ul>
        <p class="hbbo-nav__label">{l s='Tools' d='Modules.Hbkit.Admin'}</p>
        <ul class="hbbo-nav__list">
          <li class="hbbo-nav__item{if $hbk_view == 'import'} is-current{/if}"><i class="material-icons" aria-hidden="true">download</i><a class="hbbo-nav__link" href="{$hbk_urls.import}">{l s='Demo import' d='Modules.Hbkit.Admin'}</a></li>
        </ul>
      </div>
      <p class="hbbo-nav__hint">Hummingbird Theme Kit · <a href="https://www.fiverr.com/shoaibktk" target="_blank" rel="noopener">iamshoaibktk@gmail.com</a></p>
    </nav>

    <main class="hbbo-main">
      {$hbk_body nofilter}
    </main>
  </div>
</div>
