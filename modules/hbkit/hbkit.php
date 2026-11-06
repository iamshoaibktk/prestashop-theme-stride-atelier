<?php
/**
 * Hummingbird Theme Kit
 *
 * Shared engine for the Hummingbird demo themes: design tokens (colours, fonts,
 * radius, header style) as CSS variables, a top bar, a section-based homepage
 * builder and a one-click demo importer. Child themes stay thin: they only
 * carry CSS, fonts, theme.yml and a demo pack.
 *
 * @author    iamshoaibktk@gmail.com (WhatsApp: +923459016286)
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/HbkitSection.php';
require_once __DIR__ . '/classes/HbkitItem.php';
require_once __DIR__ . '/classes/HbkitTypes.php';
require_once __DIR__ . '/classes/HbkitRenderer.php';
require_once __DIR__ . '/classes/HbkitImporter.php';
require_once __DIR__ . '/classes/HbkitPreviews.php';
require_once __DIR__ . '/classes/HbkitForm.php';

class Hbkit extends Module
{
    /** Default design tokens. A demo pack overrides them on import; the merchant edits them in Theme options. */
    public const DEFAULT_TOKENS = [
        'primary' => '#1f2937',
        'primary_contrast' => '#ffffff',
        'accent' => '#c08a3e',
        'bg' => '#ffffff',
        'surface' => '#f6f4f0',
        'text' => '#1f2937',
        'muted' => '#6b7280',
        'border' => '#e5e7eb',
        'sale' => '#b42318',
        'dark' => '#111827',
        'dark_text' => '#f9fafb',
        'field_border' => '#c4c7cc',
        'font_heading' => 'system-ui',
        'font_body' => 'system-ui',
        'heading_weight' => '600',
        'heading_transform' => 'none',
        'heading_spacing' => '0',
        'radius' => '6',
        'button_radius' => '6',
        'button_transform' => 'none',
        'field_height' => '46',
        'header_style' => 'classic',
        'header_sticky' => 'always',
        'footer_style' => 'dark',
        'card_style' => 'plain',
        'cart_style' => 'modal',
        'free_bar' => '1',
        'cart_layout' => 'classic',
        'listing_layout' => 'left',
        'category_header' => 'standard',
        'grid_columns' => '3',
        'product_layout' => 'classic',
        'product_thumbs' => 'bottom',
        'product_tabs' => 'accordion',
        'search_style' => 'bar',
        'search_trigger' => 'word',
        'cart_word' => 'cart',
    ];

    public const TOKEN_CHOICES = [
        'heading_transform' => ['none', 'uppercase'],
        'button_transform' => ['none', 'uppercase'],
        'header_style' => ['classic', 'centered', 'dark', 'transparent'],
        'header_sticky' => ['always', 'up', 'off'],
        'footer_style' => ['dark', 'light', 'primary'],
        'card_style' => ['plain', 'bordered', 'shadow', 'minimal', 'overlay', 'boxed', 'editorial', 'framed', 'compact', 'label'],
        'cart_style' => ['modal', 'drawer', 'toast', 'page'],
        'free_bar' => ['1', '0'],
        'cart_layout' => ['classic', 'cards', 'lined'],
        'listing_layout' => ['left', 'right', 'drawer'],
        'category_header' => ['standard', 'banner', 'split', 'minimal'],
        'grid_columns' => ['2', '3', '4'],
        'product_layout' => ['classic', 'wide', 'boxed', 'reverse'],
        'product_thumbs' => ['bottom', 'left'],
        'product_tabs' => ['accordion', 'tabs', 'open'],
        'search_style' => ['bar', 'panel', 'field'],
        'search_trigger' => ['word', 'icon'],
        'cart_word' => ['cart', 'bag'],
    ];

    /** Body class prefix of each layout token: hbk-<prefix>-<value>. */
    public const BODY_CLASSES = [
        'header_style' => 'header', 'header_sticky' => 'hsticky', 'footer_style' => 'footer', 'card_style' => 'cards',
        'cart_style' => 'cart', 'cart_layout' => 'cartpage', 'listing_layout' => 'listing',
        'category_header' => 'cathead', 'grid_columns' => 'cols', 'product_layout' => 'pp',
        'product_thumbs' => 'thumbs', 'product_tabs' => 'ptabs', 'search_style' => 'search', 'search_trigger' => 'searchtrigger',
    ];

    /** Shop texts a demo can set per language (Configuration, multilang). */
    public const TEXT_KEYS = [
        'HBKIT_TXT_NEWS_TITLE' => 'news_title',
        'HBKIT_TXT_NEWS_TEXT' => 'news_text',
        'HBKIT_TXT_PRODUCT_NOTE' => 'product_note',
        'HBKIT_TXT_DELIVERY' => 'delivery',
        'HBKIT_TXT_SEARCH_PLACEHOLDER' => 'search_placeholder',
        'HBKIT_TXT_SEARCH_POPULAR' => 'search_popular',
        'HBKIT_TXT_NEXT_CATEGORY' => 'next_category',
        'HBKIT_TXT_SIZE_HINT' => 'size_hint',
    ];

    public function __construct()
    {
        $this->name = 'hbkit';
        $this->tab = 'front_office_features';
        $this->version = '1.1.1';
        $this->author = 'iamshoaibktk@gmail.com';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '9.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Hummingbird Theme Kit', [], 'Modules.Hbkit.Admin');
        $this->description = $this->trans('Theme options, homepage sections and one-click demo import for Hummingbird child themes.', [], 'Modules.Hbkit.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module?', [], 'Modules.Hbkit.Admin');
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function install(): bool
    {
        return parent::install()
            && $this->installDb()
            && $this->installTab()
            && Configuration::updateValue('HBKIT_TOKENS', json_encode(self::DEFAULT_TOKENS))
            && Configuration::updateValue('HBKIT_TOPBAR_ACTIVE', 0)
            && $this->ensureHooks();
    }

    /** Hooks the kit needs. A theme switch can drop some of them, so they are re-checked after enable, import and in the back office. */
    public const HOOKS = [
        'displayHeader',
        'displayBanner',
        'displayHome',
        'displayTop',
        'displayBeforeBodyClosingTag',
        'displayShoppingCart',
        'displayProductListReviews',
        'displayProductAdditionalInfo',
        'displayProductPriceBlock',
        'displayFooterCategory',
        'actionFrontControllerSetMedia',
        'actionFrontControllerSetVariables',
        'actionObjectShopUpdateAfter',
        'actionOutputHTMLBefore',
    ];

    public function ensureHooks(): bool
    {
        $ok = true;
        foreach (self::HOOKS as $hook) {
            if (!$this->isRegisteredInHook($hook)) {
                $ok = $this->registerHook($hook) && $ok;
            }
        }

        return $ok;
    }

    /**
     * Structured data repair for PrestaShop 9.2. The core builds the JSON-LD in PHP; for product features it uses
     * preg_replace('/\R+/', ...) without the "u" flag, so "\R" also matches the byte 0x85, the second half of letters such
     * as "ą" (Polish) or "х" (Russian). The letter is cut, json_encode fails and the block is printed empty, so search
     * engines see no product, price or availability on that page. Here an empty block is rebuilt from the same data, with
     * the features read as UTF-8. Pages without an empty block are not touched.
     */
    public function hookActionOutputHTMLBefore(array $params)
    {
        $empty = '<script type="application/ld+json"></script>';
        if (!isset($params['html']) || !is_string($params['html']) || strpos($params['html'], $empty) === false) {
            return;
        }
        $controller = $this->context->controller;
        if (!$controller || !method_exists($controller, 'getStructuredData')) {
            return;
        }
        try {
            $original = $controller->getStructuredData();
        } catch (\Throwable $e) {
            return;
        }
        $fixed = $original;
        if (isset($fixed['product'])) {
            $product = $this->context->smarty->getTemplateVars('product');
            $props = [];
            foreach ((isset($product['grouped_features']) ? $product['grouped_features'] : []) as $feature) {
                if (!isset($feature['name'], $feature['value'])) {
                    continue;
                }
                $name = trim((string) $feature['name']);
                $value = preg_replace('/\R+/u', ', ', trim((string) $feature['value']));
                if ($name === '' || $value === null || $value === '') {
                    continue;
                }
                $props[] = ['@type' => 'PropertyValue', 'name' => $name, 'value' => $value];
            }
            if ($props) {
                $fixed['product']['additionalProperty'] = $props;
            } else {
                unset($fixed['product']['additionalProperty']);
            }
        }
        // The blocks are printed in this order; each one whose encoding failed is an empty block, in the same order.
        foreach ($original as $key => $element) {
            if (json_encode($element, 320) !== false || !isset($fixed[$key])) {
                continue;
            }
            $json = json_encode($fixed[$key], 320 | JSON_INVALID_UTF8_SUBSTITUTE);
            $pos = strpos($params['html'], $empty);
            if ($json === false || $pos === false) {
                break;
            }
            $params['html'] = substr_replace($params['html'], '<script type="application/ld+json">' . $json . '</script>', $pos, strlen($empty));
        }
    }

    /** Fired by the theme manager when the shop's theme changes. */
    public function hookActionObjectShopUpdateAfter()
    {
        $this->ensureHooks();
    }

    public function uninstall(): bool
    {
        $db = Db::getInstance();
        foreach (['hbkit_item_lang', 'hbkit_item', 'hbkit_section_lang', 'hbkit_section'] as $table) {
            $db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $table . '`');
        }
        foreach (['HBKIT_TOKENS', 'HBKIT_TOPBAR_ACTIVE', 'HBKIT_TOPBAR_TEXT', 'HBKIT_TOPBAR_LINK', 'HBKIT_DEMO', 'HBKIT_CARD_FEATURE', 'HBKIT_PRODUCT_FEATURES', 'HBKIT_FEATURES_NAMED', 'HBKIT_QUICK_FACET', 'HBKIT_QUICK_FEATURE', 'HBKIT_SIZE_GUIDE', 'HBKIT_SCALE_FEATURE', 'HBKIT_SCALE_RANGE', 'HBKIT_NEXT_CATEGORY', 'HBKIT_FIT_FEATURE', 'HBKIT_STAMP_FEATURE'] as $key) {
            Configuration::deleteByName($key);
        }
        foreach (array_keys(self::TEXT_KEYS) as $key) {
            Configuration::deleteByName($key);
        }
        $idTab = (int) Tab::getIdFromClassName('AdminHbkit');
        if ($idTab) {
            (new Tab($idTab))->delete();
        }

        return parent::uninstall();
    }

    private function installDb(): bool
    {
        $engine = _MYSQL_ENGINE_;
        $p = _DB_PREFIX_;
        $sql = [
            "CREATE TABLE IF NOT EXISTS `{$p}hbkit_section` (
                `id_hbkit_section` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_shop` INT UNSIGNED NOT NULL DEFAULT 1,
                `type` VARCHAR(32) NOT NULL,
                `position` INT UNSIGNED NOT NULL DEFAULT 0,
                `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                `settings` MEDIUMTEXT NULL,
                PRIMARY KEY (`id_hbkit_section`), KEY `shop_pos` (`id_shop`, `position`)
            ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS `{$p}hbkit_section_lang` (
                `id_hbkit_section` INT UNSIGNED NOT NULL,
                `id_lang` INT UNSIGNED NOT NULL,
                `title` VARCHAR(255) NULL,
                `subtitle` VARCHAR(255) NULL,
                `content` MEDIUMTEXT NULL,
                PRIMARY KEY (`id_hbkit_section`, `id_lang`)
            ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS `{$p}hbkit_item` (
                `id_hbkit_item` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_hbkit_section` INT UNSIGNED NOT NULL,
                `position` INT UNSIGNED NOT NULL DEFAULT 0,
                `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                `data` TEXT NULL,
                PRIMARY KEY (`id_hbkit_item`), KEY `section` (`id_hbkit_section`, `position`)
            ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS `{$p}hbkit_item_lang` (
                `id_hbkit_item` INT UNSIGNED NOT NULL,
                `id_lang` INT UNSIGNED NOT NULL,
                `title` VARCHAR(255) NULL,
                `subtitle` VARCHAR(255) NULL,
                `text` TEXT NULL,
                `button` VARCHAR(128) NULL,
                `url` VARCHAR(512) NULL,
                PRIMARY KEY (`id_hbkit_item`, `id_lang`)
            ) ENGINE={$engine} DEFAULT CHARSET=utf8mb4",
        ];
        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    private function installTab(): bool
    {
        if (Tab::getIdFromClassName('AdminHbkit')) {
            return true;
        }
        $tab = new Tab();
        $tab->class_name = 'AdminHbkit';
        $tab->module = $this->name;
        $tab->id_parent = (int) Tab::getIdFromClassName('IMPROVE');
        $tab->icon = 'palette';
        $tab->active = 1;
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = 'Theme Kit';
        }

        return (bool) $tab->add();
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminHbkit'));
    }

    /* ------------------------------------------------------------------ */
    /* Tokens                                                              */
    /* ------------------------------------------------------------------ */

    public static function getTokens(): array
    {
        $saved = json_decode((string) Configuration::get('HBKIT_TOKENS'), true);

        return array_merge(self::DEFAULT_TOKENS, is_array($saved) ? $saved : []);
    }

    /** Builds the CSS custom properties block from the saved tokens. */
    public static function buildCssVars(array $t): array
    {
        $font = function ($name) {
            $name = trim((string) $name);
            if ($name === '' || $name === 'system-ui') {
                return 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';
            }
            $serif = preg_match('/serif|garamond|playfair|lora|cormorant|fraunces|libre|baskerville|dm serif/i', $name)
                && !preg_match('/sans/i', $name);

            return '"' . str_replace('"', '', $name) . '", ' . ($serif ? 'Georgia, serif' : 'system-ui, sans-serif');
        };

        return [
            '--hb-primary' => $t['primary'],
            '--hb-primary-rgb' => self::hexToRgb($t['primary']),
            '--hb-primary-hover' => self::shade($t['primary'], -12),
            '--hb-primary-contrast' => $t['primary_contrast'],
            '--hb-accent' => $t['accent'],
            '--hb-accent-rgb' => self::hexToRgb($t['accent']),
            '--hb-bg' => $t['bg'],
            '--hb-surface' => $t['surface'],
            '--hb-text' => $t['text'],
            '--hb-text-rgb' => self::hexToRgb($t['text']),
            '--hb-muted' => $t['muted'],
            '--hb-border' => $t['border'],
            '--hb-sale' => $t['sale'],
            '--hb-dark' => $t['dark'],
            '--hb-dark-text' => $t['dark_text'],
            '--hb-field-border' => $t['field_border'],
            '--hb-field-h' => max(36, min(64, (int) $t['field_height'])) . 'px',
            '--hb-font-heading' => $font($t['font_heading']),
            '--hb-font-body' => $font($t['font_body']),
            '--hb-heading-weight' => (string) (int) $t['heading_weight'],
            '--hb-heading-transform' => $t['heading_transform'] === 'uppercase' ? 'uppercase' : 'none',
            '--hb-heading-spacing' => ((float) $t['heading_spacing']) . 'em',
            '--hb-radius' => ((int) $t['radius']) . 'px',
            '--hb-btn-radius' => ((int) $t['button_radius']) . 'px',
            '--hb-btn-transform' => $t['button_transform'] === 'uppercase' ? 'uppercase' : 'none',
        ];
    }

    public static function hexToRgb($hex): string
    {
        $hex = ltrim((string) $hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (!preg_match('/^[0-9a-f]{6}$/i', $hex)) {
            return '0,0,0';
        }

        return implode(',', array_map('hexdec', str_split($hex, 2)));
    }

    /** Lightens (positive) or darkens (negative) a hex colour by a percentage. */
    public static function shade($hex, int $percent): string
    {
        $rgb = array_map('intval', explode(',', self::hexToRgb($hex)));
        foreach ($rgb as &$c) {
            $c = $percent < 0 ? $c * (100 + $percent) / 100 : $c + (255 - $c) * $percent / 100;
            $c = max(0, min(255, (int) round($c)));
        }

        return sprintf('#%02x%02x%02x', ...$rgb);
    }

    public static function isValidColor($value): bool
    {
        return (bool) preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', (string) $value);
    }

    /* ------------------------------------------------------------------ */
    /* Front hooks                                                         */
    /* ------------------------------------------------------------------ */

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet('hbkit', 'modules/' . $this->name . '/views/css/hbkit.css', ['media' => 'all', 'priority' => 60]);
        $this->context->controller->registerJavascript('hbkit', 'modules/' . $this->name . '/views/js/hbkit.js', ['position' => 'bottom', 'priority' => 160]);
    }

    public function hookDisplayHeader()
    {
        $this->context->smarty->assign([
            'hbkit_vars' => self::buildCssVars(self::getTokens()),
            'hbkit_front' => $this->getFrontConfig(),
        ]);

        return $this->fetch('module:hbkit/views/templates/hook/head.tpl');
    }

    public function hookActionFrontControllerSetVariables(array $params)
    {
        $t = self::getTokens();
        if (isset($params['templateVars']['page']['body_classes'])) {
            foreach (self::BODY_CLASSES as $key => $prefix) {
                $params['templateVars']['page']['body_classes']['hbk-' . $prefix . '-' . preg_replace('/[^a-z0-9_-]/', '', (string) $t[$key])] = true;
            }
            $params['templateVars']['page']['body_classes']['hbk'] = true;
        }

        return ['tokens' => $t, 'demo' => (string) Configuration::get('HBKIT_DEMO'), 'texts' => $this->getTexts(), 'size_guide' => $this->sizeGuide(),
            'fit' => $this->fitScale(), 'stamp_feature' => (int) Configuration::get('HBKIT_STAMP_FEATURE'),
            // Home page: the hero's first title is the page's h1 (the theme header then leaves the logo out of the h1).
            'hero_h1' => $this->homeHasHero()];
    }

    /** True on the home page when an active hero section is shown. */
    private function homeHasHero(): bool
    {
        $controller = $this->context->controller;
        if (!$controller || ($controller->php_self ?? '') !== 'index') {
            return false;
        }

        return (bool) Db::getInstance()->getValue('SELECT 1 FROM `' . _DB_PREFIX_ . "hbkit_section` WHERE `type` = 'hero' AND active = 1 AND id_shop = " . (int) $this->context->shop->id);
    }

    public function hookDisplayBanner()
    {
        if (!Configuration::get('HBKIT_TOPBAR_ACTIVE')) {
            return '';
        }
        $idLang = (int) $this->context->language->id;
        $text = (string) Configuration::get('HBKIT_TOPBAR_TEXT', $idLang);
        if ($text === '') {
            return '';
        }
        $this->context->smarty->assign([
            'hbkit_topbar' => array_filter(array_map('trim', explode('|', $text))),
            'hbkit_topbar_link' => (string) Configuration::get('HBKIT_TOPBAR_LINK', $idLang),
        ]);

        return $this->fetch('module:hbkit/views/templates/hook/topbar.tpl');
    }

    /* ------------------------------------------------------------------ */
    /* Feature labels (for example "Silk 100%", "Made in Italy")           */
    /* ------------------------------------------------------------------ */

    /** @var array<int, array<int, string>> feature values per product, by id_feature */
    private $featureCache = [];

    /** Feature values of a product in the current language, keyed by id_feature. */
    public function getFeatureValues(int $idProduct): array
    {
        if (!isset($this->featureCache[$idProduct])) {
            $rows = Db::getInstance()->executeS('SELECT fp.id_feature, fvl.value FROM `' . _DB_PREFIX_ . 'feature_product` fp
                JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl ON fvl.id_feature_value = fp.id_feature_value AND fvl.id_lang = ' . (int) $this->context->language->id . '
                WHERE fp.id_product = ' . $idProduct);
            $this->featureCache[$idProduct] = [];
            foreach ($rows ?: [] as $r) {
                $this->featureCache[$idProduct][(int) $r['id_feature']] = (string) $r['value'];
            }
        }

        return $this->featureCache[$idProduct];
    }

    /** id_feature list from a "3,5,7" setting. */
    /**
     * A fit feature for product templates (HBKIT_FIT_FEATURE): its id and each value's step,
     * in the order the values were created (for example runs small, true to size, runs large).
     */
    private function fitScale(): array
    {
        $id = (int) Configuration::get('HBKIT_FIT_FEATURE');
        if (!$id) {
            return ['feature' => 0, 'steps' => [], 'count' => 0];
        }
        $rows = Db::getInstance()->executeS('SELECT id_feature_value FROM `' . _DB_PREFIX_ . 'feature_value`
            WHERE id_feature = ' . $id . ' AND custom = 0 ORDER BY id_feature_value') ?: [];
        $steps = [];
        foreach ($rows as $i => $r) {
            $steps[(int) $r['id_feature_value']] = $i + 1;
        }

        return ['feature' => $id, 'steps' => $steps, 'count' => count($steps)];
    }

    private static function featureIds(string $key): array
    {
        return array_values(array_filter(array_map('intval', explode(',', (string) Configuration::get($key)))));
    }

    private static function productId($product): int
    {
        if (is_array($product) || $product instanceof ArrayAccess) {
            return (int) ($product['id_product'] ?? $product['id'] ?? 0);
        }

        return is_object($product) ? (int) ($product->id ?? 0) : 0;
    }

    /** Product cards: one feature as a small label under the name (Theme Kit > Product cards). */
    public function hookDisplayProductListReviews(array $params)
    {
        $ids = self::featureIds('HBKIT_CARD_FEATURE');
        $idProduct = self::productId($params['product'] ?? null);
        if (!$ids || !$idProduct) {
            return '';
        }
        $values = $this->getFeatureValues($idProduct);
        $out = '';
        foreach ($ids as $idFeature) {
            if (!empty($values[$idFeature])) {
                $out .= '<span class="hbk-ftag">' . htmlspecialchars($values[$idFeature], ENT_QUOTES, 'UTF-8') . '</span>';
            }
        }

        return $out ? '<div class="hbk-ftags hbk-ftags--card">' . $out . '</div>' : '';
    }

    /** Product page: a short delivery note and the chosen features as labels, under the add to cart button. */
    public function hookDisplayProductAdditionalInfo(array $params)
    {
        $html = '';
        $note = (string) Configuration::get('HBKIT_TXT_PRODUCT_NOTE', (int) $this->context->language->id);
        // Several lines separated by "|" become a short list (for example delivery, then returns).
        $lines = array_values(array_filter(array_map('trim', explode('|', $note)), 'strlen'));
        if (count($lines) > 1) {
            $html .= '<ul class="hbk-pnote hbk-pnote--list">';
            foreach ($lines as $line) {
                $html .= '<li>' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</li>';
            }
            $html .= '</ul>';
        } elseif ($lines) {
            $html .= '<p class="hbk-pnote">' . htmlspecialchars($lines[0], ENT_QUOTES, 'UTF-8') . '</p>';
        }
        $ids = self::featureIds('HBKIT_PRODUCT_FEATURES');
        $idProduct = self::productId($params['product'] ?? null);
        if ($ids && $idProduct) {
            $values = $this->getFeatureValues($idProduct);
            $named = self::featureIds('HBKIT_FEATURES_NAMED');
            $out = '';
            foreach ($ids as $idFeature) {
                if (empty($values[$idFeature])) {
                    continue;
                }
                $name = '';
                if (in_array($idFeature, $named, true)) {
                    $name = '<span class="hbk-ftag__name">' . htmlspecialchars((string) Feature::getFeature((int) $this->context->language->id, $idFeature)['name'], ENT_QUOTES, 'UTF-8') . '</span> ';
                }
                $out .= '<span class="hbk-ftag">' . $name . '<span class="hbk-ftag__value">' . htmlspecialchars($values[$idFeature], ENT_QUOTES, 'UTF-8') . '</span></span>';
            }
            if ($out) {
                $html .= '<div class="hbk-ftags hbk-ftags--product">' . $out . '</div>';
            }
        }

        return $html;
    }

    /** Top-level categories for the search panel. */
    private function topCategories(): array
    {
        $out = [];
        $idLang = (int) $this->context->language->id;
        foreach (Category::getChildren((int) Configuration::get('PS_HOME_CATEGORY'), $idLang, true) as $c) {
            $out[] = ['name' => $c['name'], 'url' => $this->context->link->getCategoryLink((int) $c['id_category'], $c['link_rewrite'], $idLang)];
        }

        return $out;
    }

    /** Search panel results: products for a query, rendered as small cards. */
    public function renderSearchResults(string $query): array
    {
        $idLang = (int) $this->context->language->id;
        $query = trim($query);
        $found = Tools::strlen($query) >= 2 ? Search::find($idLang, $query, 1, 4, 'position', 'desc', false, true, $this->context) : ['total' => 0, 'result' => []];
        $assembler = new ProductAssembler($this->context);
        $presenter = (new ProductPresenterFactory($this->context))->getPresenter();
        $settings = (new ProductPresenterFactory($this->context))->getPresentationSettings();
        $products = [];
        foreach ($found['result'] ?? [] as $row) {
            $products[] = $presenter->present($settings, $assembler->assembleProduct($row), $this->context->language);
        }
        $card = self::featureIds('HBKIT_CARD_FEATURE');
        foreach ($products as $i => $p) {
            $values = $this->getFeatureValues((int) $p['id_product']);
            $products[$i]['hbk_tag'] = $card && !empty($values[$card[0]]) ? $values[$card[0]] : '';
        }
        $this->context->smarty->assign('hbk_sr', [
            'query' => $query,
            'total' => (int) ($found['total'] ?? 0),
            'products' => $products,
            'all_url' => $this->context->link->getPageLink('search', true, $idLang, ['s' => $query]),
        ]);

        return ['total' => (int) ($found['total'] ?? 0), 'html' => $this->fetch('module:hbkit/views/templates/hook/search-results.tpl')];
    }

    /** Free delivery threshold in the shop's default currency (Shipping > Preferences), 0 when not set. */
    public static function freeShippingFrom(): float
    {
        return (float) Configuration::get('PS_SHIPPING_FREE_PRICE');
    }

    /** Settings read by hbkit.js; printed in the head so they exist before the theme scripts run. */
    public function getFrontConfig(): array
    {
        $t = self::getTokens();
        $page = (string) $this->context->controller->php_self;

        return [
            'cart_style' => $t['cart_style'],
            'drawer' => $t['cart_style'] === 'drawer' && !in_array($page, ['cart', 'order'], true),
            'cart_url' => $this->context->link->getModuleLink($this->name, 'cart', [], true),
            'cart_page' => $this->context->link->getPageLink('cart', true, null, ['action' => 'show']),
            'checkout_url' => $this->context->link->getPageLink('order', true),
            'options_url' => $this->context->link->getModuleLink($this->name, 'options', [], true),
            'product_tabs' => $t['product_tabs'],
            'close' => $this->trans('Close', [], 'Modules.Hbkit.Shop'),
            'view_cart' => $t['cart_word'] === 'bag' ? $this->trans('View bag', [], 'Modules.Hbkit.Shop') : $this->trans('View cart', [], 'Modules.Hbkit.Shop'),
            'search' => in_array($t['search_style'], ['panel', 'field'], true) ? [
                'url' => $this->context->link->getModuleLink($this->name, 'search', [], true),
            ] : null,
            'size_guide' => $this->sizeGuide(),
            'size_name' => $this->quickFacetName(),
            'size_hint' => (string) Configuration::get('HBKIT_TXT_SIZE_HINT', (int) $this->context->language->id),
            'quick_facet' => $this->quickFacet(),
            'labels' => [
                'subtotal' => $this->trans('Subtotal', [], 'Modules.Hbkit.Shop'),
                'checkout' => $this->trans('Checkout', [], 'Modules.Hbkit.Shop'),
                'note' => $this->trans('Taxes and delivery are calculated at checkout.', [], 'Modules.Hbkit.Shop'),
                'added' => $this->trans('Added to your cart', [], 'Modules.Hbkit.Shop'),
                'empty' => $this->trans('Your cart is empty', [], 'Modules.Hbkit.Shop'),
                'soldout' => $this->trans('Sold out', [], 'Modules.Hbkit.Shop'),
                'add' => $t['cart_word'] === 'bag' ? $this->trans('Add to bag', [], 'Modules.Hbkit.Shop') : '',
            ],
        ];
    }

    /** Texts set by the demo (HBKIT_TXT_*), in the current language. */
    public function getTexts(): array
    {
        $idLang = (int) $this->context->language->id;
        $out = [];
        foreach (self::TEXT_KEYS as $key => $name) {
            $out[$name] = (string) Configuration::get($key, $idLang);
        }
        $out['search_popular'] = array_values(array_filter(array_map('trim', explode('|', $out['search_popular']))));

        return $out;
    }

    /** Link to the size guide page (HBKIT_SIZE_GUIDE = id_cms), or null. */
    private function sizeGuide(): ?array
    {
        $id = (int) Configuration::get('HBKIT_SIZE_GUIDE');
        if (!$id) {
            return null;
        }
        $cms = new CMS($id, (int) $this->context->language->id);

        return Validate::isLoadedObject($cms) ? ['url' => $this->context->link->getCMSLink($cms), 'label' => $this->trans('Size guide', [], 'Modules.Hbkit.Shop')] : null;
    }

    /** Name of the attribute group shown as quick filter buttons on category pages (HBKIT_QUICK_FACET = id_attribute_group). */
    private function quickFacetName(): string
    {
        $id = (int) Configuration::get('HBKIT_QUICK_FACET');
        if (!$id) {
            return '';
        }
        $group = new AttributeGroup($id, (int) $this->context->language->id);

        return Validate::isLoadedObject($group) ? (string) ($group->public_name ?: $group->name) : '';
    }

    /** Quick filter buttons on category pages: a feature (HBKIT_QUICK_FEATURE) or else the size group (HBKIT_QUICK_FACET). */
    private function quickFacet(): ?array
    {
        $id = (int) Configuration::get('HBKIT_QUICK_FEATURE');
        if ($id) {
            $idLang = (int) $this->context->language->id;
            $f = Feature::getFeature($idLang, $id);
            // Buttons follow the order the values were created in (filters sort them by name).
            $order = array_column(Db::getInstance()->executeS('SELECT fvl.value FROM `' . _DB_PREFIX_ . 'feature_value` fv
                JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl ON fvl.id_feature_value = fv.id_feature_value AND fvl.id_lang = ' . $idLang . '
                WHERE fv.id_feature = ' . $id . ' AND fv.custom = 0 ORDER BY fv.id_feature_value') ?: [], 'value');

            return !empty($f['name']) ? ['type' => 'feature', 'name' => (string) $f['name'], 'order' => $order] : null;
        }
        $name = $this->quickFacetName();

        return $name !== '' ? ['type' => 'attribute_group', 'name' => $name] : null;
    }

    /** Header: the "Search" trigger when the search opens as a panel, or a search field with a results list. */
    public function hookDisplayTop()
    {
        $t = self::getTokens();
        if (!in_array($t['search_style'], ['panel', 'field'], true)) {
            return '';
        }
        $this->context->smarty->assign([
            'hbkit_search_url' => $this->context->link->getPageLink('search', true),
            'hbkit_search_query' => (string) Tools::getValue('s', ''),
            'hbkit_texts' => $this->getTexts(),
        ]);

        return $this->fetch('module:hbkit/views/templates/hook/' . ($t['search_style'] === 'field' ? 'search-field.tpl' : 'search-trigger.tpl'));
    }

    /**
     * Product page: a scale under the price for a feature that holds a range, for example a temperature
     * rating "-10 to 5 °C" (HBKIT_SCALE_FEATURE = id_feature, HBKIT_SCALE_RANGE = "min|max|step").
     */
    public function hookDisplayProductPriceBlock(array $params)
    {
        if (($params['type'] ?? '') !== 'after_price' || !($idFeature = (int) Configuration::get('HBKIT_SCALE_FEATURE'))) {
            return '';
        }
        $idProduct = self::productId($params['product'] ?? null);
        $value = $idProduct ? ($this->getFeatureValues($idProduct)[$idFeature] ?? '') : '';
        $range = self::parseRange($value);
        if (!$range) {
            return '';
        }
        [$min, $max, $step] = array_map('intval', explode('|', (string) Configuration::get('HBKIT_SCALE_RANGE') ?: '-30|30|10') + [-30, 30, 10]);
        if ($max <= $min || $step <= 0) {
            return '';
        }
        $pos = function ($v) use ($min, $max) { return round((max($min, min($max, $v)) - $min) / ($max - $min) * 100, 2); };
        $ticks = [];
        for ($v = $min; $v <= $max; $v += $step) {
            $ticks[] = str_replace('-', '−', (string) $v);
        }
        $f = Feature::getFeature((int) $this->context->language->id, $idFeature);
        $this->context->smarty->assign('hbk_scale', [
            'name' => (string) ($f['name'] ?? ''),
            'value' => $value,
            'left' => $pos($range[0]),
            'width' => max(2, $pos($range[1]) - $pos($range[0])),
            'ticks' => $ticks,
            'unit' => trim((string) preg_replace('/^.*\d/u', '', $value)),
        ]);

        return $this->fetch('module:hbkit/views/templates/hook/scale.tpl');
    }

    /** Two numbers from a text such as "-10 to 5 °C" or "−25 – −5 °C", or null. */
    public static function parseRange(string $text): ?array
    {
        $text = str_replace(['−', '–', '—'], ['-', ' ', ' '], $text);
        if (!preg_match_all('/-?\d+(?:[.,]\d+)?/', $text, $m) || count($m[0]) < 2) {
            return null;
        }
        $a = (float) str_replace(',', '.', $m[0][0]);
        $b = (float) str_replace(',', '.', $m[0][1]);

        return [min($a, $b), max($a, $b)];
    }

    /** Category page: a link to the next category on the same level (HBKIT_NEXT_CATEGORY = 1). */
    public function hookDisplayFooterCategory(array $params)
    {
        if (!Configuration::get('HBKIT_NEXT_CATEGORY')) {
            return '';
        }
        $idLang = (int) $this->context->language->id;
        $cat = $params['category'] ?? null;
        $idCategory = (int) (is_array($cat) ? ($cat['id'] ?? 0) : (Tools::getValue('id_category') ?: 0));
        $current = new Category($idCategory, $idLang);
        if (!Validate::isLoadedObject($current) || $current->is_root_category || !$current->id_parent) {
            return '';
        }
        $siblings = Category::getChildren((int) $current->id_parent, $idLang, true);
        if (count($siblings) < 2) {
            return '';
        }
        $next = null;
        foreach ($siblings as $i => $c) {
            if ((int) $c['id_category'] === $idCategory) {
                $next = $siblings[($i + 1) % count($siblings)];
                break;
            }
        }
        if (!$next) {
            return '';
        }
        $label = $this->getTexts()['next_category'] ?: $this->trans('Next category', [], 'Modules.Hbkit.Shop');
        $this->context->smarty->assign('hbk_next', [
            'label' => $label,
            'name' => $next['name'],
            'url' => $this->context->link->getCategoryLink((int) $next['id_category'], $next['link_rewrite'], $idLang),
        ]);

        return $this->fetch('module:hbkit/views/templates/hook/next-category.tpl');
    }

    /** Side cart shell and the settings the front script reads. The cart lines come from the "cart" front controller. */
    public function hookDisplayBeforeBodyClosingTag()
    {
        $t = self::getTokens();
        $this->context->smarty->assign([
            'hbkit_front' => $this->getFrontConfig(),
            'hbkit_texts' => $this->getTexts(),
            'hbkit_cart_word' => $t['cart_word'],
            'hbkit_search' => $t['search_style'] === 'panel' ? [
                'url' => $this->context->link->getPageLink('search', true),
                'categories' => $this->topCategories(),
            ] : null,
        ]);
        return $this->fetch('module:hbkit/views/templates/hook/drawer.tpl');
    }

    /** Free delivery bar at the top of the cart summary. */
    public function hookDisplayShoppingCart()
    {
        if ((string) $this->context->controller->php_self !== 'cart') {
            return '';
        }
        $this->context->smarty->assign('hbk', $this->getCartView());

        return '<div class="hbk-cartbar js-hbk-cartbar">' . $this->fetch('module:hbkit/views/templates/hook/freebar.tpl') . '</div>';
    }

    /** Values for the side cart and the free delivery bar. */
    public function getCartView(): array
    {
        $cart = $this->context->cart;
        $presented = (new PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter())->present($cart);
        $t = self::getTokens();
        $free = self::freeShippingFrom();
        $currency = $this->context->currency;
        if ($free > 0 && $currency && (int) $currency->id !== (int) Configuration::get('PS_CURRENCY_DEFAULT')) {
            $free = Tools::convertPrice($free, $currency);
        }
        $bar = null;
        if ($free > 0 && $t['free_bar'] === '1' && $presented['products_count'] > 0) {
            $total = $cart ? (float) $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS) : 0.0;
            $left = max(0, $free - $total);
            $bar = [
                'done' => $left <= 0,
                'left' => $this->context->getCurrentLocale()->formatPrice($left, $currency->iso_code),
                'percent' => (int) min(100, round($total / $free * 100)),
            ];
        }

        return [
            'cart' => $presented,
            'word' => $t['cart_word'],
            'bar' => $bar,
            'urls' => [
                'cart' => $this->context->link->getPageLink('cart', true, null, ['action' => 'show']),
                'checkout' => $this->context->link->getPageLink('order', true),
                'home' => $this->context->link->getPageLink('index', true),
            ],
        ];
    }

    public function hookDisplayHome()
    {
        $renderer = new HbkitRenderer($this->context, $this);
        $sections = $renderer->getSections();
        if (!$sections) {
            return '';
        }
        $this->context->smarty->assign(['hbkit_sections' => $sections]);

        return $this->fetch('module:hbkit/views/templates/hook/home.tpl');
    }
}
