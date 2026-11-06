<?php
/**
 * Back office form schema: what each homepage section shows, in the shop owner's words.
 * Storage keys and values stay the same as HbkitTypes; this only describes how to edit them.
 *
 * @author    iamshoaibktk@gmail.com (WhatsApp: +923459016286)
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HbkitForm
{
    /** @var callable */
    private $t;

    public function __construct(callable $translate)
    {
        $this->t = $translate;
    }

    private function t(string $s): string
    {
        return ($this->t)($s);
    }

    /** Types whose heading is drawn by the shared section header (and can sit left with a link). */
    private const SHARED_HEADING = ['usp', 'categories', 'products', 'banners', 'image_text', 'testimonials', 'brands', 'faq', 'gallery'];

    /** Name, one-line description and item wording of each section type. */
    public function info(string $type): array
    {
        $t = function ($s) { return $this->t($s); };
        $all = [
            'hero' => [$t('Hero'), $t('The large photo at the top of the homepage. Its title, text and button are set on each slide.'), [$t('Slide'), $t('Slides'), $t('One slide shows as a still photo. Two or more turn into a slider.'), $t('Add slide')]],
            'usp' => [$t('Benefits'), $t('Short reasons to buy, like free delivery or easy returns.'), [$t('Benefit'), $t('Benefits'), $t('Three or four work best.'), $t('Add benefit')]],
            'categories' => [$t('Categories'), $t('Links to your main categories, as tiles, circles or a list.'), [$t('Category'), $t('Categories'), $t('Pick a category and, if you like, your own photo and name.'), $t('Add category')]],
            'products' => [$t('Products'), $t('A row of products. Add a second list to show tabs, for example New in and Best sellers.'), [$t('List'), $t('Lists'), $t('Each list becomes a tab when there is more than one.'), $t('Add a list')]],
            'banners' => [$t('Banners'), $t('Two to four promotions with a photo, a title and a button.'), [$t('Banner'), $t('Banners'), $t('Use photos of the same size so the row lines up.'), $t('Add banner')]],
            'image_text' => [$t('Image and text'), $t('A story next to a photo. Add more blocks and they alternate sides.'), [$t('Block'), $t('Blocks'), '', $t('Add block')]],
            'testimonials' => [$t('Testimonials'), $t('Quotes from your customers.'), [$t('Quote'), $t('Quotes'), '', $t('Add quote')]],
            'brands' => [$t('Brand logos'), $t('A row of brand logos from Catalog > Brands.'), [$t('Brand'), $t('Brands'), $t('Only used when "Chosen brands" is selected.'), $t('Add brand')]],
            'countdown' => [$t('Deal with countdown'), $t('One product on offer, with a timer until the offer ends.'), null],
            'faq' => [$t('Questions'), $t('Frequently asked questions that open one at a time.'), [$t('Question'), $t('Questions'), '', $t('Add question')]],
            'gallery' => [$t('Gallery'), $t('A row of photos, for example from your customers or Instagram.'), [$t('Photo'), $t('Photos'), $t('Square or portrait photos look best.'), $t('Add photo')]],
            'text' => [$t('Text block'), $t('A title, a few lines of text and an optional button.'), null],
            'newsletter' => [$t('Newsletter'), $t('Email sign-up with a photo.'), null],
        ];
        $i = $all[$type] ?? [$type, '', null];

        return [
            'name' => $i[0],
            'intro' => $i[1],
            'items' => $i[2] ? ['one' => $i[2][0], 'many' => $i[2][1], 'help' => $i[2][2], 'add' => $i[2][3]] : null,
            'heading' => true,
            'shared_heading' => in_array($type, self::SHARED_HEADING, true),
        ];
    }

    /** Short description for the "Add a section" dialog. */
    public function blurb(string $type): string
    {
        $t = function ($s) { return $this->t($s); };
        $b = [
            'hero' => $t('Large photo with a title'), 'usp' => $t('Icons with short reasons to buy'), 'categories' => $t('Tiles, circles or a list'),
            'products' => $t('New, best sellers or a category'), 'banners' => $t('Two to four promotions'), 'image_text' => $t('A story next to a photo'),
            'testimonials' => $t('Customer quotes'), 'brands' => $t('A row of logos'), 'countdown' => $t('One offer with a timer'),
            'faq' => $t('Questions and answers'), 'gallery' => $t('A row of photos'), 'text' => $t('Title, text and a button'), 'newsletter' => $t('Email sign-up'),
        ];

        return $b[$type] ?? '';
    }

    private function seg(string $key, string $label, array $options, string $help = ''): array
    {
        return ['key' => $key, 'label' => $label, 'help' => $help, 'ctl' => 'seg', 'options' => $options];
    }

    private function sizes(): array
    {
        return ['s' => $this->t('Small'), 'm' => $this->t('Medium'), 'l' => $this->t('Large')];
    }

    /** Section settings, grouped: ['layout' => [...], 'look' => [...]]. */
    public function settings(string $type): array
    {
        $t = function ($s) { return $this->t($s); };
        $layout = [];
        switch ($type) {
            case 'hero':
                $layout[] = ['key' => 'variant', 'label' => $t('Style'), 'ctl' => 'pics', 'help' => '', 'options' => [
                    'slider' => [$t('Slider'), $t('Text centred on the photo')], 'split' => [$t('Split'), $t('Text beside the photo')],
                    'center' => [$t('Centred'), $t('Text in the middle')], 'fullscreen' => [$t('Full screen'), $t('Text bottom left')],
                ]];
                $layout[] = $this->seg('height', $t('Height'), ['s' => $t('Small'), 'm' => $t('Medium'), 'l' => $t('Large')]);
                $layout[] = ['key' => 'autoplay', 'label' => $t('Change slides'), 'ctl' => 'select', 'help' => $t('Only used when there are two or more slides.'), 'options' => [
                    '0' => $t('Never'), '4' => $t('Every 4 seconds'), '6' => $t('Every 6 seconds'), '8' => $t('Every 8 seconds'), '10' => $t('Every 10 seconds'),
                ]];
                $layout[] = ['key' => 'id_product', 'label' => $t('Product on the card'), 'ctl' => 'product', 'help' => $t('Shown in the card on the photo, with its photo and name.'), 'optional' => true];
                $layout[] = ['key' => 'overlay', 'label' => $t('Dim the photo'), 'ctl' => 'range', 'min' => 0, 'max' => 60, 'step' => 5, 'unit' => '%', 'help' => $t('Darkens the photo so white text stays readable.')];
                break;
            case 'usp':
                $layout[] = $this->seg('style', $t('Style'), ['bar' => $t('Row'), 'cards' => $t('Cards'), 'inline' => $t('Compact')]);
                break;
            case 'categories':
                $layout[] = ['key' => 'style', 'label' => $t('Style'), 'ctl' => 'pics', 'help' => '', 'options' => [
                    'tiles' => [$t('Tiles'), $t('Photo with the name below')], 'overlay' => [$t('On the photo'), $t('Name written on the photo')],
                    'circles' => [$t('Circles'), $t('Round photos')], 'cards' => [$t('Cards'), $t('Photo and name in a box')],
                    'index' => [$t('List'), $t('Large names with a photo')],
                ]];
                $layout[] = $this->seg('columns', $t('Per row'), ['2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'], $t('Not used by the List style.'));
                break;
            case 'products':
                $layout[] = $this->seg('layout', $t('Show as'), ['grid' => $t('Grid'), 'carousel' => $t('Carousel')]);
                $layout[] = $this->seg('columns', $t('Products per row'), ['3' => '3', '4' => '4', '5' => '5']);
                $layout[] = ['key' => 'limit', 'label' => $t('How many products'), 'ctl' => 'select', 'help' => '', 'options' => ['4' => '4', '8' => '8', '12' => '12', '16' => '16']];
                break;
            case 'banners':
                $layout[] = ['key' => 'layout', 'label' => $t('Arrangement'), 'ctl' => 'select', 'help' => '', 'options' => [
                    '1' => $t('One wide banner'), '2' => $t('Two side by side'), '3' => $t('Three side by side'), '4' => $t('Four side by side'),
                    '2-1' => $t('Large and small'), '1-2' => $t('Small and large'),
                ]];
                $layout[] = $this->seg('style', $t('Text'), ['overlay' => $t('On the photo'), 'below' => $t('Below the photo'), 'ruler' => $t('Size ruler, no photos')]);
                $layout[] = $this->seg('height', $t('Height'), $this->sizes());
                break;
            case 'image_text':
                $layout[] = $this->seg('style', $t('Photo side'), ['alternate' => $t('Alternate'), 'image-left' => $t('Left'), 'image-right' => $t('Right')]);
                $layout[] = $this->seg('ratio', $t('Photo shape'), ['portrait' => $t('Portrait'), 'square' => $t('Square'), 'landscape' => $t('Landscape')]);
                break;
            case 'testimonials':
                $layout[] = $this->seg('style', $t('Style'), ['cards' => $t('Cards'), 'quote' => $t('One large quote')]);
                break;
            case 'brands':
                $layout[] = $this->seg('source', $t('Which brands'), ['all' => $t('All brands with a logo'), 'items' => $t('Chosen brands')]);
                $layout[] = ['key' => 'grayscale', 'label' => $t('Show logos in grey'), 'ctl' => 'switch', 'help' => $t('They turn to colour when pointed at.')];
                break;
            case 'countdown':
                $layout[] = ['key' => 'id_product', 'label' => $t('Product on offer'), 'ctl' => 'product', 'help' => $t('Its price and discount are shown next to the timer.')];
                $layout[] = ['key' => 'date_to', 'label' => $t('Offer ends'), 'ctl' => 'date', 'help' => $t('The section hides itself when the time is up.')];
                $layout[] = ['key' => 'image', 'label' => $t('Photo'), 'ctl' => 'image', 'help' => $t('Without a photo, the product picture is used.')];
                $layout[] = ['key' => 'link', 'label' => $t('Button goes to'), 'ctl' => 'link', 'help' => $t('Without a link, the button opens the product.')];
                break;
            case 'gallery':
                $layout[] = $this->seg('columns', $t('Photos per row'), ['3' => '3', '4' => '4', '5' => '5', '6' => '6']);
                break;
            case 'text':
                $layout[] = ['key' => 'link', 'label' => $t('Button goes to'), 'ctl' => 'link', 'help' => $t('The button text is the small text above the title.')];
                break;
            case 'newsletter':
                $layout[] = ['key' => 'image', 'label' => $t('Photo'), 'ctl' => 'image', 'help' => $t('Shown beside the sign-up form.')];
                break;
        }
        $look = [
            ['key' => 'background', 'label' => $t('Background'), 'ctl' => 'swatches', 'help' => $t('Colours come from Design > Colours.'), 'options' => [
                'none' => [$t('None'), 'var(--hb-bg, #fff)'], 'surface' => [$t('Panel'), 'surface'], 'dark' => [$t('Dark'), 'dark'],
                'primary' => [$t('Main'), 'primary'], 'accent' => [$t('Accent'), 'accent'],
            ]],
            $this->seg('spacing', $t('Space above and below'), ['none' => $t('None'), 's' => $t('Small'), 'm' => $t('Medium'), 'l' => $t('Large')]),
            $this->seg('width', $t('Width'), ['container' => $t('Page width'), 'narrow' => $t('Narrow'), 'full' => $t('Edge to edge')]),
        ];
        if ($type === 'hero') {
            $look = array_values(array_filter($look, function ($f) { return $f['key'] !== 'background'; }));
        }

        return ['layout' => $layout, 'look' => $look];
    }

    /** Heading fields for types that have a heading. */
    public function heading(string $type): array
    {
        $t = function ($s) { return $this->t($s); };
        $info = $this->info($type);
        $out = ['fields' => [], 'align' => null, 'link' => null, 'card' => $t('Heading'), 'help' => ''];
        if (!$info['heading']) {
            return $out;
        }
        if ($type === 'hero') {
            // The hero's own texts live on each slide; the section texts fill the optional card on the photo.
            $out['card'] = $t('Card on the photo');
            $out['help'] = $t('An optional small card in the corner of the first slide, for example this week\'s weather and the product that suits it. Leave it empty to hide the card.');
            $out['fields'] = [
                ['key' => 'subtitle', 'label' => $t('Small text at the top'), 'kind' => 'text', 'optional' => true],
                ['key' => 'title', 'label' => $t('Large text'), 'kind' => 'text', 'optional' => true],
                ['key' => 'content', 'label' => $t('Text next to the large text'), 'kind' => 'text', 'optional' => true],
            ];

            return $out;
        }
        $sub = in_array($type, ['text'], true) ? $t('Button text') : $t('Small text above the title');
        $out['fields'] = [
            ['key' => 'title', 'label' => $t('Title'), 'kind' => 'text', 'optional' => false],
            ['key' => 'subtitle', 'label' => $sub, 'label_split' => $t('Link text on the right'), 'kind' => 'text', 'optional' => true],
            ['key' => 'content', 'label' => $type === 'newsletter' || $type === 'countdown' || $type === 'text' ? $t('Text') : $t('Intro under the title'), 'kind' => 'textarea', 'optional' => true],
        ];
        if ($info['shared_heading']) {
            $out['align'] = $this->seg('heading_align', $t('Title position'), ['center' => $t('Centred'), 'left' => $t('Left'), 'split' => $t('Left, with a link')]);
            $out['link'] = ['key' => 'head_link', 'label' => $t('Link goes to'), 'ctl' => 'link', 'help' => ''];
        }

        return $out;
    }

    /** Item fields: ['lang' => [...], 'data' => [...]]. */
    public function itemFields(string $type): array
    {
        $t = function ($s) { return $this->t($s); };
        $L = function ($key, $label, $kind = 'text', $optional = true, $help = '') {
            return ['key' => $key, 'label' => $label, 'kind' => $kind, 'optional' => $optional, 'help' => $help];
        };
        $img = function ($help = '') use ($t) { return ['key' => 'image', 'label' => $t('Photo'), 'ctl' => 'image', 'help' => $help]; };
        $link = ['key' => 'link', 'label' => $t('Goes to'), 'ctl' => 'link', 'help' => ''];
        $align = $this->seg('align', $t('Text position'), ['left' => $t('Left'), 'center' => $t('Centre'), 'right' => $t('Right')]);
        $tone = $this->seg('tone', $t('Text colour'), ['light' => $t('Light'), 'dark' => $t('Dark')]);
        switch ($type) {
            case 'hero':
                return ['lang' => [$L('title', $t('Title'), 'text', false), $L('subtitle', $t('Small text above the title')), $L('text', $t('Text'), 'textarea'), $L('button', $t('Button text')), $L('url', $t('Web address'), 'text', true, $t('Only when the button goes to another website.'))],
                    'data' => [$img($t('Best size: 2400 × 1200 px, JPG or WebP.')), ['key' => 'image_mobile', 'label' => $t('Photo for phones'), 'ctl' => 'image', 'help' => $t('Portrait, 1080 × 1350 px. Without it, the main photo is cropped.'), 'optional' => true], $link, $align, $tone]];
            case 'usp':
                return ['lang' => [$L('title', $t('Title'), 'text', false), $L('text', $t('Text'))], 'data' => [['key' => 'icon', 'label' => $t('Icon'), 'ctl' => 'icon', 'help' => '']]];
            case 'categories':
                return ['lang' => [$L('title', $t('Name shown'), 'text', true, $t('Leave empty to use the category name.')), $L('subtitle', $t('Short text')), $L('button', $t('Button text'))],
                    'data' => [['key' => 'id_category', 'label' => $t('Category'), 'ctl' => 'category', 'help' => ''], $img($t('Leave empty to use the category picture.'))]];
            case 'products':
                return ['lang' => [$L('title', $t('Tab name'), 'text', false)],
                    'data' => [['key' => 'source', 'label' => $t('Which products'), 'ctl' => 'select', 'help' => '', 'options' => ['new' => $t('Newest products'), 'bestsellers' => $t('Best sellers'), 'specials' => $t('On sale'), 'featured' => $t('Featured (home category)'), 'category' => $t('From a category')]],
                        ['key' => 'id_category', 'label' => $t('Category'), 'ctl' => 'category', 'help' => '', 'show_if' => ['source', 'category']]]];
            case 'banners':
                return ['lang' => [$L('title', $t('Title'), 'text', false), $L('subtitle', $t('Small text above the title')), $L('text', $t('Text'), 'textarea'), $L('button', $t('Button text')), $L('url', $t('Web address'), 'text', true, $t('Only when the banner goes to another website.'))],
                    'data' => [$img(), $link, $align, $tone]];
            case 'image_text':
                return ['lang' => [$L('title', $t('Title'), 'text', false), $L('subtitle', $t('Small text above the title')), $L('text', $t('Text'), 'textarea'), $L('button', $t('Button text')), $L('url', $t('Web address'), 'text', true, $t('Only when the button goes to another website.'))],
                    'data' => [$img(), $link]];
            case 'testimonials':
                return ['lang' => [$L('text', $t('Quote'), 'textarea', false), $L('title', $t('Name'), 'text', false), $L('subtitle', $t('Place or role'))],
                    'data' => [$this->seg('rating', $t('Stars'), ['5' => '5', '4' => '4', '3' => '3'])]];
            case 'brands':
                return ['lang' => [], 'data' => [['key' => 'id_manufacturer', 'label' => $t('Brand'), 'ctl' => 'manufacturer', 'help' => '']]];
            case 'faq':
                return ['lang' => [$L('title', $t('Question'), 'text', false), $L('text', $t('Answer'), 'textarea', false)], 'data' => []];
            case 'gallery':
                return ['lang' => [$L('title', $t('Caption')), $L('url', $t('Web address'), 'text', true, $t('Only when the photo links to another website.'))], 'data' => [$img(), $link]];
        }

        return ['lang' => [], 'data' => []];
    }
}
