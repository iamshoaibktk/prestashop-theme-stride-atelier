<div class="hbk-products hbk-products--{$s.layout}" style="--hbk-cols:{$s.columns|intval}">
  {if $section.tabs|count > 1}
    <div class="hbk-tabs" role="tablist">
      {foreach $section.tabs as $tab}
        <button type="button" role="tab" class="hbk-tabs__btn js-hbk-tab{if $tab@first} is-active{/if}" aria-selected="{if $tab@first}true{else}false{/if}" aria-controls="{$tab.id}">{$tab.label}</button>
      {/foreach}
    </div>
  {/if}
  {foreach $section.tabs as $tab}
    <div id="{$tab.id}" class="hbk-tabs__panel{if $tab@first} is-active{/if}" role="tabpanel" {if !$tab@first}hidden{/if}>
      {include file='catalog/_partials/productlist.tpl' products=$tab.products}
    </div>
  {/foreach}
</div>
