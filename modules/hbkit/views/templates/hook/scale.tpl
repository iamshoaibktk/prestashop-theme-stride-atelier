{**
 * Product page: a range on a scale, for example a temperature rating (HBKIT_SCALE_FEATURE).
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
<div class="hbk-scale">
  <p class="hbk-scale__head"><span class="hbk-scale__name">{$hbk_scale.name}</span><span class="hbk-ftag">{$hbk_scale.value}</span></p>
  <div class="hbk-scale__bar" role="img" aria-label="{$hbk_scale.name}: {$hbk_scale.value}"><span class="hbk-scale__fill" style="left:{$hbk_scale.left}%;width:{$hbk_scale.width}%"></span></div>
  <ol class="hbk-scale__ticks" aria-hidden="true">{foreach $hbk_scale.ticks as $t}<li>{$t}{if $t@last && $hbk_scale.unit} {$hbk_scale.unit}{/if}</li>{/foreach}</ol>
</div>
