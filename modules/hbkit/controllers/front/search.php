<?php
/**
 * Search panel: returns the first results for a query as HTML (JSON).
 *
 * @author    iamshoaibktk@gmail.com (WhatsApp: +923459016286)
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HbkitSearchModuleFrontController extends ModuleFrontController
{
    /** @var Hbkit */
    public $module;

    public $ssl = true;

    public function initContent()
    {
        parent::initContent();
        $query = Tools::substr((string) Tools::getValue('s', ''), 0, 64);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $this->ajaxRender(json_encode($this->module->renderSearchResults($query)));
        exit;
    }
}
