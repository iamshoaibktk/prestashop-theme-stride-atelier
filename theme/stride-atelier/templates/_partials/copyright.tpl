{**
 * Stride Atelier: shop name and address instead of the software credit.
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
{extends file='parent:_partials/copyright.tpl'}
{block name='copyright_link'}
  <span>&copy; {'Y'|date} {$shop.name}{if !empty($shop.address.address1)}, {$shop.address.address1}, {$shop.address.city}{/if}</span>
{/block}
