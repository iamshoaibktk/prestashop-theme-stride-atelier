{* Free delivery progress bar. @author iamshoaibktk@gmail.com @license AFL-3.0 *}
{if $hbk.bar}
  <div class="hbk-freebar{if $hbk.bar.done} is-done{/if}">
    <p class="hbk-freebar__text">
      {if $hbk.bar.done}
        <i class="material-icons" aria-hidden="true">local_shipping</i> {l s='Your order ships free.' d='Modules.Hbkit.Shop'}
      {else}
        {l s='Add %amount% more for free delivery.' sprintf=['%amount%' => $hbk.bar.left] d='Modules.Hbkit.Shop'}
      {/if}
    </p>
    <div class="hbk-freebar__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{$hbk.bar.percent|intval}"><span style="width:{$hbk.bar.percent|intval}%"></span></div>
  </div>
{/if}
