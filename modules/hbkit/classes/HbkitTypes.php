<?php
/**
 * Section types: what each one stores, used by the editor and the renderer.
 *
 * Field kinds: text, textarea (HTML), select, int, bool, color, image, link, category, product, manufacturer, date, icon.
 * "settings" are per section (not translated); "item" lists non-translated item data fields;
 * "item_lang" lists which translated item fields the type uses (title, subtitle, text, button, url).
 *
 * @author    iamshoaibktk@gmail.com (WhatsApp: +923459016286)
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HbkitTypes
{
    /** Settings every section has. */
    public const COMMON = [
        'background' => ['select', ['none', 'surface', 'dark', 'primary', 'accent']],
        'spacing' => ['select', ['m', 's', 'l', 'none']],
        'width' => ['select', ['container', 'full', 'narrow']],
        'heading_align' => ['select', ['center', 'left', 'split']],
        'head_link' => ['link'],
    ];

    public const ICONS = [
        'local_shipping', 'autorenew', 'verified_user', 'lock', 'support_agent', 'card_giftcard', 'eco', 'favorite',
        'star', 'bolt', 'workspace_premium', 'inventory_2', 'payments', 'schedule', 'storefront', 'spa',
        'recycling', 'straighten', 'handshake', 'local_offer', 'diamond', 'pets', 'restaurant', 'build',
    ];

    public static function all(): array
    {
        return [
            'hero' => [
                'label' => 'Hero / slider',
                'settings' => [
                    'variant' => ['select', ['slider', 'split', 'center', 'fullscreen']],
                    'height' => ['select', ['l', 'm', 's']],
                    'autoplay' => ['int', 6],
                    'overlay' => ['int', 25],
                    'id_product' => ['product'],
                ],
                'item' => ['image' => ['image'], 'image_mobile' => ['image'], 'link' => ['link'], 'align' => ['select', ['left', 'center', 'right']], 'tone' => ['select', ['light', 'dark']]],
                'item_lang' => ['subtitle', 'title', 'text', 'button', 'url'],
            ],
            'usp' => [
                'label' => 'Benefits bar',
                'settings' => ['style' => ['select', ['bar', 'cards', 'inline']]],
                'item' => ['icon' => ['icon']],
                'item_lang' => ['title', 'text'],
            ],
            'categories' => [
                'label' => 'Category tiles',
                'settings' => ['style' => ['select', ['tiles', 'overlay', 'circles', 'cards', 'index']], 'columns' => ['select', ['4', '3', '5', '6', '2']]],
                'item' => ['id_category' => ['category'], 'image' => ['image']],
                'item_lang' => ['title', 'subtitle', 'button'],
            ],
            'products' => [
                'label' => 'Product tabs',
                'settings' => ['limit' => ['int', 8], 'layout' => ['select', ['grid', 'carousel']], 'columns' => ['select', ['4', '3', '5']]],
                'item' => ['source' => ['select', ['featured', 'new', 'specials', 'bestsellers', 'category']], 'id_category' => ['category']],
                'item_lang' => ['title'],
            ],
            'banners' => [
                'label' => 'Banners',
                'settings' => ['layout' => ['select', ['2', '3', '1', '2-1', '1-2', '4']], 'style' => ['select', ['overlay', 'below', 'ruler']], 'height' => ['select', ['m', 's', 'l']]],
                'item' => ['image' => ['image'], 'link' => ['link'], 'align' => ['select', ['left', 'center', 'right']], 'tone' => ['select', ['light', 'dark']]],
                'item_lang' => ['subtitle', 'title', 'text', 'button', 'url'],
            ],
            'image_text' => [
                'label' => 'Image and text',
                'settings' => ['style' => ['select', ['alternate', 'image-left', 'image-right']], 'ratio' => ['select', ['portrait', 'square', 'landscape']]],
                'item' => ['image' => ['image'], 'link' => ['link']],
                'item_lang' => ['subtitle', 'title', 'text', 'button', 'url'],
            ],
            'testimonials' => [
                'label' => 'Testimonials',
                'settings' => ['style' => ['select', ['cards', 'quote']]],
                'item' => ['rating' => ['int', 5]],
                'item_lang' => ['title', 'subtitle', 'text'],
            ],
            'brands' => [
                'label' => 'Brand logos',
                'settings' => ['source' => ['select', ['all', 'items']], 'grayscale' => ['bool', 1]],
                'item' => ['id_manufacturer' => ['manufacturer']],
                'item_lang' => [],
            ],
            'countdown' => [
                'label' => 'Deal with countdown',
                'settings' => ['id_product' => ['product'], 'date_to' => ['date'], 'image' => ['image'], 'link' => ['link']],
                'item' => [],
                'item_lang' => [],
            ],
            'faq' => [
                'label' => 'FAQ',
                'settings' => [],
                'item' => [],
                'item_lang' => ['title', 'text'],
            ],
            'gallery' => [
                'label' => 'Image gallery',
                'settings' => ['columns' => ['select', ['4', '3', '5', '6']]],
                'item' => ['image' => ['image'], 'link' => ['link']],
                'item_lang' => ['title', 'url'],
            ],
            'text' => [
                'label' => 'Text block',
                'settings' => ['link' => ['link']],
                'item' => [],
                'item_lang' => [],
            ],
            'newsletter' => [
                'label' => 'Newsletter',
                'settings' => ['image' => ['image']],
                'item' => [],
                'item_lang' => [],
            ],
        ];
    }

    public static function get(string $type): ?array
    {
        $all = self::all();

        return $all[$type] ?? null;
    }

    /** Settings with defaults applied (type defaults first, then common ones). */
    public static function settingsWithDefaults(string $type, array $saved): array
    {
        $def = self::get($type);
        $out = [];
        foreach (array_merge($def ? $def['settings'] : [], self::COMMON) as $key => $field) {
            if (array_key_exists($key, $saved)) {
                $out[$key] = $saved[$key];
            } elseif ($field[0] === 'select') {
                $out[$key] = $field[1][0];
            } else {
                $out[$key] = $field[1] ?? '';
            }
        }

        return $out + $saved;
    }
}
