<?php
/**
 * Side cart: returns the rendered cart lines as JSON for the drawer.
 *
 * @author    iamshoaibktk@gmail.com (WhatsApp: +923459016286)
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HbkitCartModuleFrontController extends ModuleFrontController
{
    /** @var Hbkit */
    public $module;

    public $ssl = true;

    public function initContent()
    {
        parent::initContent();
        $view = $this->module->getCartView();
        $this->context->smarty->assign('hbk', $view);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $this->ajaxRender(json_encode([
            'count' => (int) $view['cart']['products_count'],
            'html' => $this->module->fetch('module:hbkit/views/templates/hook/drawer-body.tpl'),
        ]));
        exit;
    }
}
