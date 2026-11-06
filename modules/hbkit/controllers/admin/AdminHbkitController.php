<?php
/**
 * Back office: homepage sections, theme options, top bar and demo import.
 *
 * @author    iamshoaibktk@gmail.com (WhatsApp: +923459016286)
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminHbkitController extends ModuleAdminController
{
    /** @var Hbkit */
    public $module;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    private function t(string $s, array $p = []): string
    {
        return $this->trans($s, $p, 'Modules.Hbkit.Admin');
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addCSS($this->module->getPathUri() . 'views/css/admin.css');
        $this->addJS($this->module->getPathUri() . 'views/js/admin.js');
    }

    private function url(array $params = []): string
    {
        return $this->context->link->getAdminLink('AdminHbkit', true, [], $params);
    }

    public function postProcess()
    {
        if (Tools::getValue('ajax') && Tools::getIsset('hbkit_ajax')) {
            $this->ajaxAction((string) Tools::getValue('hbkit_ajax'));
        }
        if (!Tools::getIsset('hbkit_action')) {
            return;
        }
        $action = (string) Tools::getValue('hbkit_action');
        $id = (int) Tools::getValue('id_section');
        try {
            switch ($action) {
                case 'save_options': $this->saveOptions(); $this->confirmations[] = $this->t('Design saved. Open the shop to see it.'); break;
                case 'save_topbar': $this->saveTopbar(); $this->confirmations[] = $this->t('Top bar saved.'); break;
                case 'add_section': $id = $this->addSection((string) Tools::getValue('type')); Tools::redirectAdmin($this->url(['view' => 'edit', 'id_section' => $id])); break;
                case 'save_section': $this->saveSection($id); $this->confirmations[] = $this->t('Section saved.'); break;
                case 'toggle': $s = new HbkitSection($id); $s->active = !$s->active; $s->update(); Tools::redirectAdmin($this->url()); break;
                case 'delete': (new HbkitSection($id))->delete(); Tools::redirectAdmin($this->url()); break;
                case 'move': $this->move($id, (string) Tools::getValue('dir')); Tools::redirectAdmin($this->url()); break;
                case 'duplicate': $this->duplicate($id); Tools::redirectAdmin($this->url()); break;
                case 'import': $this->runImport(); break;
            }
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /** Instant saves from the sections list: order and visibility. */
    private function ajaxAction(string $action): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $ok = false;
        if ($action === 'order') {
            $ids = array_values(array_filter(array_map('intval', explode(',', (string) Tools::getValue('ids')))));
            foreach ($ids as $pos => $id) {
                Db::getInstance()->update('hbkit_section', ['position' => $pos + 1], 'id_hbkit_section = ' . (int) $id . ' AND id_shop = ' . (int) $this->context->shop->id);
            }
            $ok = (bool) $ids;
        } elseif ($action === 'toggle') {
            $s = new HbkitSection((int) Tools::getValue('id_section'));
            if (Validate::isLoadedObject($s)) {
                $s->active = (int) (bool) Tools::getValue('active');
                $ok = (bool) $s->update();
            }
        }
        echo json_encode(['ok' => $ok, 'message' => $ok ? $this->t('Saved') : $this->t('Could not save. Reload the page and try again.')]);
        exit;
    }

    public function initContent()
    {
        parent::initContent();
        $this->module->ensureHooks();
        $view = (string) Tools::getValue('view', 'sections');
        $languages = Language::getLanguages(false);
        $this->context->smarty->assign([
            'hbk_view' => $view,
            'hbk_urls' => [
                'sections' => $this->url(),
                'options' => $this->url(['view' => 'options']),
                'design' => array_map(function ($g) { return $this->url(['view' => 'options', 'group' => $g]); }, array_combine(array_keys($this->designGroups()), array_keys($this->designGroups()))),
                'topbar' => $this->url(['view' => 'topbar']),
                'import' => $this->url(['view' => 'import']),
                'front' => $this->context->link->getPageLink('index'),
                'self' => $this->url(['view' => $view] + (Tools::getValue('id_section') ? ['id_section' => (int) Tools::getValue('id_section')] : []) + (Tools::getValue('group') ? ['group' => preg_replace('/[^a-z]/', '', (string) Tools::getValue('group'))] : [])),
            ],
            'hbk_design_nav' => array_map(function ($g) { return ['title' => $g[0], 'icon' => $g[2]]; }, $this->designGroups()),
            'hbk_group_key' => preg_replace('/[^a-z]/', '', (string) Tools::getValue('group', 'colors')),
            'hbk_languages' => $languages,
            'hbk_default_lang' => (int) Configuration::get('PS_LANG_DEFAULT'),
            'hbk_id_lang' => (int) Configuration::get('PS_LANG_DEFAULT'),
            'hbk_token' => Tools::getAdminTokenLite('AdminHbkit'),
            'hbk_demo' => (string) Configuration::get('HBKIT_DEMO'),
            'hbk_author_img' => $this->module->getPathUri() . 'views/img/author.jpg',
        ]);
        $method = 'view' . ucfirst(preg_replace('/[^a-z]/', '', $view));
        $body = method_exists($this, $method) ? $this->$method() : $this->viewSections();
        $this->context->smarty->assign('hbk_body', $body);
        $this->content = $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/layout.tpl');
        $this->context->smarty->assign('content', $this->content);
    }

    private function fetchTpl(string $name, array $vars): string
    {
        $this->context->smarty->assign($vars);

        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/' . $name . '.tpl');
    }

    /* ------------------------------------------------------------------ */
    /* Sections                                                            */
    /* ------------------------------------------------------------------ */

    private function form(): HbkitForm
    {
        return new HbkitForm(function ($s) { return $this->t($s); });
    }

    /** Public URL of a kit image ("uploads/x.jpg" or a demo file). */
    private function imageUrl(string $file): string
    {
        return $file === '' ? '' : _PS_IMG_ . HbkitRenderer::IMG_DIR . $file;
    }

    /** First picture of a section, for its row in the list. */
    private function sectionThumb(array $row, array $settings): string
    {
        foreach (['image'] as $k) {
            if (!empty($settings[$k])) {
                return $this->imageUrl((string) $settings[$k]);
            }
        }
        foreach (HbkitItem::getForSection((int) $row['id_hbkit_section'], null, false) as $it) {
            $data = json_decode((string) $it['data'], true) ?: [];
            if (!empty($data['image'])) {
                return $this->imageUrl((string) $data['image']);
            }
            if (!empty($data['id_category'])) {
                $cat = new Category((int) $data['id_category'], (int) $this->context->language->id);
                if ($cat->id_image) {
                    return $this->context->link->getCatImageLink($cat->link_rewrite, (int) $cat->id_image);
                }
            }
        }

        return '';
    }

    private function viewSections(): string
    {
        $form = $this->form();
        $rows = [];
        foreach (HbkitSection::getAll((int) $this->context->shop->id, (int) $this->context->language->id) as $r) {
            $info = $form->info((string) $r['type']);
            $settings = json_decode((string) $r['settings'], true) ?: [];
            $count = count(HbkitItem::getForSection((int) $r['id_hbkit_section'], null, false));
            $title = trim((string) $r['title']);
            if ($r['type'] === 'hero' || $title === '') {
                foreach (HbkitItem::getForSection((int) $r['id_hbkit_section'], (int) $this->context->language->id) as $it) {
                    if (trim((string) $it['title']) !== '') {
                        $title = trim(strip_tags((string) $it['title']));
                        break;
                    }
                }
            }
            $meta = $info['name'];
            if ($info['items']) {
                $meta .= ', ' . $count . ' ' . Tools::strtolower($count === 1 ? $info['items']['one'] : $info['items']['many']);
            }
            if (!$r['active']) {
                $meta .= ', ' . $this->t('hidden');
            }
            $rows[] = [
                'id' => (int) $r['id_hbkit_section'],
                'active' => (int) $r['active'],
                'title' => $title !== '' ? $title : $info['name'],
                'meta' => $meta,
                'thumb' => $this->sectionThumb($r, $settings),
                'edit' => $this->url(['view' => 'edit', 'id_section' => (int) $r['id_hbkit_section']]),
                'duplicate' => $this->url(['hbkit_action' => 'duplicate', 'id_section' => (int) $r['id_hbkit_section']]),
                'delete' => $this->url(['hbkit_action' => 'delete', 'id_section' => (int) $r['id_hbkit_section']]),
            ];
        }
        $types = [];
        foreach (array_keys(HbkitTypes::all()) as $type) {
            $types[] = ['key' => $type, 'name' => $form->info($type)['name'], 'blurb' => $form->blurb($type), 'svg' => HbkitPreviews::section($type)];
        }

        return $this->fetchTpl('sections', ['hbk_sections' => $rows, 'hbk_add_types' => $types, 'hbk_ajax' => $this->url(['ajax' => 1])]);
    }

    private function viewEdit(): string
    {
        $id = (int) Tools::getValue('id_section');
        $section = new HbkitSection($id);
        if (!Validate::isLoadedObject($section)) {
            return $this->viewSections();
        }
        $form = $this->form();
        $type = (string) $section->type;
        $info = $form->info($type);
        $itemDef = $form->itemFields($type);
        $idLang = (int) Configuration::get('PS_LANG_DEFAULT');
        $items = [];
        foreach (HbkitItem::getForSection($id, null, false) as $row) {
            $item = new HbkitItem((int) $row['id_hbkit_item']);
            $items[] = $this->presentItem($item, $type);
        }
        $settingsDef = $form->settings($type);
        foreach ($settingsDef as $g => $fields) {
            foreach ($fields as $i => $f) {
                if ($f['ctl'] === 'pics') {
                    foreach ($f['options'] as $val => $o) {
                        $settingsDef[$g][$i]['options'][$val][2] = HbkitPreviews::svg($type . '.' . $f['key'], (string) $val);
                    }
                }
            }
        }
        $blank = ['id' => 0, 'active' => 1, 'position' => 999, 'data' => [], 'title' => [], 'subtitle' => [], 'text' => [], 'button' => [], 'url' => [], 'thumb' => '', 'name' => '', 'meta' => ''];
        $tokens = Hbkit::getTokens();

        return $this->fetchTpl('edit', [
            'hbk_section' => [
                'id' => (int) $section->id, 'type' => $type, 'active' => (int) $section->active,
                'title' => $section->title, 'subtitle' => $section->subtitle, 'content' => $section->content,
                'settings' => HbkitTypes::settingsWithDefaults($type, $section->getSettings()),
            ],
            'hbk_info' => $info,
            'hbk_settings' => $settingsDef,
            'hbk_heading' => $form->heading($type),
            'hbk_items' => $items,
            'hbk_blank' => $blank,
            'hbk_item_def' => $itemDef,
            'hbk_has_items' => (bool) $info['items'],
            'hbk_lists' => $this->pickerLists(),
            'hbk_icons' => HbkitTypes::ICONS,
            'hbk_swatch' => ['surface' => $tokens['surface'], 'dark' => $tokens['dark'], 'primary' => $tokens['primary'], 'accent' => $tokens['accent']],
            'hbk_img_base' => _PS_IMG_ . HbkitRenderer::IMG_DIR,
            'hbk_id_lang' => $idLang,
        ]);
    }

    /** Item row for the editor: name, picture and a short description. */
    private function presentItem(HbkitItem $item, string $type): array
    {
        $idLang = (int) Configuration::get('PS_LANG_DEFAULT');
        $data = $item->getData();
        $name = trim(strip_tags((string) ($item->title[$idLang] ?? '')));
        $thumb = !empty($data['image']) ? $this->imageUrl((string) $data['image']) : '';
        $meta = '';
        if (!empty($data['id_category'])) {
            $cat = new Category((int) $data['id_category'], $idLang);
            $name = $name !== '' ? $name : (string) $cat->name;
            $meta = $this->t('Category') . ': ' . $cat->name;
            if ($thumb === '' && $cat->id_image) {
                $thumb = $this->context->link->getCatImageLink($cat->link_rewrite, (int) $cat->id_image);
            }
        }
        if (!empty($data['id_manufacturer'])) {
            $name = Manufacturer::getNameById((int) $data['id_manufacturer']);
        }
        if ($type === 'products' && !empty($data['source'])) {
            $meta = $this->form()->itemFields('products')['data'][0]['options'][$data['source']] ?? $data['source'];
        }
        if ($type === 'usp' && !empty($data['icon'])) {
            $meta = (string) ($item->text[$idLang] ?? '');
        }
        if ($type === 'testimonials') {
            $meta = $name;
            $name = Tools::truncateString(strip_tags((string) ($item->text[$idLang] ?? '')), 60);
        }
        if (in_array($type, ['hero', 'banners', 'image_text'], true) && !empty($item->button[$idLang])) {
            $meta = $this->t('Button') . ': ' . $item->button[$idLang];
        }

        return [
            'id' => (int) $item->id, 'active' => (int) $item->active, 'position' => (int) $item->position, 'data' => $data,
            'title' => $item->title, 'subtitle' => $item->subtitle, 'text' => $item->text, 'button' => $item->button, 'url' => $item->url,
            'thumb' => $thumb, 'name' => $name, 'meta' => $meta,
        ];
    }

    /** Choices for link pickers and selects. */
    private function pickerLists(): array
    {
        $idLang = (int) $this->context->language->id;
        $cms = [];
        foreach (CMS::getCMSPages($idLang, null, false) as $c) {
            $cms[(int) $c['id_cms']] = $c['meta_title'];
        }
        $products = [];
        $rows = Db::getInstance()->executeS('SELECT p.id_product, pl.name FROM `' . _DB_PREFIX_ . 'product` p JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.id_product = p.id_product AND pl.id_lang = ' . $idLang . ' AND pl.id_shop = ' . (int) $this->context->shop->id . ' ORDER BY pl.name LIMIT 1000');
        foreach ($rows ?: [] as $r) {
            $products[(int) $r['id_product']] = $r['name'];
        }
        $brands = [];
        foreach (Manufacturer::getManufacturers(false, $idLang, false) as $m) {
            $brands[(int) $m['id_manufacturer']] = $m['name'];
        }

        $attrs = [];
        foreach (AttributeGroup::getAttributesGroups($idLang) as $g) {
            foreach (AttributeGroup::getAttributes($idLang, (int) $g['id_attribute_group']) as $a) {
                $attrs[(int) $a['id_attribute']] = $g['public_name'] . ': ' . $a['name'];
            }
        }

        return [
            'category' => $this->categoryOptions(),
            'attribute' => $attrs,
            'cms' => $cms,
            'product' => $products,
            'manufacturer' => $brands,
            'page' => [
                'new-products' => $this->t('New products'), 'best-sales' => $this->t('Best sellers'), 'prices-drop' => $this->t('Prices drop'),
                'manufacturer' => $this->t('All brands'), 'contact' => $this->t('Contact us'), 'stores' => $this->t('Our stores'), 'sitemap' => $this->t('Sitemap'),
            ],
            'types' => [
                '' => $this->t('Nowhere'), 'category' => $this->t('Category'), 'cms' => $this->t('Page'), 'product' => $this->t('Product'),
                'manufacturer' => $this->t('Brand'), 'page' => $this->t('Shop page'),
                'attribute' => $this->t('Products in one size or colour'),
            ],
        ];
    }

    private function categoryOptions(): array
    {
        $out = [];
        $idLang = (int) $this->context->language->id;
        $rows = Db::getInstance()->executeS('SELECT c.id_category, c.level_depth, cl.name FROM `' . _DB_PREFIX_ . 'category` c
            JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON cl.id_category = c.id_category AND cl.id_lang = ' . $idLang . ' AND cl.id_shop = ' . (int) $this->context->shop->id . '
            WHERE c.level_depth > 0 ORDER BY c.nleft');
        foreach ($rows ?: [] as $r) {
            $out[(int) $r['id_category']] = str_repeat('— ', max(0, (int) $r['level_depth'] - 1)) . $r['name'];
        }

        return $out;
    }

    private function addSection(string $type): int
    {
        if (!HbkitTypes::get($type)) {
            throw new PrestaShopException('Unknown section type.');
        }
        $s = new HbkitSection();
        $s->id_shop = (int) $this->context->shop->id;
        $s->type = $type;
        $s->active = 0;
        $s->settings = json_encode(HbkitTypes::settingsWithDefaults($type, []));
        foreach (Language::getLanguages(false) as $l) {
            $s->title[(int) $l['id_lang']] = HbkitTypes::get($type)['label'];
        }
        $s->add();

        return (int) $s->id;
    }

    private function saveSection(int $id): void
    {
        $section = new HbkitSection($id);
        if (!Validate::isLoadedObject($section)) {
            throw new PrestaShopException('Section not found.');
        }
        $type = HbkitTypes::get($section->type);
        $section->active = (int) Tools::getValue('active');
        $section->settings = json_encode($this->readFields($type['settings'] + HbkitTypes::COMMON, (array) Tools::getValue('settings', []), 'settings', $section->getSettings()));
        foreach (Language::getLanguages(false) as $l) {
            $idLang = (int) $l['id_lang'];
            $section->title[$idLang] = (string) Tools::getValue('title_' . $idLang);
            $section->subtitle[$idLang] = (string) Tools::getValue('subtitle_' . $idLang);
            $section->content[$idLang] = (string) Tools::getValue('content_' . $idLang);
        }
        $section->update();

        // Items
        $posted = (array) Tools::getValue('items', []);
        $existing = [];
        foreach (HbkitItem::getForSection($id, null, false) as $r) {
            $existing[(int) $r['id_hbkit_item']] = true;
        }
        foreach ($posted as $key => $row) {
            $idItem = (int) ($row['id'] ?? 0);
            if (!empty($row['delete'])) {
                if ($idItem && isset($existing[$idItem])) {
                    (new HbkitItem($idItem))->delete();
                }
                continue;
            }
            $item = $idItem && isset($existing[$idItem]) ? new HbkitItem($idItem) : new HbkitItem();
            if (!$item->id && empty($row['is_new'])) {
                continue;
            }
            $item->id_hbkit_section = $id;
            $item->active = (int) !empty($row['active']);
            $item->position = (int) ($row['position'] ?? 0);
            $item->data = json_encode($this->readFields($type['item'], (array) ($row['data'] ?? []), 'items_' . $key, $item->id ? $item->getData() : []));
            foreach (Language::getLanguages(false) as $l) {
                $idLang = (int) $l['id_lang'];
                foreach (['title', 'subtitle', 'text', 'button', 'url'] as $f) {
                    $value = (string) ($row[$f][$idLang] ?? '');
                    $item->{$f}[$idLang] = $f === 'url' && $value !== '' && !Validate::isUrl($value) ? '' : $value;
                }
            }
            $item->id ? $item->update() : $item->add();
        }
    }

    /** Reads posted values for a field schema; image fields come from uploads ($_FILES[$fileKey]). */
    private function readFields(array $schema, array $posted, string $fileKey, array $current): array
    {
        $out = [];
        foreach ($schema as $key => $field) {
            $kind = $field[0];
            $value = $posted[$key] ?? null;
            switch ($kind) {
                case 'image':
                    $out[$key] = $current[$key] ?? '';
                    if (!empty($posted[$key . '_remove'])) {
                        $out[$key] = '';
                    }
                    $upload = $this->upload($fileKey, $key);
                    if ($upload) {
                        $out[$key] = $upload;
                    }
                    break;
                case 'int':
                case 'category':
                case 'product':
                case 'manufacturer':
                    $out[$key] = (int) $value;
                    break;
                case 'bool':
                    $out[$key] = (int) !empty($value);
                    break;
                case 'select':
                    $out[$key] = in_array((string) $value, $field[1], true) ? (string) $value : $field[1][0];
                    break;
                case 'color':
                    $out[$key] = Hbkit::isValidColor($value) ? (string) $value : '';
                    break;
                case 'date':
                    $out[$key] = preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $value) ? str_replace('T', ' ', (string) $value) : '';
                    break;
                case 'link':
                    $out[$key] = preg_match('/^(category|cms|product|manufacturer|page):[a-z0-9_-]+$/i', trim((string) $value)) ? trim((string) $value) : '';
                    break;
                case 'icon':
                    $out[$key] = preg_replace('/[^a-z0-9_]/', '', (string) $value);
                    break;
                case 'class':
                    // Not in the form: keep the saved value unless one is posted.
                    $out[$key] = trim(preg_replace('/[^a-z0-9 _-]/i', '', (string) ($value ?? ($current[$key] ?? ''))));
                    break;
                default:
                    $out[$key] = strip_tags((string) $value);
            }
        }

        return $out;
    }

    /** Handles $_FILES[$group]['name'][$key] (sections) or $_FILES['items']['name'][$n]['data'][$key] (items). */
    private function upload(string $group, string $key): string
    {
        if (strpos($group, 'items_') === 0) {
            $n = substr($group, 6);
            $f = $_FILES['items'] ?? null;
            $tmp = $f['tmp_name'][$n]['data'][$key] ?? '';
            $name = $f['name'][$n]['data'][$key] ?? '';
            $err = $f['error'][$n]['data'][$key] ?? UPLOAD_ERR_NO_FILE;
        } else {
            $f = $_FILES[$group] ?? null;
            $tmp = $f['tmp_name'][$key] ?? '';
            $name = $f['name'][$key] ?? '';
            $err = $f['error'][$key] ?? UPLOAD_ERR_NO_FILE;
        }
        if ($err !== UPLOAD_ERR_OK || !$tmp || !is_uploaded_file($tmp)) {
            return '';
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)) {
            throw new PrestaShopException($this->t('Only JPG, PNG, WebP, GIF and SVG images can be uploaded.'));
        }
        if ($ext !== 'svg' && ImageManager::validateUpload(['tmp_name' => $tmp, 'name' => $name, 'size' => filesize($tmp), 'type' => mime_content_type($tmp), 'error' => 0])) {
            throw new PrestaShopException($this->t('The image could not be uploaded.'));
        }
        if ($ext === 'svg' && preg_match('/<script|on\w+\s*=/i', (string) file_get_contents($tmp))) {
            throw new PrestaShopException($this->t('This SVG file contains scripts and was rejected.'));
        }
        $dir = _PS_IMG_DIR_ . HbkitRenderer::IMG_DIR . 'uploads/';
        @mkdir($dir, 0755, true);
        $file = Tools::str2url(pathinfo($name, PATHINFO_FILENAME)) . '-' . substr(sha1_file($tmp), 0, 8) . '.' . $ext;
        if (!move_uploaded_file($tmp, $dir . $file)) {
            throw new PrestaShopException($this->t('The image could not be uploaded.'));
        }

        return 'uploads/' . $file;
    }

    private function move(int $id, string $dir): void
    {
        $rows = HbkitSection::getAll((int) $this->context->shop->id);
        $ids = array_map(function ($r) { return (int) $r['id_hbkit_section']; }, $rows);
        $i = array_search($id, $ids, true);
        if ($i === false) {
            return;
        }
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if (!isset($ids[$j])) {
            return;
        }
        [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
        foreach ($ids as $pos => $sid) {
            Db::getInstance()->update('hbkit_section', ['position' => $pos + 1], 'id_hbkit_section = ' . (int) $sid);
        }
    }

    private function duplicate(int $id): void
    {
        $src = new HbkitSection($id);
        if (!Validate::isLoadedObject($src)) {
            return;
        }
        $copy = $src->duplicateObject();
        if (!$copy) {
            return;
        }
        $copy->active = 0;
        $copy->position = 0;
        $copy->update();
        foreach (HbkitItem::getForSection($id, null, false) as $r) {
            $item = (new HbkitItem((int) $r['id_hbkit_item']))->duplicateObject();
            if ($item) {
                $item->id_hbkit_section = (int) $copy->id;
                $item->update();
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /* Theme options and top bar                                           */
    /* ------------------------------------------------------------------ */

    /** Design groups shown in the left menu: key => [title, intro, icon, token keys]. */
    public function designGroups(): array
    {
        return [
            'colors' => [$this->t('Colours'), $this->t('Brand colours used by buttons, links, prices, backgrounds and the footer.'), 'palette',
                ['primary', 'primary_contrast', 'accent', 'bg', 'surface', 'text', 'muted', 'border', 'field_border', 'sale', 'dark', 'dark_text']],
            'type' => [$this->t('Fonts and shapes'), $this->t('Typefaces, heading style and how round corners and buttons are.'), 'text_fields',
                ['font_heading', 'font_body', 'heading_weight', 'heading_transform', 'heading_spacing', 'radius', 'button_radius', 'button_transform', 'field_height']],
            'layout' => [$this->t('Header and footer'), $this->t('How the top of every page and the footer look.'), 'web',
                ['header_style', 'header_sticky', 'search_style', 'search_trigger', 'footer_style']],
            'cards' => [$this->t('Product cards'), $this->t('The product tiles on the homepage, category pages and in carousels.'), 'grid_view',
                ['card_style']],
            'cart' => [$this->t('Side cart'), $this->t('What customers see after "Add to cart", and the free delivery bar.'), 'shopping_bag',
                ['cart_style', 'free_bar', 'cart_word']],
            'cartpage' => [$this->t('Cart page'), $this->t('The layout of the cart page before checkout.'), 'shopping_cart',
                ['cart_layout']],
            'listing' => [$this->t('Category pages'), $this->t('Filters, the category title area and how many products per row.'), 'view_module',
                ['listing_layout', 'category_header', 'grid_columns']],
            'product' => [$this->t('Product page'), $this->t('Gallery, buy box and how the description and details are shown.'), 'inventory_2',
                ['product_layout', 'product_thumbs', 'product_tabs']],
        ];
    }

    /** Label, help text and choice labels of each token. */
    private function tokenMeta(): array
    {
        $t = function ($s) { return $this->t($s); };

        return [
            'primary' => [$t('Main colour'), $t('Buttons, links and highlights.')],
            'primary_contrast' => [$t('Text on main colour'), ''],
            'accent' => [$t('Accent colour'), $t('Small details: icons, labels, progress bars.')],
            'bg' => [$t('Page background'), ''],
            'surface' => [$t('Panel background'), $t('Boxes, image backgrounds and the side cart footer.')],
            'text' => [$t('Text'), ''],
            'muted' => [$t('Soft text'), ''],
            'border' => [$t('Lines'), ''],
            'sale' => [$t('Sale price'), ''],
            'field_border' => [$t('Field borders'), $t('Border of text fields, selects and checkboxes.')],
            'field_height' => [$t('Field height'), $t('In pixels, for example 46.')],
            'dark' => [$t('Dark footer background'), ''],
            'dark_text' => [$t('Dark footer text'), ''],
            'font_heading' => [$t('Heading font'), $t('Family name declared by the theme.')],
            'font_body' => [$t('Text font'), ''],
            'heading_weight' => [$t('Heading weight'), $t('400 regular, 600 semi-bold, 700 bold.')],
            'heading_transform' => [$t('Heading letters'), '', ['none' => $t('As typed'), 'uppercase' => $t('Capitals')]],
            'heading_spacing' => [$t('Heading letter spacing'), $t('In em, for example 0.05.')],
            'radius' => [$t('Corner radius'), $t('In pixels. 0 gives square corners.')],
            'button_radius' => [$t('Button radius'), $t('In pixels. 40 gives pill buttons.')],
            'button_transform' => [$t('Button letters'), '', ['none' => $t('As typed'), 'uppercase' => $t('Capitals')]],
            'header_style' => [$t('Header'), '', [
                'classic' => [$t('Classic'), $t('Logo left, menu next to it.')],
                'centered' => [$t('Centred'), $t('Logo in the middle, menu below.')],
                'dark' => [$t('Dark'), $t('Header on the dark colour.')],
                'transparent' => [$t('Over the hero'), $t('Header sits on the first homepage image.')],
            ]],
            'header_sticky' => [$t('Header while scrolling'), '', [
                'always' => [$t('Always visible'), $t('Stays at the top of the screen.')],
                'up' => [$t('Back when scrolling up'), $t('Hides while you read down the page and comes back as soon as you scroll up.')],
                'off' => [$t('Scrolls away'), $t('Only at the top of the page.')],
            ]],
            'search_style' => [$t('Search'), '', [
                'bar' => [$t('Search field'), $t('A search field in the header.')],
                'panel' => [$t('Search panel'), $t('The word "Search" opens a panel with live results.')],
                'field' => [$t('Field with results'), $t('A search field in the header that lists products while you type.')],
            ]],
            'search_trigger' => [$t('Search button'), $t('How the search panel is opened from the header.'), [
                'word' => [$t('The word "Search"'), ''], 'icon' => [$t('Magnifier icon'), ''],
            ]],
            'cart_word' => [$t('Cart wording'), $t('The word used in the header, side cart and buttons.'), [
                'cart' => [$t('Cart'), ''], 'bag' => [$t('Bag'), ''],
            ]],
            'footer_style' => [$t('Footer'), '', [
                'dark' => [$t('Dark'), ''], 'light' => [$t('Light'), ''], 'primary' => [$t('Main colour'), ''],
            ]],
            'card_style' => [$t('Card style'), '', [
                'plain' => [$t('Plain'), $t('Picture, name, price and button.')],
                'bordered' => [$t('Bordered'), $t('A thin frame around each card.')],
                'shadow' => [$t('Shadow'), $t('Cards float with a soft shadow.')],
                'minimal' => [$t('Minimal'), $t('Centred text and a text link.')],
                'overlay' => [$t('Button on picture'), $t('"Add to cart" appears over the picture.')],
                'boxed' => [$t('Rounded box'), $t('Soft panel and a round cart button.')],
                'editorial' => [$t('Editorial'), $t('Tall picture, small capitals.')],
                'framed' => [$t('Framed'), $t('Picture in a mount, outline button.')],
                'compact' => [$t('Compact'), $t('Dense grid with an icon button.')],
                'label' => [$t('Fabric label'), $t('Name and price on one line, a feature label, no button.')],
            ]],
            'cart_style' => [$t('After "Add to cart"'), '', [
                'modal' => [$t('Pop-up'), $t('A window in the middle of the page.')],
                'drawer' => [$t('Side cart'), $t('The cart slides in from the right. The header cart opens it too.')],
                'toast' => [$t('Small notice'), $t('A short message in the corner. Customers keep browsing.')],
                'page' => [$t('Go to the cart'), $t('Customers are taken straight to the cart page.')],
            ]],
            'free_bar' => [$t('Free delivery bar'), $t('Shows how much is left for free delivery. Set the amount in Shipping > Preferences.'), [
                '1' => [$t('Show'), ''], '0' => [$t('Hide'), ''],
            ]],
            'cart_layout' => [$t('Cart page layout'), '', [
                'classic' => [$t('Classic'), $t('Product list and a summary column.')],
                'cards' => [$t('Cards'), $t('Each product on its own panel, summary on a panel.')],
                'lined' => [$t('Lined'), $t('Ruled list with a framed summary.')],
            ]],
            'listing_layout' => [$t('Filters'), '', [
                'left' => [$t('Left column'), $t('Filters and categories on the left.')],
                'right' => [$t('Right column'), $t('Filters and categories on the right.')],
                'drawer' => [$t('Filter button'), $t('Full-width grid, filters slide in from a button.')],
            ]],
            'category_header' => [$t('Category title area'), '', [
                'standard' => [$t('Standard'), $t('Title, text, then the image.')],
                'banner' => [$t('Banner'), $t('Title written on the category image.')],
                'split' => [$t('Split'), $t('Text on the left, image on the right.')],
                'minimal' => [$t('Minimal'), $t('Centred title, no image.')],
            ]],
            'grid_columns' => [$t('Products per row'), $t('On large screens. Phones always show two.'), [
                '2' => [$t('2 per row'), ''], '3' => [$t('3 per row'), ''], '4' => [$t('4 per row'), ''],
            ]],
            'product_layout' => [$t('Product layout'), '', [
                'classic' => [$t('Classic'), $t('Gallery left, details right.')],
                'wide' => [$t('Large gallery'), $t('Bigger pictures, details stay in view.')],
                'boxed' => [$t('Boxed details'), $t('Details on a panel that stays in view.')],
                'reverse' => [$t('Details first'), $t('Details left, gallery right.')],
            ]],
            'product_thumbs' => [$t('Thumbnails'), $t('Only shown when a product has several pictures.'), [
                'bottom' => [$t('Below'), ''], 'left' => [$t('On the left'), ''],
            ]],
            'product_tabs' => [$t('Description and details'), '', [
                'accordion' => [$t('Accordion'), $t('Sections open one at a time.')],
                'tabs' => [$t('Tabs'), $t('Sections as tabs in a row.')],
                'open' => [$t('All open'), $t('Every section shown one under the other.')],
            ]],
        ];
    }

    private function viewOptions(): string
    {
        $groups = $this->designGroups();
        $key = (string) Tools::getValue('group', 'colors');
        if (!isset($groups[$key])) {
            $key = 'colors';
        }
        [$title, $intro, , $keys] = $groups[$key];
        $tokens = Hbkit::getTokens();
        $meta = $this->tokenMeta();
        $fields = [];
        foreach ($keys as $k) {
            $m = $meta[$k] ?? [$k, ''];
            $f = ['key' => $k, 'label' => $m[0], 'help' => $m[1], 'value' => (string) $tokens[$k]];
            if (isset(Hbkit::TOKEN_CHOICES[$k])) {
                $f['type'] = 'choice';
                $f['choices'] = [];
                foreach (Hbkit::TOKEN_CHOICES[$k] as $opt) {
                    $c = $m[2][$opt] ?? $opt;
                    $f['choices'][] = ['value' => $opt, 'label' => is_array($c) ? $c[0] : $c, 'help' => is_array($c) ? $c[1] : '', 'svg' => HbkitPreviews::svg($k, (string) $opt)];
                }
                $f['visual'] = (bool) $f['choices'][0]['svg'];
            } elseif (strpos((string) Hbkit::DEFAULT_TOKENS[$k], '#') === 0) {
                $f['type'] = 'color';
            } else {
                $f['type'] = 'text';
            }
            $fields[] = $f;
        }
        $colourGroups = [];
        if ($key === 'colors') {
            $byKey = [];
            foreach ($fields as $f) {
                $byKey[$f['key']] = $f;
            }
            foreach ([
                [$this->t('Brand'), ['primary', 'primary_contrast', 'accent', 'sale']],
                [$this->t('Text and lines'), ['text', 'muted', 'border', 'field_border']],
                [$this->t('Backgrounds'), ['bg', 'surface', 'dark', 'dark_text']],
            ] as $g) {
                $colourGroups[] = ['title' => $g[0], 'fields' => array_values(array_filter(array_map(function ($k) use ($byKey) { return $byKey[$k] ?? null; }, $g[1])))];
            }
        }
        $units = ['radius' => 'px', 'button_radius' => 'px', 'field_height' => 'px', 'heading_spacing' => 'em'];
        foreach ($fields as $i => $f) {
            $fields[$i]['unit'] = $units[$f['key']] ?? '';
        }
        $previews = ['cart' => 'product', 'cartpage' => 'cart', 'listing' => 'category', 'product' => 'product', 'cards' => 'category'];

        return $this->fetchTpl('options', [
            'hbk_group' => ['key' => $key, 'title' => $title, 'intro' => $intro, 'fields' => $fields, 'colour_groups' => $colourGroups],
            'hbk_tokens' => $tokens,
            'hbk_preview' => $this->previewUrl($previews[$key] ?? 'index'),
        ]);
    }

    /** A shop page to look at after saving a group. */
    private function previewUrl(string $page): string
    {
        $link = $this->context->link;
        $idLang = (int) $this->context->language->id;
        if ($page === 'category' || $page === 'product') {
            $idCat = (int) Db::getInstance()->getValue('SELECT c.id_category FROM `' . _DB_PREFIX_ . 'category` c
                JOIN `' . _DB_PREFIX_ . 'category_product` cp ON cp.id_category = c.id_category
                WHERE c.active = 1 AND c.level_depth = 2 ORDER BY c.nleft');
            if ($page === 'category' && $idCat) {
                return $link->getCategoryLink($idCat, null, $idLang);
            }
            $idProduct = (int) Db::getInstance()->getValue('SELECT p.id_product FROM `' . _DB_PREFIX_ . 'product` p WHERE p.active = 1' . ($idCat ? ' AND p.id_category_default = ' . $idCat : '') . ' ORDER BY p.id_product');
            if ($page === 'product' && $idProduct) {
                return $link->getProductLink($idProduct, null, null, null, $idLang);
            }
        }
        if ($page === 'cart') {
            return $link->getPageLink('cart', true, $idLang, ['action' => 'show']);
        }

        return $link->getPageLink('index', true, $idLang);
    }

    private function saveOptions(): void
    {
        $tokens = Hbkit::getTokens();
        $posted = (array) Tools::getValue('tokens', []);
        foreach (Hbkit::DEFAULT_TOKENS as $key => $default) {
            if (!isset($posted[$key])) {
                continue;
            }
            $v = trim((string) $posted[$key]);
            if (isset(Hbkit::TOKEN_CHOICES[$key])) {
                $tokens[$key] = in_array($v, Hbkit::TOKEN_CHOICES[$key], true) ? $v : $default;
            } elseif (strpos($default, '#') === 0) {
                $tokens[$key] = Hbkit::isValidColor($v) ? $v : $default;
            } elseif (strpos($key, 'font') === 0) {
                $tokens[$key] = preg_replace('/[^A-Za-z0-9 -]/', '', $v) ?: $default;
            } else {
                $tokens[$key] = (string) (float) $v;
            }
        }
        Configuration::updateValue('HBKIT_TOKENS', json_encode($tokens));
    }

    private function viewTopbar(): string
    {
        $text = $link = [];
        foreach (Language::getLanguages(false) as $l) {
            $text[(int) $l['id_lang']] = (string) Configuration::get('HBKIT_TOPBAR_TEXT', (int) $l['id_lang']);
            $link[(int) $l['id_lang']] = (string) Configuration::get('HBKIT_TOPBAR_LINK', (int) $l['id_lang']);
        }

        $msgs = [];
        foreach ($text as $idLang => $value) {
            $msgs[$idLang] = array_values(array_filter(array_map('trim', explode('|', $value)), 'strlen')) ?: [''];
        }

        return $this->fetchTpl('topbar', ['hbk_topbar' => ['active' => (int) Configuration::get('HBKIT_TOPBAR_ACTIVE'), 'text' => $text, 'link' => $link, 'msgs' => $msgs], 'hbk_tokens' => Hbkit::getTokens()]);
    }

    private function saveTopbar(): void
    {
        $text = $link = [];
        foreach (Language::getLanguages(false) as $l) {
            $text[(int) $l['id_lang']] = strip_tags((string) Tools::getValue('topbar_text_' . $l['id_lang']));
            $url = trim((string) Tools::getValue('topbar_link_' . $l['id_lang']));
            $link[(int) $l['id_lang']] = $url !== '' && Validate::isUrl($url) ? $url : '';
        }
        Configuration::updateValue('HBKIT_TOPBAR_ACTIVE', (int) Tools::getValue('topbar_active'));
        Configuration::updateValue('HBKIT_TOPBAR_TEXT', $text);
        Configuration::updateValue('HBKIT_TOPBAR_LINK', $link);
    }

    /* ------------------------------------------------------------------ */
    /* Demo import                                                         */
    /* ------------------------------------------------------------------ */

    private function viewImport(): string
    {
        $iso = (string) $this->context->language->iso_code;
        $packs = HbkitImporter::findPacks();
        foreach ($packs as $k => $pack) {
            $packs[$k]['description'] = $pack['descriptions'][$iso] ?? $pack['description'];
            $packs[$k]['summary'] = $this->t('This loads %products% products, %categories% categories and %pages% pages, with the menu, footer links, homepage sections and the %name% design.', [
                '%products%' => $pack['counts']['products'], '%categories%' => $pack['counts']['categories'], '%pages%' => $pack['counts']['pages'], '%name%' => $pack['name'],
            ]);
        }

        return $this->fetchTpl('import', ['hbk_packs' => $packs, 'hbk_import_log' => $this->importLog ?? []]);
    }

    private $importLog;

    private function runImport(): void
    {
        $theme = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) Tools::getValue('pack'));
        $packs = HbkitImporter::findPacks();
        if (!isset($packs[$theme])) {
            throw new PrestaShopException($this->t('Demo pack not found.'));
        }
        if (Tools::getValue('confirm') !== 'IMPORT') {
            throw new PrestaShopException($this->t('Type IMPORT to confirm.'));
        }
        $importer = new HbkitImporter($packs[$theme]['dir']);
        $importer->run(['wipe' => (bool) Tools::getValue('wipe'), 'theme' => true]);
        $this->importLog = $importer->getLog();
        $this->confirmations[] = $this->t('Demo imported.');
    }
}
