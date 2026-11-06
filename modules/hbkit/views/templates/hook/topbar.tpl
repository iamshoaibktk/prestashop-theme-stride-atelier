<div class="hbk-topbar" role="region" aria-label="{l s='Announcements' d='Modules.Hbkit.Shop'}">
  <div class="container hbk-topbar__inner">
    {foreach $hbkit_topbar as $msg}
      <span class="hbk-topbar__msg">{if $hbkit_topbar_link && $msg@first}<a href="{$hbkit_topbar_link}">{$msg}</a>{else}{$msg}{/if}</span>
    {/foreach}
  </div>
</div>
