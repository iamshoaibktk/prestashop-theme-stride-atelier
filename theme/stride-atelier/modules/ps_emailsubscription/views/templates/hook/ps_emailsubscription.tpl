{**
 * Stride Atelier: the newsletter ("First in line for new drops") as the first footer column, with the shop's own title and text
 * (Theme Kit texts HBKIT_TXT_NEWS_TITLE / HBKIT_TXT_NEWS_TEXT).
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 *}
<section class="ps-emailsubscription hbk-news" id="emailsubscription_anchor_{$hookName}">
  <p class="footer-block__title hbk-news__title">{if !empty($modules.hbkit.texts.news_title)}{$modules.hbkit.texts.news_title}{else}{l s='Get our latest news and special sales' d='Shop.Theme.Global'}{/if}</p>
  {if !empty($modules.hbkit.texts.news_text)}<p class="hbk-news__text">{$modules.hbkit.texts.news_text}</p>{/if}
  <form action="{$urls.current_url}#emailsubscription_anchor_{$hookName}" method="post">
    {if $msg}
      <div class="alert {if $nw_error}alert-danger{else}alert-success{/if} mb-2" role="alert" tabindex="0">{$msg}</div>
    {/if}
    <div class="hbk-news__row">
      <label for="emailsubscription_input" class="visually-hidden">{l s='Your email address' d='Modules.Emailsubscription.Shop'}</label>
      <input class="form-control" type="email" name="email" value="{$value}" placeholder="{l s='Your email address' d='Modules.Emailsubscription.Shop'}" id="emailsubscription_input" autocomplete="email" required>
      <button class="btn btn-primary" type="submit" name="submitNewsletter" value="1">{l s='Sign up' d='Modules.Hbkit.Shop'}</button>
    </div>
    {capture name="display_gdpr_consent"}{hook h='displayGDPRConsent' id_module=$id_module}{/capture}
    {if $smarty.capture.display_gdpr_consent}<div class="hbk-news__consent">{$smarty.capture.display_gdpr_consent nofilter}</div>{/if}
    {if $conditions}<p class="hbk-news__help">{$conditions}</p>{/if}
    {hook h='displayNewsletterRegistration'}
    <input type="hidden" value="{$hookName}" name="blockHookName">
    <input type="hidden" name="action" value="0">
  </form>
</section>
