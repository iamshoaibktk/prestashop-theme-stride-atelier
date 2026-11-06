<?php
/**
 * Turns stored sections into template-ready arrays.
 *
 * @author    iamshoaibktk@gmail.com (WhatsApp: +923459016286)
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class HbkitRenderer
{
    /** Uploaded images live outside the module so module updates never remove them. */
    public const IMG_DIR = 'hbkit/';

    private $context;
    private $module;
    private $idLang;
    private $idShop;

    public function __construct(Context $context, Module $module)
    {
        $this->context = $context;
        $this->module = $module;
        $this->idLang = (int) $context->language->id;
        $this->idShop = (int) $context->shop->id;
    }

    public static function imageUrl(?string $file): string
    {
        if (!$file) {
            return '';
        }
        if (preg_match('#^https?://#', $file)) {
            return $file;
        }

        return Context::getContext()->link->getMediaLink(_PS_IMG_ . self::IMG_DIR . $file);
    }

    public static function imageSize(?string $file): array
    {
        $path = _PS_IMG_DIR_ . self::IMG_DIR . $file;
        if (!$file || !is_file($path)) {
            return [0, 0];
        }
        $s = @getimagesize($path);

        return $s ? [(int) $s[0], (int) $s[1]] : [0, 0];
    }

    public function getSections(): array
    {
        $out = [];
        foreach (HbkitSection::getAll($this->idShop, $this->idLang, true) as $row) {
            $type = (string) $row['type'];
            if (!HbkitTypes::get($type)) {
                continue;
            }
            $settings = HbkitTypes::settingsWithDefaults($type, json_decode((string) $row['settings'], true) ?: []);
            $section = [
                'id' => (int) $row['id_hbkit_section'],
                'type' => $type,
                'settings' => $settings,
                'title' => (string) $row['title'],
                'subtitle' => (string) $row['subtitle'],
                'content' => (string) $row['content'],
                'items' => [],
            ];
            $section['head_href'] = $this->resolveLink($settings['head_link'] ?? '');
            foreach (HbkitItem::getForSection($section['id'], $this->idLang) as $item) {
                $section['items'][] = $this->presentItem($item);
            }
            $method = 'build' . str_replace('_', '', ucwords($type, '_'));
            if (method_exists($this, $method)) {
                $section = $this->$method($section);
            }
            if ($section) {
                $out[] = $section;
            }
        }

        return $out;
    }

    private function presentItem(array $row): array
    {
        $data = json_decode((string) $row['data'], true) ?: [];
        $item = $data + [
            'id' => (int) $row['id_hbkit_item'],
            'title' => (string) $row['title'],
            'subtitle' => (string) $row['subtitle'],
            'text' => (string) $row['text'],
            'button' => (string) $row['button'],
        ];
        $item['href'] = $this->resolveLink($data['link'] ?? '', (string) $row['url']);
        foreach (['image', 'image_mobile'] as $key) {
            if (!empty($data[$key])) {
                $item[$key . '_url'] = self::imageUrl($data[$key]);
                [$item[$key . '_w'], $item[$key . '_h']] = self::imageSize($data[$key]);
            }
        }

        return $item;
    }

    /** Link spec: "category:3", "cms:4", "product:9", "manufacturer:2", "page:new-products"; otherwise the item's own URL. */
    public function resolveLink($spec, string $fallbackUrl = ''): string
    {
        $link = $this->context->link;
        $spec = (string) $spec;
        if ($spec !== '' && strpos($spec, ':') !== false) {
            [$kind, $value] = explode(':', $spec, 2);
            $id = (int) $value;
            switch ($kind) {
                case 'category':
                    return $id ? $link->getCategoryLink($id, null, $this->idLang) : '';
                case 'cms':
                    return $id ? $link->getCMSLink($id, null, null, $this->idLang) : '';
                case 'product':
                    return $id ? $link->getProductLink($id, null, null, null, $this->idLang) : '';
                case 'manufacturer':
                    return $id ? $link->getManufacturerLink($id, null, $this->idLang) : '';
                case 'page':
                    return $link->getPageLink(preg_replace('/[^a-z0-9_-]/i', '', $value), null, $this->idLang);
                case 'attribute':
                    return $id ? $this->filterLink($id) : '';
            }
        }

        return $fallbackUrl;
    }

    /**
     * The whole shop filtered to one size or colour (ps_facetedsearch "?q=Group-Value"),
     * for example the age stops of a size ruler.
     */
    private function filterLink(int $idAttribute): string
    {
        $attr = new ProductAttribute($idAttribute, $this->idLang);
        if (!Validate::isLoadedObject($attr)) {
            return '';
        }
        $group = new AttributeGroup((int) $attr->id_attribute_group, $this->idLang);
        $esc = function ($v) {
            return str_replace(['-', '/'], ['\\-', '\\/'], (string) $v);
        };
        $q = $esc($group->public_name ?: $group->name) . '-' . $esc($attr->name);
        $url = $this->context->link->getCategoryLink((int) Configuration::get('PS_HOME_CATEGORY'), null, $this->idLang);

        return $url . (strpos($url, '?') === false ? '?' : '&') . 'q=' . rawurlencode($q);
    }

    /* ------------------------------------------------------------------ */

    private function buildCategories(array $s): ?array
    {
        foreach ($s['items'] as $k => &$item) {
            $id = (int) ($item['id_category'] ?? 0);
            if ($id) {
                $cat = new Category($id, $this->idLang);
                if (!Validate::isLoadedObject($cat) || !$cat->active) {
                    unset($s['items'][$k]);
                    continue;
                }
                $item['title'] = $item['title'] ?: $cat->name;
                $item['href'] = $item['href'] ?: $this->context->link->getCategoryLink($cat, null, $this->idLang);
                if (empty($item['image_url']) && $cat->id_image) {
                    $item['image_url'] = $this->context->link->getCatImageLink($cat->link_rewrite, $cat->id_image, 'category_default');
                }
                $item['count'] = (int) $cat->getProducts($this->idLang, 1, 1, null, null, true);
            }
        }
        $s['items'] = array_values($s['items']);

        return $s['items'] ? $s : null;
    }

    private function buildProducts(array $s): ?array
    {
        $limit = max(1, min(24, (int) $s['settings']['limit']));
        $tabs = [];
        foreach ($s['items'] as $item) {
            $ids = $this->productIds((string) ($item['source'] ?? 'featured'), (int) ($item['id_category'] ?? 0), $limit);
            $products = $this->presentProducts($ids);
            if ($products) {
                $tabs[] = ['id' => 'hbk-tab-' . $item['id'], 'label' => $item['title'], 'products' => $products];
            }
        }
        if (!$tabs) {
            return null;
        }
        $s['tabs'] = $tabs;

        return $s;
    }

    private function buildBrands(array $s): ?array
    {
        $ids = [];
        if ($s['settings']['source'] === 'items') {
            foreach ($s['items'] as $item) {
                $ids[] = (int) ($item['id_manufacturer'] ?? 0);
            }
        } else {
            foreach (Manufacturer::getManufacturers(false, $this->idLang, true) as $m) {
                $ids[] = (int) $m['id_manufacturer'];
            }
        }
        $brands = [];
        foreach (array_filter($ids) as $id) {
            $m = new Manufacturer($id, $this->idLang);
            if (!Validate::isLoadedObject($m) || !$m->active) {
                continue;
            }
            $img = _PS_MANU_IMG_DIR_ . $id . '.jpg';
            $brands[] = [
                'name' => $m->name,
                'href' => $this->context->link->getManufacturerLink($m, null, $this->idLang),
                'image' => is_file($img) ? $this->context->link->getMediaLink(_THEME_MANU_DIR_ . $id . '.jpg') : '',
            ];
        }
        $s['brands'] = $brands;

        return $brands ? $s : null;
    }

    private function buildCountdown(array $s): ?array
    {
        $idProduct = (int) ($s['settings']['id_product'] ?? 0);
        $products = $idProduct ? $this->presentProducts([$idProduct]) : [];
        $s['product'] = $products[0] ?? null;
        $s['date_to'] = (string) ($s['settings']['date_to'] ?? '');
        if ($s['date_to'] === '' || strtotime($s['date_to']) < time()) {
            // Keep the demo alive: a rolling deadline at the end of the coming Sunday.
            $s['date_to'] = date('Y-m-d 23:59:59', strtotime('next sunday'));
        }
        $s['image_url'] = self::imageUrl($s['settings']['image'] ?? '');
        $s['href'] = $this->resolveLink($s['settings']['link'] ?? '') ?: ($s['product']['url'] ?? '');

        return $s;
    }

    private function buildText(array $s): ?array
    {
        $s['href'] = $this->resolveLink($s['settings']['link'] ?? '');

        return $s;
    }

    private function buildNewsletter(array $s): ?array
    {
        $s['image_url'] = self::imageUrl($s['settings']['image'] ?? '');
        $s['has_module'] = Module::isEnabled('ps_emailsubscription');

        return $s;
    }

    private function buildHero(array $s): ?array
    {
        if (!$s['items']) {
            return null;
        }
        // Optional card on the first slide: the section texts and one product.
        $idProduct = (int) ($s['settings']['id_product'] ?? 0);
        $products = $idProduct ? $this->presentProducts([$idProduct]) : [];
        $s['card'] = null;
        if ($s['title'] !== '' || $s['subtitle'] !== '' || $products) {
            $s['card'] = ['label' => $s['subtitle'], 'value' => $s['title'], 'text' => strip_tags($s['content']), 'product' => $products[0] ?? null, 'tag' => ''];
            if ($products && ($card = array_filter(array_map('intval', explode(',', (string) Configuration::get('HBKIT_CARD_FEATURE')))))) {
                $s['card']['tag'] = $this->module->getFeatureValues($idProduct)[reset($card)] ?? '';
            }
        }

        return $s;
    }

    /* ------------------------------------------------------------------ */

    private function productIds(string $source, int $idCategory, int $limit): array
    {
        $rows = [];
        switch ($source) {
            case 'new':
                $rows = Product::getNewProducts($this->idLang, 0, $limit) ?: [];
                break;
            case 'specials':
                $rows = Product::getPricesDrop($this->idLang, 0, $limit) ?: [];
                break;
            case 'bestsellers':
                $rows = ProductSale::getBestSalesLight($this->idLang, 0, $limit) ?: [];
                if (!$rows) {
                    // A fresh shop has no sales yet: fall back to the home category.
                    return $this->productIds('featured', 0, $limit);
                }
                break;
            case 'category':
            case 'featured':
            default:
                $idCategory = $idCategory ?: (int) Configuration::get('HOME_FEATURED_CAT') ?: (int) Configuration::get('PS_HOME_CATEGORY');
                $cat = new Category($idCategory, $this->idLang);
                $rows = Validate::isLoadedObject($cat) ? ($cat->getProducts($this->idLang, 1, $limit, 'position', 'asc') ?: []) : [];
        }

        return array_slice(array_map(function ($r) { return (int) $r['id_product']; }, $rows), 0, $limit);
    }

    public function presentProducts(array $ids): array
    {
        if (!$ids) {
            return [];
        }
        $assembler = new ProductAssembler($this->context);
        $factory = new ProductPresenterFactory($this->context);
        $presenter = new ProductListingPresenter(
            new ImageRetriever($this->context->link),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );
        $settings = $factory->getPresentationSettings();
        $raw = $assembler->assembleProducts(array_map(function ($id) { return ['id_product' => $id]; }, $ids));
        $out = [];
        foreach ($raw as $p) {
            if (empty($p['active']) || empty($p['name'])) {
                continue;
            }
            $out[] = $presenter->present($settings, $p, $this->context->language);
        }

        return $out;
    }
}
