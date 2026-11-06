{**
 * Search panel results (returned by the "search" controller).
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
{if $hbk_sr.products}
  <ul class="hbk-sr">
    {foreach $hbk_sr.products as $p}
      <li class="hbk-sr__item">
        <a class="hbk-sr__link" href="{$p.url}">
          <span class="hbk-sr__img">{if $p.cover}<img src="{$p.cover.bySize.home_default.url}" alt="" loading="lazy" width="{$p.cover.bySize.home_default.width}" height="{$p.cover.bySize.home_default.height}">{/if}</span>
          <span class="hbk-sr__body">
            <span class="hbk-sr__row"><span class="hbk-sr__name">{$p.name}</span>{if $p.show_price}<span class="hbk-sr__price">{$p.price}</span>{/if}</span>
            {if $p.hbk_tag}<span class="hbk-ftag">{$p.hbk_tag}</span>{/if}
          </span>
        </a>
      </li>
    {/foreach}
  </ul>
  <p class="hbk-sr__all"><a href="{$hbk_sr.all_url}">{l s='See all %count% results for “%query%”' sprintf=['%count%' => $hbk_sr.total, '%query%' => $hbk_sr.query] d='Modules.Hbkit.Shop'}</a></p>
{else}
  <p class="hbk-sr__none">{l s='Nothing found for “%query%”. Try another word, like a colour or a fabric.' sprintf=['%query%' => $hbk_sr.query] d='Modules.Hbkit.Shop'}</p>
{/if}
