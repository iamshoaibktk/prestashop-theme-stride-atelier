<?php
/**
 * Applies a demo pack (themes/<theme>/demo/demo.json + images/) to the shop.
 *
 * Texts in demo.json are either a plain string or a map {"en": "...", "fr": "...", ...};
 * a missing language falls back to "en". References between objects use keys
 * ("category:dresses", "product:silk-wrap-dress", "cms:about", "brand:vellor-atelier"),
 * which the importer turns into ids.
 *
 * The import is meant for a fresh shop. With "wipe" it first removes the existing
 * catalogue, CMS pages, menu links, link blocks and kit sections.
 *
 * @author    iamshoaibktk@gmail.com (WhatsApp: +923459016286)
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Addon\Theme\ThemeManagerBuilder;

class HbkitImporter
{
    private $dir;
    private $demo;
    private $langs = [];
    private $idShop;
    private $log = [];
    private $ids = ['category' => [], 'product' => [], 'cms' => [], 'brand' => [], 'attribute' => [], 'group' => [], 'feature' => [], 'feature_value' => []];

    public function __construct(string $packDir)
    {
        $this->dir = rtrim($packDir, '/');
        $json = $this->dir . '/demo.json';
        if (!is_file($json)) {
            throw new PrestaShopException('Demo pack not found: ' . $json);
        }
        $this->demo = json_decode((string) file_get_contents($json), true);
        if (!is_array($this->demo)) {
            throw new PrestaShopException('demo.json is not valid JSON: ' . json_last_error_msg());
        }
        foreach (Language::getLanguages(false) as $l) {
            $this->langs[(int) $l['id_lang']] = strtolower($l['iso_code']);
        }
        $this->idShop = (int) Context::getContext()->shop->id;
    }

    /** Lists the demo packs found in the installed themes. */
    public static function findPacks(): array
    {
        $packs = [];
        foreach (glob(_PS_ALL_THEMES_DIR_ . '*/demo/demo.json') ?: [] as $file) {
            $data = json_decode((string) file_get_contents($file), true);
            $theme = basename(dirname($file, 2));
            $packs[$theme] = [
                'theme' => $theme,
                'dir' => dirname($file),
                'name' => $data['meta']['name'] ?? $theme,
                'description' => $data['meta']['description']['en'] ?? ($data['meta']['description'] ?? ''),
                'version' => $data['meta']['version'] ?? '',
                'preview' => is_file(_PS_ALL_THEMES_DIR_ . $theme . '/preview.png') ? _THEMES_DIR_ . $theme . '/preview.png' : '',
                'descriptions' => is_array($data['meta']['description'] ?? null) ? $data['meta']['description'] : [],
                'counts' => ['products' => count($data['products'] ?? []), 'categories' => count($data['categories'] ?? []), 'pages' => count($data['cms'] ?? [])],
            ];
        }

        return $packs;
    }

    public function getLog(): array
    {
        return $this->log;
    }

    private function log(string $msg): void
    {
        $this->log[] = $msg;
        if (PHP_SAPI === 'cli') {
            echo $msg . PHP_EOL;
        }
    }

    public function run(array $options = []): bool
    {
        $options += ['wipe' => true, 'theme' => true];
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');
        Context::getContext()->employee = Context::getContext()->employee ?: new Employee((int) Db::getInstance()->getValue('SELECT id_employee FROM `' . _DB_PREFIX_ . 'employee` WHERE id_profile = 1 ORDER BY id_employee'));

        if ($options['wipe']) {
            $this->wipe();
        }
        if ($options['theme'] && !empty($this->demo['meta']['theme'])) {
            $this->enableTheme($this->demo['meta']['theme']);
        }
        if (isset($this->demo['config']['PS_JPEG_QUALITY'])) {
            Configuration::updateValue('PS_JPEG_QUALITY', (int) $this->demo['config']['PS_JPEG_QUALITY']);
        }
        $this->importShop();
        $this->importTokens();
        $this->importAttributes();
        $this->importFeatures();
        $this->importBrands();
        $this->importCategories();
        $this->importProducts();
        $this->importCms();
        $this->importMenu();
        $this->importLinkBlocks();
        $this->importReassurance();
        $this->importHome();
        $this->importSeo();
        $this->importConfig();
        $this->finish();
        Configuration::updateValue('HBKIT_DEMO', (string) ($this->demo['meta']['slug'] ?? ''));
        $this->log('Done.');

        return true;
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    /** Text value -> [id_lang => string] with English fallback. */
    private function lang($value, ?callable $filter = null): array
    {
        $out = [];
        foreach ($this->langs as $id => $iso) {
            if (is_array($value)) {
                $v = $value[$iso] ?? ($value['en'] ?? reset($value));
            } else {
                $v = (string) $value;
            }
            $out[$id] = $filter ? $filter((string) $v) : (string) $v;
        }

        return $out;
    }

    private function langOne($value, int $idLang): string
    {
        return $this->lang($value)[$idLang] ?? '';
    }

    private function rewrite($value): array
    {
        return $this->lang($value, function ($v) {
            $r = Tools::str2url($v);

            return $r !== '' ? $r : 'item';
        });
    }

    /** "category:dresses" -> "category:12" (keeps "page:..." and unknown specs untouched). */
    private function link(?string $spec): string
    {
        $spec = (string) $spec;
        if ($spec === '' || strpos($spec, ':') === false) {
            return $spec;
        }
        [$kind, $key] = explode(':', $spec, 2);
        $map = ['category' => 'category', 'product' => 'product', 'cms' => 'cms', 'brand' => 'brand', 'manufacturer' => 'brand'];
        if ($kind === 'attribute') {
            return 'attribute:' . (int) ($this->ids['attribute'][$key] ?? (ctype_digit($key) ? $key : 0));
        }
        if (isset($map[$kind])) {
            $id = $this->id($map[$kind], $key);

            return ($kind === 'brand' ? 'manufacturer' : $kind) . ':' . $id;
        }

        return $spec;
    }

    private function id(string $type, $key): int
    {
        if (is_int($key) || ctype_digit((string) $key)) {
            return (int) $key;
        }
        if ($type === 'category' && $key === 'home') {
            return (int) Configuration::get('PS_HOME_CATEGORY');
        }

        return (int) ($this->ids[$type][$key] ?? 0);
    }

    /** Adds an ObjectModel and stops the import with a clear message if it fails. */
    private function save(ObjectModel $obj): void
    {
        $error = $obj->validateFields(false, true);
        if ($error !== true) {
            throw new PrestaShopException(get_class($obj) . ': ' . $error);
        }
        $error = $obj->validateFieldsLang(false, true);
        if ($error !== true) {
            throw new PrestaShopException(get_class($obj) . ': ' . $error);
        }
        if (!$obj->add()) {
            throw new PrestaShopException(get_class($obj) . ' could not be saved: ' . Db::getInstance()->getMsgError());
        }
    }

    private function image(string $rel): string
    {
        $path = $this->dir . '/images/' . ltrim($rel, '/');

        return is_file($path) ? $path : '';
    }

    /** Resizes $src into "$base.jpg" and every image type of $entity, cropping to each type's shape first (cover). */
    private function makeImages(string $src, string $base, string $entity): void
    {
        ImageManager::resize($src, $base . '.jpg', null, null, 'jpg', true);
        foreach (ImageType::getImagesTypes($entity, true) as $type) {
            $w = (int) $type['width'];
            $h = (int) $type['height'];
            $cropped = $this->cropToRatio($src, $w, $h);
            ImageManager::resize($cropped ?: $src, $base . '-' . stripslashes($type['name']) . '.jpg', $w, $h, 'jpg', true);
            if ($cropped) {
                @unlink($cropped);
            }
        }
    }

    /** Crops a copy of the image to the w:h ratio (centred, slightly above centre for tall crops). Returns a temp file or ''. */
    private function cropToRatio(string $src, int $w, int $h): string
    {
        $info = @getimagesize($src);
        if (!$info || !$w || !$h) {
            return '';
        }
        [$sw, $sh] = $info;
        $target = $w / $h;
        $ratio = $sw / $sh;
        if (abs($ratio - $target) < 0.02 && $sw >= $w) {
            return '';
        }
        if ($ratio > $target) {
            $cw = (int) round($sh * $target);
            $ch = $sh;
            $x = (int) (($sw - $cw) / 2);
            $y = 0;
        } else {
            $cw = $sw;
            $ch = (int) round($sw / $target);
            $x = 0;
            $y = (int) (($sh - $ch) * 0.35);
        }
        $img = $info[2] === IMAGETYPE_PNG ? @imagecreatefrompng($src) : (@imagecreatefromjpeg($src) ?: @imagecreatefromstring((string) file_get_contents($src)));
        if (!$img) {
            return '';
        }
        // PrestaShop never upscales (it pads with white), so bring small crops up to the target size here
        $ow = max($cw, $w);
        $oh = (int) round($ow / $target);
        $out = imagecreatetruecolor($ow, $oh);
        imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
        imagecopyresampled($out, $img, 0, 0, $x, $y, $ow, $oh, $cw, $ch);
        $tmp = tempnam(_PS_TMP_IMG_DIR_, 'hbk') . '.jpg';
        imagejpeg($out, $tmp, 92);
        imagedestroy($img);
        imagedestroy($out);

        return $tmp;
    }

    /* ------------------------------------------------------------------ */
    /* Steps                                                               */
    /* ------------------------------------------------------------------ */

    private function wipe(): void
    {
        $db = Db::getInstance();
        $root = (int) Configuration::get('PS_ROOT_CATEGORY');
        $home = (int) Configuration::get('PS_HOME_CATEGORY');

        foreach ($db->executeS('SELECT id_product FROM `' . _DB_PREFIX_ . 'product`') ?: [] as $r) {
            (new Product((int) $r['id_product']))->delete();
        }
        foreach ($db->executeS('SELECT id_category FROM `' . _DB_PREFIX_ . 'category` WHERE id_category NOT IN (' . $root . ',' . $home . ') AND id_parent = ' . $home) ?: [] as $r) {
            $c = new Category((int) $r['id_category']);
            if (Validate::isLoadedObject($c)) {
                $c->delete();
            }
        }
        foreach ($db->executeS('SELECT id_manufacturer FROM `' . _DB_PREFIX_ . 'manufacturer`') ?: [] as $r) {
            (new Manufacturer((int) $r['id_manufacturer']))->delete();
        }
        foreach ($db->executeS('SELECT id_attribute_group FROM `' . _DB_PREFIX_ . 'attribute_group`') ?: [] as $r) {
            (new AttributeGroup((int) $r['id_attribute_group']))->delete();
        }
        foreach ($db->executeS('SELECT id_feature FROM `' . _DB_PREFIX_ . 'feature`') ?: [] as $r) {
            (new Feature((int) $r['id_feature']))->delete();
        }
        foreach ($db->executeS('SELECT id_cms FROM `' . _DB_PREFIX_ . 'cms`') ?: [] as $r) {
            (new CMS((int) $r['id_cms']))->delete();
        }
        foreach (HbkitSection::getAll($this->idShop) as $r) {
            (new HbkitSection((int) $r['id_hbkit_section']))->delete();
        }
        foreach (['link_block_lang', 'link_block_shop', 'link_block', 'linksmenutop_lang', 'linksmenutop'] as $t) {
            if ($db->executeS('SHOW TABLES LIKE \'' . _DB_PREFIX_ . $t . '\'')) {
                $db->execute('DELETE FROM `' . _DB_PREFIX_ . $t . '`');
            }
        }
        Tools::deleteDirectory(_PS_IMG_DIR_ . HbkitRenderer::IMG_DIR, false);
        $this->log('Existing catalogue and content removed.');
    }

    private function enableTheme(string $name): void
    {
        if (!is_dir(_PS_ALL_THEMES_DIR_ . $name)) {
            $this->log('Theme folder not found: ' . $name . ' (skipped).');

            return;
        }
        $manager = (new ThemeManagerBuilder(Context::getContext(), Db::getInstance()))->build();
        $manager->enable($name);
        $kit = Module::getInstanceByName('hbkit');
        if ($kit) {
            $kit->ensureHooks();
        }
        $this->log('Theme enabled: ' . $name);
    }

    private function importShop(): void
    {
        $s = $this->demo['shop'] ?? [];
        $map = [
            'name' => 'PS_SHOP_NAME', 'email' => 'PS_SHOP_EMAIL', 'phone' => 'PS_SHOP_PHONE', 'address1' => 'PS_SHOP_ADDR1',
            'address2' => 'PS_SHOP_ADDR2', 'postcode' => 'PS_SHOP_CODE', 'city' => 'PS_SHOP_CITY', 'details' => 'PS_SHOP_DETAILS',
        ];
        foreach ($map as $key => $conf) {
            if (isset($s[$key])) {
                Configuration::updateValue($conf, (string) $s[$key]);
            }
        }
        if (!empty($s['country'])) {
            $idCountry = (int) Country::getByIso($s['country']);
            if ($idCountry) {
                Configuration::updateValue('PS_SHOP_COUNTRY_ID', $idCountry);
                Configuration::updateValue('PS_SHOP_COUNTRY', Country::getNameById((int) Configuration::get('PS_LANG_DEFAULT'), $idCountry));
            }
        }
        if (!empty($s['name'])) {
            $shop = new Shop($this->idShop);
            $shop->name = (string) $s['name'];
            $shop->update();
        }
        foreach (['logo' => 'PS_LOGO', 'logo_mail' => 'PS_LOGO_MAIL', 'logo_invoice' => 'PS_LOGO_INVOICE'] as $key => $conf) {
            $file = !empty($s[$key]) ? $this->image($s[$key]) : (($key !== 'logo' && !empty($s['logo'])) ? $this->image($s['logo']) : '');
            if ($file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $target = Tools::str2url((string) ($s['name'] ?? 'shop')) . '-' . str_replace('_', '-', $key) . '.' . $ext;
                copy($file, _PS_IMG_DIR_ . $target);
                Configuration::updateValue($conf, $target);
            }
        }
        if (!empty($s['favicon']) && ($file = $this->image($s['favicon']))) {
            copy($file, _PS_IMG_DIR_ . 'favicon.ico');
            Configuration::updateValue('PS_FAVICON', 'favicon.ico');
        }
        // Only the networks this demo lists: clear the ones a previous demo set.
        foreach (['FACEBOOK', 'TWITTER', 'RSS', 'YOUTUBE', 'PINTEREST', 'VIMEO', 'INSTAGRAM', 'LINKEDIN', 'TIKTOK', 'DISCORD'] as $network) {
            Configuration::updateValue('BLOCKSOCIAL_' . $network, '');
        }
        foreach ($s['social'] ?? [] as $network => $url) {
            Configuration::updateValue('BLOCKSOCIAL_' . strtoupper($network), (string) $url);
        }
        $this->log('Shop details, logo and social links set.');
    }

    private function importTokens(): void
    {
        if (!empty($this->demo['tokens'])) {
            // A demo starts from the kit defaults, so no setting is left over from a demo imported before it.
            $tokens = array_intersect_key($this->demo['tokens'], Hbkit::DEFAULT_TOKENS) + Hbkit::DEFAULT_TOKENS;
            Configuration::updateValue('HBKIT_TOKENS', json_encode($tokens));
        }
        $top = $this->demo['topbar'] ?? null;
        if ($top) {
            Configuration::updateValue('HBKIT_TOPBAR_ACTIVE', (int) ($top['active'] ?? 1));
            Configuration::updateValue('HBKIT_TOPBAR_TEXT', $this->lang($top['text'] ?? ''));
            Configuration::updateValue('HBKIT_TOPBAR_LINK', $this->lang($top['url'] ?? ''));
        }
        $this->log('Theme options set.');
    }

    private function importAttributes(): void
    {
        foreach ($this->demo['attribute_groups'] ?? [] as $pos => $g) {
            $group = new AttributeGroup();
            $group->name = $this->lang($g['name']);
            $group->public_name = $this->lang($g['public_name'] ?? $g['name']);
            $group->group_type = $g['type'] ?? 'select';
            $group->is_color_group = (int) (($g['type'] ?? '') === 'color');
            $group->position = $pos;
            $this->save($group);
            $this->ids['group'][$g['key']] = (int) $group->id;
            foreach ($g['values'] as $vpos => $v) {
                $attr = new ProductAttribute();
                $attr->id_attribute_group = (int) $group->id;
                $attr->name = $this->lang($v['name']);
                $attr->color = (string) ($v['color'] ?? '');
                $attr->position = $vpos;
                $this->save($attr);
                $this->ids['attribute'][$g['key'] . ':' . $v['key']] = (int) $attr->id;
            }
        }
        $this->log(count($this->ids['group']) . ' attribute groups created.');
    }

    private function importFeatures(): void
    {
        foreach ($this->demo['features'] ?? [] as $pos => $f) {
            $feature = new Feature();
            $feature->name = $this->lang($f['name']);
            $feature->position = $pos;
            $this->save($feature);
            $this->ids['feature'][$f['key']] = (int) $feature->id;
            foreach ($f['values'] as $v) {
                $fv = new FeatureValue();
                $fv->id_feature = (int) $feature->id;
                $fv->value = $this->lang($v['name']);
                $fv->custom = 0;
                $this->save($fv);
                $this->ids['feature_value'][$f['key'] . ':' . $v['key']] = (int) $fv->id;
            }
        }
        $this->log(count($this->ids['feature']) . ' features created.');
    }

    private function importBrands(): void
    {
        foreach ($this->demo['brands'] ?? [] as $b) {
            $m = new Manufacturer();
            $m->name = (string) $b['name'];
            $m->active = 1;
            $m->description = $this->lang($b['description'] ?? '');
            $m->short_description = $this->lang($b['short_description'] ?? '');
            $m->meta_title = $this->lang($b['name']);
            $this->save($m);
            $this->ids['brand'][$b['key']] = (int) $m->id;
            if (!empty($b['logo']) && ($file = $this->image($b['logo']))) {
                $this->makeImages($file, _PS_MANU_IMG_DIR_ . (int) $m->id, 'manufacturers');
            }
        }
        $this->log(count($this->ids['brand']) . ' brands created.');
    }

    private function importCategories(): void
    {
        if (!empty($this->demo['catalog']['home_name'])) {
            $home = new Category((int) Configuration::get('PS_HOME_CATEGORY'));
            $home->name = $this->lang($this->demo['catalog']['home_name']);
            $home->link_rewrite = $this->rewrite($this->demo['catalog']['home_name']);
            $home->update();
        }
        $pos = 0;
        foreach ($this->demo['categories'] ?? [] as $c) {
            $cat = new Category();
            $cat->id_parent = $this->id('category', $c['parent'] ?? 'home') ?: (int) Configuration::get('PS_HOME_CATEGORY');
            $cat->name = $this->lang($c['name'], function ($v) { return trim(preg_replace('/[<>;=#{}]/', '', $v)); });
            $cat->link_rewrite = $this->rewrite($c['name']);
            $cat->description = $this->lang($c['description'] ?? '');
            $cat->additional_description = $this->lang($c['additional_description'] ?? '');
            $cat->meta_title = $this->lang($c['meta_title'] ?? $c['name']);
            $cat->meta_description = $this->lang($c['meta_description'] ?? '');
            $cat->active = 1;
            $cat->position = $pos++;
            $this->save($cat);
            $this->ids['category'][$c['key']] = (int) $cat->id;
            if (!empty($c['image']) && ($file = $this->image($c['image']))) {
                $this->makeImages($file, _PS_CAT_IMG_DIR_ . (int) $cat->id, 'categories');
            }
            if (!empty($c['thumbnail']) && ($file = $this->image($c['thumbnail']))) {
                ImageManager::resize($file, _PS_CAT_IMG_DIR_ . (int) $cat->id . '_thumb.jpg', null, null, 'jpg', true);
            }
        }
        Category::regenerateEntireNtree();
        $this->log(count($this->ids['category']) . ' categories created.');
    }

    private function importProducts(): void
    {
        $idTaxRulesGroup = (int) ($this->demo['catalog']['id_tax_rules_group'] ?? 0);
        $now = time();
        $n = 0;
        foreach ($this->demo['products'] ?? [] as $i => $p) {
            $product = new Product();
            $product->name = $this->lang($p['name'], function ($v) { return trim(preg_replace('/[<>;=#{}]/', '', $v)); });
            $product->link_rewrite = $this->rewrite($p['name']);
            $product->description_short = $this->lang($p['short'] ?? '');
            $product->description = $this->lang($p['description'] ?? '');
            $product->meta_title = $this->lang($p['meta_title'] ?? '');
            $product->meta_description = $this->lang($p['meta_description'] ?? ($p['short_plain'] ?? ''));
            $product->reference = (string) ($p['reference'] ?? strtoupper(substr(md5($p['key']), 0, 8)));
            $product->price = round((float) $p['price'], 6);
            $product->wholesale_price = round((float) $p['price'] * 0.45, 2);
            $product->id_tax_rules_group = $idTaxRulesGroup;
            $product->id_manufacturer = $this->id('brand', $p['brand'] ?? '');
            $categories = array_values(array_unique(array_filter(array_map(function ($k) { return $this->id('category', $k); }, array_merge([$p['category']], $p['categories'] ?? [])))));
            $product->id_category_default = $categories[0] ?? (int) Configuration::get('PS_HOME_CATEGORY');
            $product->active = 1;
            $product->visibility = 'both';
            $product->condition = 'new';
            $product->show_price = 1;
            $product->available_for_order = 1;
            $product->weight = (float) ($p['weight'] ?? 0.5);
            $product->state = Product::STATE_SAVED;
            $dateAdd = date('Y-m-d H:i:s', !empty($p['new']) ? $now - ($i * 3600) : $now - (60 + $i) * 86400);
            $this->save($product);
            $id = (int) $product->id;
            // add() sets date_add to now, which would make every product "New": write the intended date afterwards.
            foreach (['product', 'product_shop'] as $table) {
                Db::getInstance()->update($table, ['date_add' => pSQL($dateAdd)], 'id_product = ' . $id);
            }
            $this->ids['product'][$p['key']] = $id;

            $product->addToCategories(array_unique(array_merge($categories, $this->demo['catalog']['also_in_home'] ?? true ? [(int) Configuration::get('PS_HOME_CATEGORY')] : [])));

            // Features
            foreach ($p['features'] ?? [] as $fKey => $vKey) {
                $idFeature = $this->id('feature', $fKey);
                $idValue = (int) ($this->ids['feature_value'][$fKey . ':' . $vKey] ?? 0);
                if ($idFeature && $idValue) {
                    Product::addFeatureProductImport($id, $idFeature, $idValue);
                }
            }

            // Text the customer types on the product page (for example initials for a free monogram):
            // PrestaShop customization fields. The theme decides how the field looks.
            $textFields = 0;
            $required = false;
            foreach ($p['text_fields'] ?? [] as $tf) {
                $field = new CustomizationField();
                $field->id_product = $id;
                $field->type = Product::CUSTOMIZE_TEXTFIELD;
                $field->required = !empty($tf['required']) ? 1 : 0;
                $field->is_module = 0;
                $field->is_deleted = 0;
                $field->name = $this->lang($tf['name'], function ($v) { return trim(preg_replace('/[<>;=#{}]/', '', $v)); });
                $this->save($field);
                ++$textFields;
                $required = $required || $field->required;
            }
            if ($textFields) {
                // 1 = has fields, 2 = has a required field (then the product cannot be added from a listing).
                $flags = ['customizable' => $required ? 2 : 1, 'text_fields' => $textFields];
                foreach (['product', 'product_shop'] as $table) {
                    Db::getInstance()->update($table, $flags, 'id_product = ' . $id);
                }
            }

            // Images (first = cover)
            $imageIds = [];
            foreach ($p['images'] ?? [] as $k => $rel) {
                $file = $this->image($rel);
                if (!$file) {
                    continue;
                }
                $image = new Image();
                $image->id_product = $id;
                $image->position = Image::getHighestPosition($id) + 1;
                $image->cover = $k === 0 ? 1 : 0;
                $image->legend = $product->name;
                if ($image->add()) {
                    $image->createImgFolder();
                    $this->makeImages($file, $image->getPathForCreation(), 'products');
                    $imageIds[] = (int) $image->id;
                }
            }

            // Combinations: {"size": ["s","m"], "color": ["black"]} -> cartesian product
            $qty = (int) ($p['quantity'] ?? 25);
            if (!empty($p['combinations'])) {
                $sets = [[]];
                foreach ($p['combinations'] as $gKey => $values) {
                    $next = [];
                    foreach ($sets as $set) {
                        foreach ($values as $vKey) {
                            $idAttr = (int) ($this->ids['attribute'][$gKey . ':' . $vKey] ?? 0);
                            if ($idAttr) {
                                $next[] = array_merge($set, [$idAttr]);
                            }
                        }
                    }
                    $sets = $next;
                }
                foreach ($sets as $k => $attrs) {
                    $comb = new Combination();
                    $comb->id_product = $id;
                    $comb->reference = $product->reference . '-' . ($k + 1);
                    $comb->default_on = $k === 0 ? 1 : null;
                    $comb->minimal_quantity = 1;
                    $this->save($comb);
                    $comb->setAttributes($attrs);
                    if ($imageIds) {
                        $comb->setImages([$imageIds[0]]);
                    }
                    StockAvailable::setQuantity($id, (int) $comb->id, max(0, $qty - ($k % 4) * 3));
                }
                Product::updateDefaultAttribute($id);
            } else {
                StockAvailable::setQuantity($id, 0, $qty);
            }

            // Discount
            if (!empty($p['discount'])) {
                $sp = new SpecificPrice();
                $sp->id_product = $id;
                $sp->id_product_attribute = 0;
                $sp->id_shop = 0;
                $sp->id_currency = 0;
                $sp->id_country = 0;
                $sp->id_group = 0;
                $sp->id_customer = 0;
                $sp->from_quantity = 1;
                $sp->price = -1;
                $sp->reduction = ((float) $p['discount']) / 100;
                $sp->reduction_type = 'percentage';
                $sp->reduction_tax = 1;
                $sp->from = '0000-00-00 00:00:00';
                $sp->to = '0000-00-00 00:00:00';
                $this->save($sp);
            }

            // Accessories (related products) are linked after all products exist
            Search::indexation(false, $id);
            ++$n;
        }

        foreach ($this->demo['products'] ?? [] as $p) {
            if (!empty($p['related'])) {
                $ids = array_filter(array_map(function ($k) { return $this->id('product', $k); }, $p['related']));
                if ($ids) {
                    (new Product($this->id('product', $p['key'])))->changeAccessories($ids);
                }
            }
        }
        // Category positions follow the demo order
        foreach ($this->ids['category'] as $idCat) {
            $pos = 0;
            foreach ($this->demo['products'] ?? [] as $p) {
                $idProduct = $this->id('product', $p['key']);
                Db::getInstance()->update('category_product', ['position' => $pos++], 'id_category = ' . (int) $idCat . ' AND id_product = ' . $idProduct);
            }
        }
        $this->log($n . ' products created.');
    }

    private function importCms(): void
    {
        foreach ($this->demo['cms'] ?? [] as $pos => $c) {
            $cms = new CMS();
            $cms->id_cms_category = 1;
            $cms->meta_title = $this->lang($c['title']);
            $cms->head_seo_title = $this->lang($c['seo_title'] ?? $c['title']);
            $cms->meta_description = $this->lang($c['meta_description'] ?? '');
            $cms->link_rewrite = $this->rewrite($c['slug'] ?? $c['title']);
            $cms->content = $this->lang($c['content']);
            $cms->active = 1;
            $cms->indexation = 1;
            $cms->position = $pos;
            $this->save($cms);
            $this->ids['cms'][$c['key']] = (int) $cms->id;
            if (!empty($c['role'])) {
                // e.g. "conditions" -> PS_CONDITIONS_CMS_ID (checkout terms checkbox)
                $roles = ['conditions' => 'PS_CONDITIONS_CMS_ID'];
                if (isset($roles[$c['role']])) {
                    Configuration::updateValue($roles[$c['role']], (int) $cms->id);
                    Configuration::updateValue('PS_CONDITIONS', 1);
                }
            }
        }
        $this->log(count($this->ids['cms']) . ' CMS pages created.');
    }

    private function importMenu(): void
    {
        if (empty($this->demo['menu'])) {
            return;
        }
        $db = Db::getInstance();
        $items = [];
        foreach ($this->demo['menu'] as $entry) {
            if (is_string($entry)) {
                [$kind, $key] = array_pad(explode(':', $entry, 2), 2, '');
                switch ($kind) {
                    case 'category': $items[] = 'CAT' . $this->id('category', $key); break;
                    case 'cms': $items[] = 'CMS' . $this->id('cms', $key); break;
                    case 'brand': $items[] = 'MAN' . $this->id('brand', $key); break;
                    case 'brands': $items[] = 'ALLMAN0'; break;
                    case 'product': $items[] = 'PRD' . $this->id('product', $key); break;
                }
                continue;
            }
            // Custom link {"label": {...}, "url": {...} | "page:new-products", "new_window": false}
            $db->insert('linksmenutop', ['id_shop' => $this->idShop, 'new_window' => (int) !empty($entry['new_window'])]);
            $idLink = (int) $db->Insert_ID();
            foreach ($this->langs as $idLang => $iso) {
                $url = $entry['url'] ?? '';
                if (is_string($url) && strpos($url, 'page:') === 0) {
                    $url = Context::getContext()->link->getPageLink(substr($url, 5), null, $idLang);
                } else {
                    $url = $this->langOne($url, $idLang);
                }
                $db->insert('linksmenutop_lang', [
                    'id_linksmenutop' => $idLink, 'id_lang' => $idLang, 'id_shop' => $this->idShop,
                    'label' => pSQL($this->langOne($entry['label'], $idLang)), 'link' => pSQL($url),
                ]);
            }
            $items[] = 'LNK' . $idLink;
        }
        Configuration::updateValue('MOD_BLOCKTOPMENU_ITEMS', implode(',', $items));
        $this->log('Main menu set (' . count($items) . ' entries).');
    }

    private function importLinkBlocks(): void
    {
        if (empty($this->demo['link_blocks'])) {
            return;
        }
        $db = Db::getInstance();
        foreach ($this->demo['link_blocks'] as $pos => $block) {
            $content = ['cms' => [false], 'product' => [false], 'static' => [false]];
            $custom = [];
            foreach ($block['links'] as $link) {
                if (is_string($link)) {
                    [$kind, $key] = array_pad(explode(':', $link, 2), 2, '');
                    if ($kind === 'cms') {
                        $content['cms'] = array_values(array_filter(array_merge($content['cms'], [(string) $this->id('cms', $key)])));
                    } elseif ($kind === 'page' && in_array($key, ['prices-drop', 'new-products', 'best-sales'], true)) {
                        $content['product'] = array_values(array_filter(array_merge($content['product'], [$key])));
                    } elseif ($kind === 'page') {
                        $content['static'] = array_values(array_filter(array_merge($content['static'], [$key])));
                    }
                } else {
                    $custom[] = $link;
                }
            }
            foreach (['cms', 'product', 'static'] as $k) {
                $content[$k] = $content[$k] ?: [false];
            }
            $idHook = (int) Hook::getIdByName($block['hook'] ?? 'displayFooter');
            $db->insert('link_block', ['id_hook' => $idHook, 'position' => $pos, 'content' => pSQL(json_encode($content))]);
            $idBlock = (int) $db->Insert_ID();
            $db->insert('link_block_shop', ['id_link_block' => $idBlock, 'id_shop' => $this->idShop, 'position' => $pos]);
            foreach ($this->langs as $idLang => $iso) {
                $customLang = [];
                foreach ($custom as $c) {
                    $customLang[] = ['title' => $this->langOne($c['title'], $idLang), 'url' => $this->langOne($c['url'], $idLang)];
                }
                $db->insert('link_block_lang', [
                    'id_link_block' => $idBlock, 'id_lang' => $idLang,
                    'name' => pSQL(mb_substr($this->langOne($block['name'], $idLang), 0, 40)),
                    'custom_content' => pSQL(json_encode($customLang)),
                ]);
            }
        }
        $this->log(count($this->demo['link_blocks']) . ' footer link blocks created.');
    }

    private function importReassurance(): void
    {
        if (empty($this->demo['reassurance']) || !Module::isInstalled('blockreassurance')) {
            return;
        }
        $db = Db::getInstance();
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'psreassurance_lang`');
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'psreassurance`');
        foreach ($this->demo['reassurance'] as $pos => $r) {
            $idCms = !empty($r['cms']) ? $this->id('cms', $r['cms']) : 0;
            $db->insert('psreassurance', [
                'icon' => pSQL('reassurance/' . ($r['icon'] ?? 'pack2/security.svg')), 'custom_icon' => null, 'status' => 1,
                'position' => $pos + 1, 'type_link' => $idCms ? 1 : 0, 'id_cms' => $idCms, 'date_add' => date('Y-m-d H:i:s'),
            ], true);
            $id = (int) $db->Insert_ID();
            foreach ($this->langs as $idLang => $iso) {
                $db->insert('psreassurance_lang', [
                    'id_psreassurance' => $id, 'id_lang' => $idLang,
                    'title' => pSQL($this->langOne($r['title'], $idLang)),
                    'description' => pSQL($this->langOne($r['text'] ?? '', $idLang)),
                    'link' => '',
                ]);
            }
        }
        $tokens = Hbkit::getTokens();
        Configuration::updateValue('PSR_ICON_COLOR', $tokens['accent']);
        Configuration::updateValue('PSR_TEXT_COLOR', $tokens['text']);
        $this->log(count($this->demo['reassurance']) . ' reassurance blocks created.');
    }

    /**
     * Titles and descriptions for search engines, from the demo's own texts:
     * - home page: "<shop> | <hero title>" and the hero text as its description (also the "Home" category text);
     * - products, categories and CMS pages: "<name> | <shop>" (when it fits in 65 characters);
     * - products: a description of up to 155 characters from the short and long texts;
     * - brands: "Discover <brand> at <shop>: <three of its products> and more."
     */
    private function importSeo(): void
    {
        $shop = (string) ($this->demo['shop']['name'] ?? Configuration::get('PS_SHOP_NAME'));
        $db = Db::getInstance();
        $titled = function (string $name) use ($shop): string {
            $t = trim($name) . ' | ' . $shop;

            return ($shop !== '' && mb_strlen($t) <= 65) ? $t : trim($name);
        };

        // Home page
        $hero = null;
        foreach ($this->demo['home'] ?? [] as $section) {
            if (($section['type'] ?? '') === 'hero' && !empty($section['items'][0])) {
                $hero = $section['items'][0];
                break;
            }
        }
        $meta = Meta::getMetaByPage('index', (int) Configuration::get('PS_LANG_DEFAULT'));
        if ($hero && !empty($meta['id_meta'])) {
            $m = new Meta((int) $meta['id_meta']);
            $homeCat = new Category((int) Configuration::get('PS_HOME_CATEGORY'));
            foreach ($this->langs as $id => $iso) {
                $title = self::plain($this->langOne($hero['title'] ?? '', $id));
                $text = self::sentences(self::plain($this->langOne($hero['text'] ?? '', $id)), 155);
                if ($title !== '') {
                    $m->title[$id] = mb_substr($shop . ' | ' . $title, 0, 128);
                }
                if (trim((string) ($homeCat->meta_title[$id] ?? '')) === '') {
                    $homeCat->meta_title[$id] = $titled((string) ($homeCat->name[$id] ?? ''));
                }
                if ($text !== '') {
                    $m->description[$id] = $text;
                    if (trim(strip_tags((string) ($homeCat->description[$id] ?? ''))) === '') {
                        $homeCat->description[$id] = '<p>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</p>';
                        $homeCat->meta_description[$id] = $text;
                    }
                }
            }
            $m->save();
            $homeCat->update();
        }

        // Products
        $byBrand = [];
        foreach ($this->demo['products'] ?? [] as $p) {
            $idProduct = $this->id('product', $p['key']);
            if (!$idProduct) {
                continue;
            }
            foreach ($this->langs as $id => $iso) {
                $name = self::plain($this->langOne($p['name'], $id));
                $desc = self::sentences(trim(self::plain($this->langOne($p['short'] ?? '', $id)) . ' ' . self::plain($this->langOne($p['description'] ?? '', $id))), 155);
                $row = ['meta_title' => pSQL($titled($name))];
                if ($desc !== '') {
                    $row['meta_description'] = pSQL($desc);
                }
                $db->update('product_lang', $row, 'id_product = ' . (int) $idProduct . ' AND id_lang = ' . (int) $id);
                if (!empty($p['brand'])) {
                    $byBrand[$p['brand']][$id][] = $name;
                }
            }
        }

        // Categories and CMS pages
        foreach ($this->demo['categories'] ?? [] as $c) {
            $idCat = $this->id('category', $c['key']);
            if (!$idCat) {
                continue;
            }
            foreach ($this->langs as $id => $iso) {
                $row = ['meta_title' => pSQL($titled(self::plain($this->langOne($c['meta_title'] ?? $c['name'], $id))))];
                $text = self::sentences(self::plain($this->langOne($c['description'] ?? '', $id)), 155);
                if ($text !== '' && empty($c['meta_description'])) {
                    $row['meta_description'] = pSQL($text);
                }
                $db->update('category_lang', $row, 'id_category = ' . (int) $idCat . ' AND id_lang = ' . (int) $id);
            }
        }
        foreach ($this->ids['cms'] ?? [] as $idCms) {
            foreach ($this->langs as $id => $iso) {
                // meta_title is the page's heading; head_seo_title is its <title>.
                $row = $db->getRow('SELECT meta_title, head_seo_title FROM `' . _DB_PREFIX_ . 'cms_lang` WHERE id_cms = ' . (int) $idCms . ' AND id_lang = ' . (int) $id);
                $title = trim((string) (($row['head_seo_title'] ?? '') !== '' ? $row['head_seo_title'] : ($row['meta_title'] ?? '')));
                if ($title !== '' && strpos($title, ' | ') === false) {
                    $db->update('cms_lang', ['head_seo_title' => pSQL($titled($title))], 'id_cms = ' . (int) $idCms . ' AND id_lang = ' . (int) $id);
                }
            }
        }

        // Brands
        $translator = Context::getContext()->getTranslator();
        foreach ($this->demo['brands'] ?? [] as $b) {
            $idBrand = $this->id('brand', $b['key']);
            if (!$idBrand || empty($byBrand[$b['key']])) {
                continue;
            }
            foreach ($this->langs as $id => $iso) {
                $names = array_slice($byBrand[$b['key']][$id] ?? [], 0, 3);
                if (!$names) {
                    continue;
                }
                $locale = (string) ((Language::getLanguage($id) ?: [])['locale'] ?? 'en-US');
                $text = $translator->trans('Discover %brand% at %shop%: %products% and more.', ['%brand%' => $b['name'], '%shop%' => $shop, '%products%' => implode(', ', $names)], 'Modules.Hbkit.Shop', $locale);
                $db->update('manufacturer_lang', [
                    'meta_title' => pSQL($titled($b['name'])),
                    'meta_description' => pSQL(mb_substr($text, 0, 255)),
                    'short_description' => pSQL('<p>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</p>', true),
                ], 'id_manufacturer = ' . (int) $idBrand . ' AND id_lang = ' . (int) $id);
            }
        }
        $this->log('Search engine titles and descriptions written.');
    }

    /** HTML -> one line of plain text. */
    private static function plain(string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />', '</li>'], ' ', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    /** Whole sentences up to $max characters; if the first sentence is longer, it is cut at a word. */
    private static function sentences(string $text, int $max): string
    {
        if (mb_strlen($text) <= $max) {
            return $text;
        }
        $parts = preg_split('/(?<=[.!?…])\s+/u', $text) ?: [$text];
        $out = '';
        foreach ($parts as $part) {
            $next = $out === '' ? $part : $out . ' ' . $part;
            if (mb_strlen($next) > $max) {
                break;
            }
            $out = $next;
        }
        if ($out === '') {
            $out = mb_substr($text, 0, $max - 1);
            $out = rtrim(mb_substr($out, 0, (int) mb_strrpos($out, ' ') ?: $max - 1), ' ,;:') . '…';
        }

        return $out;
    }

    private function importHome(): void
    {
        $slug = preg_replace('/[^a-z0-9_-]/', '', (string) ($this->demo['meta']['slug'] ?? 'demo'));
        $imgDir = _PS_IMG_DIR_ . HbkitRenderer::IMG_DIR . $slug . '/';
        @mkdir($imgDir, 0755, true);
        @copy(_PS_IMG_DIR_ . 'index.php', _PS_IMG_DIR_ . HbkitRenderer::IMG_DIR . 'index.php');
        $copyImage = function ($rel) use ($imgDir, $slug) {
            $file = $rel ? $this->image($rel) : '';
            if (!$file) {
                return '';
            }
            $name = basename($file);
            copy($file, $imgDir . $name);

            return $slug . '/' . $name;
        };

        $pos = 0;
        foreach ($this->demo['home'] ?? [] as $s) {
            if (!HbkitTypes::get($s['type'])) {
                continue;
            }
            $settings = $s['settings'] ?? [];
            foreach (['image'] as $k) {
                if (!empty($settings[$k])) {
                    $settings[$k] = $copyImage($settings[$k]);
                }
            }
            if (!empty($settings['head_link'])) {
                $settings['head_link'] = $this->link($settings['head_link']);
            }
            if (!empty($settings['link'])) {
                $settings['link'] = $this->link($settings['link']);
            }
            if (!empty($settings['id_product'])) {
                $settings['id_product'] = $this->id('product', $settings['id_product']);
            }
            $section = new HbkitSection();
            $section->id_shop = $this->idShop;
            $section->type = $s['type'];
            $section->position = ++$pos;
            $section->active = (int) ($s['active'] ?? 1);
            $section->settings = json_encode($settings);
            $section->title = $this->lang($s['title'] ?? '');
            $section->subtitle = $this->lang($s['subtitle'] ?? '');
            $section->content = $this->lang($s['content'] ?? '');
            $this->save($section);

            foreach ($s['items'] ?? [] as $ipos => $it) {
                $data = $it['data'] ?? [];
                foreach (['image', 'image_mobile'] as $k) {
                    if (!empty($data[$k])) {
                        $data[$k] = $copyImage($data[$k]);
                    }
                }
                if (!empty($data['link'])) {
                    $data['link'] = $this->link($data['link']);
                }
                if (isset($data['id_category'])) {
                    $data['id_category'] = $this->id('category', $data['id_category']);
                }
                if (isset($data['id_manufacturer'])) {
                    $data['id_manufacturer'] = $this->id('brand', $data['id_manufacturer']);
                }
                $item = new HbkitItem();
                $item->id_hbkit_section = (int) $section->id;
                $item->position = $ipos + 1;
                $item->active = 1;
                $item->data = json_encode($data);
                foreach (['title', 'subtitle', 'text', 'button', 'url'] as $f) {
                    $item->$f = $this->lang($it[$f] ?? '');
                }
                $this->save($item);
            }
        }
        $this->log($pos . ' homepage sections created.');
    }

    private function importConfig(): void
    {
        // Kit settings that point at features, groups, pages or texts of a demo: clear them first, so a value
        // from a demo imported before (for example a feature id that no longer exists) does not remain.
        $keys = ['HBKIT_CARD_FEATURE', 'HBKIT_PRODUCT_FEATURES', 'HBKIT_FEATURES_NAMED', 'HBKIT_QUICK_FACET', 'HBKIT_QUICK_FEATURE',
            'HBKIT_SIZE_GUIDE', 'HBKIT_SCALE_FEATURE', 'HBKIT_SCALE_RANGE', 'HBKIT_NEXT_CATEGORY', 'HBKIT_FIT_FEATURE', 'HBKIT_STAMP_FEATURE'];
        foreach ($keys as $key) {
            Configuration::updateValue($key, '');
        }
        foreach (array_keys(Hbkit::TEXT_KEYS) as $key) {
            Configuration::updateValue($key, array_fill_keys(Language::getIDs(false), ''));
        }
        foreach ($this->demo['config'] ?? [] as $key => $value) {
            if (is_string($value) && preg_match('/^(category|product|cms|brand|feature|group):[^,]+(,(category|product|cms|brand|feature|group):[^,]+)*$/', $value)) {
                $refs = [];
                foreach (explode(',', $value) as $ref) {
                    [$kind, $k] = explode(':', $ref, 2);
                    $refs[] = $this->id($kind, $k);
                }
                $value = count($refs) > 1 ? implode(',', $refs) : $refs[0];
            }
            Configuration::updateValue($key, is_array($value) ? $this->lang($value) : $value);
        }
        foreach ($this->demo['hooks']['unhook'] ?? [] as $hook => $modules) {
            foreach ($modules as $name) {
                $m = Module::getInstanceByName($name);
                if ($m && $m->id) {
                    $m->unregisterHook($hook);
                }
            }
        }
        foreach ($this->demo['hooks']['hook'] ?? [] as $hook => $modules) {
            foreach ($modules as $name) {
                $m = Module::getInstanceByName($name);
                if ($m && $m->id && !$m->isRegisteredInHook($hook)) {
                    $m->registerHook($hook);
                }
            }
        }
        $this->log('Configuration values and hooks applied.');
    }

    /**
     * Keep only the filters a demo lists, in that order (catalog.facets: "feature:warmth", "group:size", "price",
     * "manufacturer", "availability", "extras", "condition", "weight", "category"). Without the list every filter stays.
     */
    private function limitFacets(): void
    {
        $want = $this->demo['catalog']['facets'] ?? [];
        if (!$want) {
            return;
        }
        $types = ['price' => 'price', 'manufacturer' => 'manufacturer', 'availability' => 'availability', 'extras' => 'extras', 'condition' => 'condition', 'weight' => 'weight', 'category' => 'category'];
        $keep = [];
        foreach ($want as $pos => $spec) {
            [$kind, $key] = array_pad(explode(':', (string) $spec, 2), 2, '');
            if ($kind === 'feature' && !empty($this->ids['feature'][$key])) {
                $keep[] = ['id_feature', (int) $this->ids['feature'][$key]];
            } elseif ($kind === 'group' && !empty($this->ids['group'][$key])) {
                $keep[] = ['id_attribute_group', (int) $this->ids['group'][$key]];
            } elseif (isset($types[$kind])) {
                $keep[] = [$types[$kind], 0];
            }
        }
        $db = Db::getInstance();
        $rows = $db->executeS('SELECT id_layered_category, type, id_value FROM `' . _DB_PREFIX_ . 'layered_category`') ?: [];
        foreach ($rows as $r) {
            $pos = null;
            foreach ($keep as $i => [$type, $id]) {
                if ($r['type'] === $type && ($id === 0 || (int) $r['id_value'] === $id)) {
                    $pos = $i;
                    break;
                }
            }
            if ($pos === null) {
                $db->delete('layered_category', 'id_layered_category = ' . (int) $r['id_layered_category']);
            } else {
                $db->update('layered_category', ['position' => $pos], 'id_layered_category = ' . (int) $r['id_layered_category']);
            }
        }
        $this->log(count($keep) . ' filters kept on category pages.');
    }

    private function finish(): void
    {
        $faceted = Module::getInstanceByName('ps_facetedsearch');
        if ($faceted && Module::isEnabled('ps_facetedsearch')) {
            Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'layered_filter`');
            Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'layered_filter_shop`');
            $faceted->rebuildLayeredStructure();
            $faceted->createDefaultTemplate();
            $faceted->buildLayeredCategories();
            $this->limitFacets();
            $faceted->fullPricesIndexProcess(0, false, false);
            $faceted->indexAttributes();
            $faceted->indexFeatures();
            $faceted->invalidateLayeredFilterBlockCache();
        }
        // ps_mainmenu keeps a rendered menu cache per language
        if (is_dir(_PS_CACHE_DIR_ . 'ps_mainmenu')) {
            Tools::deleteDirectory(_PS_CACHE_DIR_ . 'ps_mainmenu', false);
        }
        Tools::generateHtaccess();
        Tools::clearAllCache();
        $this->log('Search and filter indexes rebuilt, caches cleared.');
    }
}
