<?php
/**
 * Card quick add: the choices a product card offers before adding to the cart.
 *
 * Returns the first option group that is not a colour (usually the size), one button per value, each with the
 * combination that keeps the product's default colour. A product with only colours, or none, returns no group,
 * and the card adds its default combination straight away.
 *
 * @author    iamshoaibktk@gmail.com (WhatsApp: +923459016286)
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HbkitOptionsModuleFrontController extends ModuleFrontController
{
    /** @var Hbkit */
    public $module;

    public $ssl = true;

    public function initContent()
    {
        parent::initContent();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $this->ajaxRender(json_encode($this->options((int) Tools::getValue('id_product'))));
        exit;
    }

    private function options(int $idProduct): array
    {
        $idLang = (int) $this->context->language->id;
        $product = new Product($idProduct, false, $idLang);
        if (!Validate::isLoadedObject($product) || !$product->active || !$product->available_for_order) {
            return ['ok' => false];
        }
        $default = (int) Product::getDefaultAttribute($idProduct);
        $rows = $product->getAttributesGroups($idLang);
        if (!$default || !$rows) {
            return ['ok' => true, 'default' => $default, 'group' => null];
        }

        // Attributes of each combination, and which groups are colours.
        $combos = [];
        $groups = [];
        foreach ($rows as $r) {
            $ipa = (int) $r['id_product_attribute'];
            $g = (int) $r['id_attribute_group'];
            $combos[$ipa]['attrs'][$g] = (int) $r['id_attribute'];
            $combos[$ipa]['qty'] = (int) $r['quantity'];
            if (!isset($groups[$g])) {
                $groups[$g] = ['name' => (string) ($r['public_group_name'] ?: $r['group_name']), 'color' => (bool) $r['is_color_group'], 'values' => []];
            }
            $groups[$g]['values'][(int) $r['id_attribute']] = (string) $r['attribute_name'];
        }
        $pick = null;
        foreach ($groups as $g => $info) {
            if (!$info['color']) {
                $pick = $g;
                break;
            }
        }
        if ($pick === null || empty($combos[$default])) {
            return ['ok' => true, 'default' => $default, 'group' => null];
        }

        // For each value of the picked group: the combination that matches the default in every other group.
        $keep = $combos[$default]['attrs'];
        unset($keep[$pick]);
        $orderOk = Product::isAvailableWhenOutOfStock((int) $product->out_of_stock) || !Configuration::get('PS_STOCK_MANAGEMENT');
        $values = [];
        foreach ($groups[$pick]['values'] as $idAttribute => $label) {
            $match = 0;
            foreach ($combos as $ipa => $c) {
                if (($c['attrs'][$pick] ?? 0) !== $idAttribute) {
                    continue;
                }
                $same = true;
                foreach ($keep as $g => $a) {
                    if (($c['attrs'][$g] ?? 0) !== $a) {
                        $same = false;
                        break;
                    }
                }
                if ($same) {
                    $match = $ipa;
                    break;
                }
            }
            if ($match) {
                $values[] = ['label' => $label, 'ipa' => $match, 'available' => $orderOk || $combos[$match]['qty'] > 0];
            }
        }

        return ['ok' => true, 'default' => $default, 'group' => $values ? ['name' => $groups[$pick]['name'], 'values' => $values] : null];
    }
}
