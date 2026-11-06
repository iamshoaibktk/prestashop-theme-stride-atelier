<?php
/**
 * Small wireframe pictures for the layout choices in Theme Kit > Design.
 *
 * @author    iamshoaibktk@gmail.com (WhatsApp: +923459016286)
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HbkitPreviews
{
    private const LINE = '#cbd5e1';
    private const SOFT = '#e8edf3';
    private const INK = '#64748b';
    private const HI = '#2563eb';
    private const DIM = 'rgba(15,23,42,.35)';

    private static function r($x, $y, $w, $h, $fill = self::LINE, $rx = 2, $extra = ''): string
    {
        return sprintf('<rect x="%s" y="%s" width="%s" height="%s" rx="%s" fill="%s"%s/>', $x, $y, $w, $h, $rx, $fill, $extra);
    }

    private static function box($x, $y, $w, $h, $stroke = self::LINE, $rx = 2, $fill = 'none'): string
    {
        return sprintf('<rect x="%s" y="%s" width="%s" height="%s" rx="%s" fill="%s" stroke="%s" stroke-width="1.2"/>', $x + .6, $y + .6, $w - 1.2, $h - 1.2, $rx, $fill, $stroke);
    }

    /** A product tile: picture then two text lines. */
    private static function tile($x, $y, $w, $h, $img = self::SOFT): string
    {
        $ih = $h * .62;

        return self::r($x, $y, $w, $ih, $img, 2) . self::r($x, $y + $ih + 4, $w * .8, 3, self::INK, 1) . self::r($x, $y + $ih + 10, $w * .45, 3, self::LINE, 1);
    }

    /** Header strip used by page pictures. */
    private static function head(): string
    {
        return self::r(0, 0, 160, 12, self::SOFT, 0) . self::r(8, 4, 22, 4, self::INK, 1) . self::r(118, 4, 34, 4, self::LINE, 1);
    }

    /** Product page body: gallery and buy box. */
    private static function productBody($galleryX, $galleryW, $infoX, $infoW, $info = ''): string
    {
        $out = self::r($galleryX, 20, $galleryW, 58, self::SOFT, 2);
        $out .= $info . self::r($infoX, 22, $infoW * .85, 5, self::INK, 1) . self::r($infoX, 32, $infoW * .4, 4, self::LINE, 1);
        $out .= self::r($infoX, 42, $infoW, 3, self::LINE, 1) . self::r($infoX, 48, $infoW * .7, 3, self::LINE, 1);
        $out .= self::r($infoX, 58, $infoW, 8, self::HI, 3);

        return $out;
    }

    public static function svg(string $key, string $value): string
    {
        $b = self::draw($key, $value);
        if ($b === '') {
            return '';
        }

        return '<svg viewBox="0 0 160 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><rect width="160" height="100" fill="#fff"/>' . $b . '</svg>';
    }

    private static function draw(string $key, string $v): string
    {
        switch ($key) {
            case 'hero.variant':
                $g = '#94a3b8';
                switch ($v) {
                    case 'split': return self::r(80, 0, 80, 100, $g, 0) . self::r(10, 38, 58, 8, self::INK, 1) . self::r(10, 54, 30, 9, self::HI, 2);
                    case 'center': return self::r(0, 0, 160, 100, $g, 0) . self::r(45, 40, 70, 8, '#fff', 1) . self::r(62, 54, 36, 9, '#fff', 2);
                    case 'fullscreen': return self::r(0, 0, 160, 100, $g, 0) . self::r(10, 58, 80, 10, '#fff', 1) . self::r(10, 74, 36, 10, '#fff', 2);
                    default: return self::r(0, 0, 160, 100, $g, 0) . self::r(40, 42, 80, 8, '#fff', 1) . '<circle cx="74" cy="88" r="2" fill="#fff"/><circle cx="80" cy="88" r="2" fill="#fff" opacity=".5"/><circle cx="86" cy="88" r="2" fill="#fff" opacity=".5"/>';
                }
            case 'categories.style':
                $g = '#94a3b8';
                $out = '';
                switch ($v) {
                    case 'overlay': foreach ([8, 47, 86, 125] as $x) { $out .= self::r($x, 18, 30, 60, $g, 2) . self::r($x + 4, 64, 20, 4, '#fff', 1); } return $out;
                    case 'circles': foreach ([10, 48, 86, 124] as $x) { $out .= '<circle cx="' . ($x + 13) . '" cy="44" r="14" fill="' . $g . '"/>' . self::r($x + 3, 66, 20, 4, self::INK, 1); } return $out;
                    case 'cards': foreach ([8, 47, 86, 125] as $x) { $out .= self::box($x, 16, 30, 66, self::LINE, 3) . self::r($x + 3, 19, 24, 40, $g, 2) . self::r($x + 4, 66, 18, 4, self::INK, 1); } return $out;
                    case 'index': return self::r(10, 18, 70, 8, self::INK, 1) . self::r(10, 38, 56, 8, self::INK, 1) . self::r(10, 58, 64, 8, self::INK, 1) . self::r(100, 12, 50, 76, $g, 2);
                    default: foreach ([8, 47, 86, 125] as $x) { $out .= self::r($x, 16, 30, 50, $g, 2) . self::r($x, 72, 22, 4, self::INK, 1); } return $out;
                }
            case 'header_style':
                $bar = ['dark' => '#1e293b', 'transparent' => 'none'][$v] ?? self::SOFT;
                $ink = in_array($v, ['dark', 'transparent'], true) ? '#fff' : self::INK;
                $out = $v === 'transparent' ? self::r(0, 0, 160, 70, '#94a3b8', 0) : self::r(0, 0, 160, 22, $bar, 0);
                if ($v === 'centered') {
                    $out .= self::r(64, 4, 32, 6, $ink, 1) . self::r(36, 14, 88, 3, self::LINE, 1);
                } else {
                    $out .= self::r(8, 8, 26, 6, $ink, 1) . self::r(46, 9, 60, 3, $v === 'classic' ? self::LINE : $ink, 1) . self::r(130, 8, 22, 5, $ink, 1);
                }
                if ($v === 'transparent') {
                    $out .= self::r(12, 40, 60, 7, '#fff', 1) . self::r(12, 52, 36, 7, self::HI, 3);
                } else {
                    $out .= self::r(8, 32, 144, 40, self::SOFT, 2);
                }

                return $out . self::r(8, 80, 44, 14, self::SOFT, 2) . self::r(58, 80, 44, 14, self::SOFT, 2) . self::r(108, 80, 44, 14, self::SOFT, 2);

            case 'header_sticky':
                // A page scrolled half way: where the header is.
                $out = self::r(0, 0, 160, 100, self::SOFT, 0) . self::r(8, 30, 144, 6, self::LINE, 1) . self::r(8, 44, 120, 6, self::LINE, 1) . self::r(8, 58, 136, 6, self::LINE, 1);
                if ($v === 'always') {
                    $out .= self::r(0, 0, 160, 20, self::INK, 0) . self::r(8, 7, 26, 6, '#fff', 1);
                } elseif ($v === 'up') {
                    $out .= self::r(0, 0, 160, 20, self::INK, 0) . self::r(8, 7, 26, 6, '#fff', 1) . '<path d="M140 92V72m-6 6l6-6 6 6" fill="none" stroke="' . self::HI . '" stroke-width="2.5"/>';
                } else {
                    $out .= '<path d="M140 72v20m-6-6l6 6 6-6" fill="none" stroke="' . self::INK . '" stroke-width="2.5"/>';
                }

                return $out;

            case 'search_style':
                $out = self::head();
                if ($v === 'panel') {
                    $out .= self::r(0, 0, 160, 46, '#fff', 0) . self::r(8, 10, 90, 7, self::INK, 1) . self::r(8, 21, 144, 1, self::INK, 0)
                        . self::tile(8, 27, 33, 38) . self::tile(45, 27, 33, 38) . self::tile(82, 27, 33, 38) . self::r(0, 66, 160, 34, self::DIM, 0);
                } elseif ($v === 'field') {
                    $out .= self::box(96, 2, 50, 8, self::LINE, 4) . self::r(80, 13, 72, 52, '#fff', 2) . self::box(80, 13, 72, 52, self::LINE, 2)
                        . self::tile(85, 18, 12, 14) . self::r(101, 21, 30, 4, self::INK, 1) . self::tile(85, 34, 12, 14) . self::r(101, 37, 26, 4, self::INK, 1)
                        . self::r(8, 20, 66, 40, self::SOFT, 2);
                } else {
                    $out .= self::box(96, 2, 50, 8, self::LINE, 4) . self::r(8, 20, 144, 40, self::SOFT, 2);
                }

                return $out;

            case 'footer_style':
                $fill = ['dark' => '#1e293b', 'light' => '#eef2f6', 'primary' => self::HI][$v] ?? self::SOFT;
                $ink = $v === 'light' ? self::LINE : 'rgba(255,255,255,.55)';
                $out = self::head() . self::r(8, 18, 144, 30, self::SOFT, 2) . self::r(0, 58, 160, 42, $fill, 0);
                foreach ([12, 50, 88, 126] as $x) {
                    $out .= self::r($x, 66, 22, 3, $ink, 1) . self::r($x, 74, 18, 2, $ink, 1) . self::r($x, 80, 20, 2, $ink, 1);
                }

                return $out;

            case 'card_style':
                return self::card($v);

            case 'cart_style':
                $page = self::head() . self::r(8, 20, 70, 72, self::SOFT, 2) . self::r(86, 22, 60, 5, self::INK, 1) . self::r(86, 34, 66, 8, self::LINE, 3);
                switch ($v) {
                    case 'drawer':
                        return $page . self::r(0, 0, 160, 100, self::DIM, 0) . self::r(100, 0, 60, 100, '#fff', 0)
                            . self::r(106, 6, 30, 4, self::INK, 1) . self::r(106, 16, 48, 4, '#fde68a', 2)
                            . self::r(106, 26, 12, 12, self::SOFT, 1) . self::r(122, 28, 28, 3, self::INK, 1) . self::r(122, 34, 16, 3, self::LINE, 1)
                            . self::r(106, 44, 12, 12, self::SOFT, 1) . self::r(122, 46, 28, 3, self::INK, 1) . self::r(122, 52, 16, 3, self::LINE, 1)
                            . self::r(106, 78, 48, 7, self::HI, 3) . self::box(106, 88, 48, 7, self::HI, 3);
                    case 'toast':
                        return $page . '<rect x="92" y="14" width="62" height="20" rx="3" fill="#fff" stroke="' . self::LINE . '" style="filter:drop-shadow(0 2px 3px rgba(0,0,0,.18))"/>'
                            . '<circle cx="100" cy="24" r="3" fill="' . self::HI . '"/>' . self::r(106, 18, 12, 12, self::SOFT, 1) . self::r(121, 20, 26, 3, self::INK, 1) . self::r(121, 26, 18, 3, self::LINE, 1);
                    case 'page':
                        return self::head() . self::r(8, 20, 92, 14, self::SOFT, 2) . self::r(8, 38, 92, 14, self::SOFT, 2) . self::r(108, 20, 44, 52, '#f1f5f9', 2)
                            . self::r(114, 28, 30, 3, self::INK, 1) . self::r(114, 36, 32, 3, self::LINE, 1) . self::r(114, 60, 32, 7, self::HI, 3);
                    default:
                        return $page . self::r(0, 0, 160, 100, self::DIM, 0) . self::r(30, 26, 100, 48, '#fff', 3)
                            . self::r(38, 34, 18, 18, self::SOFT, 1) . self::r(62, 36, 30, 3, self::INK, 1) . self::r(62, 42, 20, 3, self::LINE, 1)
                            . self::box(38, 60, 38, 8, self::INK, 3) . self::r(86, 60, 38, 8, self::HI, 3);
                }

            case 'free_bar':
                $out = self::r(20, 8, 120, 84, '#fff', 3) . self::box(20, 8, 120, 84, self::LINE, 3);
                if ($v === '1') {
                    $out .= self::r(28, 16, 104, 18, '#fef3c7', 3) . self::r(34, 20, 60, 3, self::INK, 1) . self::r(34, 27, 92, 3, '#fde68a', 1.5) . self::r(34, 27, 60, 3, '#d97706', 1.5);
                }
                $y = $v === '1' ? 40 : 18;

                return $out . self::r(28, $y, 14, 14, self::SOFT, 1) . self::r(46, $y + 2, 50, 3, self::INK, 1) . self::r(46, $y + 8, 30, 3, self::LINE, 1)
                    . self::r(28, $y + 20, 14, 14, self::SOFT, 1) . self::r(46, $y + 22, 50, 3, self::INK, 1) . self::r(46, $y + 28, 30, 3, self::LINE, 1);

            case 'cart_layout':
                $out = self::head();
                for ($i = 0; $i < 3; ++$i) {
                    $y = 20 + $i * 20;
                    if ($v === 'cards') {
                        $out .= self::r(8, $y, 92, 17, '#f1f5f9', 3);
                    }
                    $out .= self::r(12, $y + 3, 11, 11, $v === 'cards' ? '#fff' : self::SOFT, 1) . self::r(28, $y + 5, 40, 3, self::INK, 1) . self::r(28, $y + 10, 22, 2, self::LINE, 1) . self::r(84, $y + 6, 12, 3, self::INK, 1);
                    if ($v !== 'cards') {
                        $out .= self::r(8, $y + 18, 92, $v === 'lined' ? .8 : .6, self::LINE, 0);
                    }
                }
                if ($v === 'lined') {
                    $out .= self::r(8, 18, 92, 1.6, self::INK, 0);
                }
                $aside = $v === 'cards' ? self::r(108, 20, 44, 56, '#f1f5f9', 3) : ($v === 'lined' ? self::box(108, 20, 44, 56, self::LINE, 0) . self::r(108, 20, 44, 2, self::HI, 0) : '');

                return $out . $aside . self::r(114, 28, 28, 3, self::INK, 1) . self::r(114, 36, 32, 2, self::LINE, 1) . self::r(114, 42, 32, 2, self::LINE, 1) . self::r(114, 62, 32, 7, self::HI, 3);

            case 'listing_layout':
                $out = self::head();
                $side = function ($x) {
                    return self::r($x, 20, 30, 3, self::INK, 1) . self::r($x, 28, 24, 2, self::LINE, 1) . self::r($x, 34, 26, 2, self::LINE, 1) . self::r($x, 40, 20, 2, self::LINE, 1) . self::r($x, 50, 30, 3, self::INK, 1) . self::r($x, 58, 24, 2, self::LINE, 1);
                };
                if ($v === 'drawer') {
                    $out .= self::box(128, 18, 24, 7, self::INK, 3);
                    foreach ([8, 45, 82, 119] as $x) {
                        $out .= self::tile($x, 30, 33, 34) . self::tile($x, 70, 33, 28);
                    }

                    return $out;
                }
                $sx = $v === 'right' ? 122 : 8;
                $gx = $v === 'right' ? 8 : 46;
                $out .= $side($sx);
                foreach ([0, 1, 2] as $i) {
                    $out .= self::tile($gx + $i * 37, 20, 32, 36) . self::tile($gx + $i * 37, 62, 32, 34);
                }

                return $out;

            case 'category_header':
                $out = self::head();
                switch ($v) {
                    case 'banner':
                        $out .= self::r(8, 18, 144, 36, '#94a3b8', 2) . self::r(16, 34, 40, 6, '#fff', 1) . self::r(16, 44, 56, 3, 'rgba(255,255,255,.8)', 1);
                        break;
                    case 'split':
                        $out .= self::r(8, 18, 144, 36, '#f1f5f9', 3) . self::r(16, 28, 40, 6, self::INK, 1) . self::r(16, 38, 50, 3, self::LINE, 1) . self::r(84, 22, 64, 28, '#94a3b8', 2);
                        break;
                    case 'minimal':
                        $out .= self::r(56, 24, 48, 7, self::INK, 1) . self::r(44, 36, 72, 3, self::LINE, 1) . self::r(8, 48, 144, .8, self::LINE, 0);
                        break;
                    default:
                        $out .= self::r(8, 18, 40, 6, self::INK, 1) . self::r(8, 27, 70, 3, self::LINE, 1) . self::r(8, 34, 144, 18, '#94a3b8', 2);
                }
                foreach ([8, 45, 82, 119] as $x) {
                    $out .= self::tile($x, 60, 33, 38);
                }

                return $out;

            case 'grid_columns':
                $n = max(2, min(4, (int) $v));
                $gap = 6;
                $w = (144 - $gap * ($n - 1)) / $n;
                $out = self::head();
                for ($i = 0; $i < $n; ++$i) {
                    $out .= self::tile(8 + $i * ($w + $gap), 20, $w, 76);
                }

                return $out;

            case 'product_layout':
                $out = self::head();
                switch ($v) {
                    case 'wide':
                        return $out . self::productBody(8, 88, 106, 46);
                    case 'boxed':
                        return $out . self::productBody(8, 72, 92, 54, self::r(86, 16, 66, 58, '#f1f5f9', 3));
                    case 'reverse':
                        return $out . self::productBody(78, 74, 8, 60);
                    default:
                        return $out . self::productBody(8, 76, 94, 58);
                }

            case 'product_thumbs':
                $out = self::head();
                if ($v === 'left') {
                    $out .= self::r(8, 20, 12, 12, self::SOFT, 1) . self::r(8, 35, 12, 12, self::SOFT, 1) . self::r(8, 50, 12, 12, self::SOFT, 1) . self::r(24, 20, 66, 66, self::SOFT, 2);
                } else {
                    $out .= self::r(8, 18, 82, 58, self::SOFT, 2);
                    foreach ([8, 25, 42, 59] as $x) {
                        $out .= self::r($x, 80, 14, 14, self::SOFT, 1);
                    }
                }

                return $out . self::r(100, 22, 48, 5, self::INK, 1) . self::r(100, 32, 30, 4, self::LINE, 1) . self::r(100, 58, 52, 8, self::HI, 3);

            case 'product_tabs':
                $out = self::r(0, 0, 160, 100, '#fff', 0);
                if ($v === 'tabs') {
                    $out .= self::r(10, 14, 30, 4, self::INK, 1) . self::r(48, 14, 30, 4, self::LINE, 1) . self::r(86, 14, 30, 4, self::LINE, 1) . self::r(10, 22, 140, .8, self::LINE, 0) . self::r(10, 21, 30, 2, self::HI, 0);
                    for ($i = 0; $i < 5; ++$i) {
                        $out .= self::r(10, 32 + $i * 9, $i === 4 ? 80 : 140, 3, self::LINE, 1);
                    }

                    return $out;
                }
                if ($v === 'open') {
                    foreach ([12, 56] as $y) {
                        $out .= self::r(10, $y, 50, 5, self::INK, 1) . self::r(10, $y + 11, 140, 3, self::LINE, 1) . self::r(10, $y + 18, 140, 3, self::LINE, 1) . self::r(10, $y + 25, 90, 3, self::LINE, 1);
                    }

                    return $out . self::r(10, 49, 140, .8, self::LINE, 0);
                }
                $out .= self::r(10, 12, 50, 4, self::INK, 1) . self::r(10, 22, 140, 3, self::LINE, 1) . self::r(10, 29, 110, 3, self::LINE, 1);
                foreach ([42, 60, 78] as $y) {
                    $out .= self::r(10, $y - 4, 140, .8, self::LINE, 0) . self::r(10, $y + 3, 50, 4, self::INK, 1) . '<path d="M142 ' . ($y + 3) . 'l4 4 4-4" fill="none" stroke="' . self::INK . '" stroke-width="1.4"/>';
                }

                return $out;
        }

        return '';
    }

    /** Picture of a section type for the "Add a section" dialog. */
    public static function section(string $type): string
    {
        $g = '#94a3b8';
        $b = '';
        switch ($type) {
            case 'hero': $b = self::r(0, 0, 160, 100, $g, 0) . self::r(12, 58, 76, 9, '#fff', 1) . self::r(12, 74, 38, 10, '#fff', 2); break;
            case 'usp': foreach ([14, 58, 102] as $x) { $b .= '<circle cx="' . ($x + 10) . '" cy="44" r="8" fill="' . self::HI . '"/>' . self::r($x + 24, 38, 28, 4, self::INK, 1) . self::r($x + 24, 46, 20, 3, self::LINE, 1); } break;
            case 'categories': $b = self::r(10, 18, 70, 8, self::INK, 1) . self::r(10, 38, 56, 8, self::INK, 1) . self::r(10, 58, 64, 8, self::INK, 1) . self::r(100, 12, 50, 76, $g, 2); break;
            case 'products': foreach ([10, 47, 84, 121] as $x) { $b .= self::tile($x, 22, 29, 60); } break;
            case 'banners': $b = self::r(10, 16, 68, 68, $g, 2) . self::r(82, 16, 68, 68, $g, 2) . self::r(18, 60, 40, 6, '#fff', 1) . self::r(90, 60, 40, 6, '#fff', 1); break;
            case 'image_text': $b = self::r(10, 16, 70, 68, $g, 2) . self::r(92, 32, 56, 7, self::INK, 1) . self::r(92, 46, 48, 3, self::LINE, 1) . self::r(92, 53, 44, 3, self::LINE, 1) . self::r(92, 64, 30, 8, self::HI, 2); break;
            case 'testimonials': foreach ([10, 60, 110] as $x) { $b .= self::box($x, 24, 42, 52, self::LINE, 4) . self::r($x + 6, 32, 30, 3, '#f59e0b', 1) . self::r($x + 6, 42, 30, 3, self::LINE, 1) . self::r($x + 6, 48, 26, 3, self::LINE, 1) . self::r($x + 6, 64, 18, 3, self::INK, 1); } break;
            case 'brands': foreach ([12, 50, 88, 126] as $x) { $b .= self::r($x, 44, 26, 10, self::LINE, 2); } break;
            case 'countdown': $b = self::r(10, 14, 64, 72, $g, 2) . self::r(86, 26, 56, 7, self::INK, 1) . self::box(86, 44, 14, 16, self::LINE, 2) . self::box(104, 44, 14, 16, self::LINE, 2) . self::box(122, 44, 14, 16, self::LINE, 2) . self::r(86, 68, 40, 8, self::HI, 2); break;
            case 'faq': foreach ([20, 40, 60] as $y) { $b .= self::r(14, $y, 120, 4, self::INK, 1) . '<path d="M140 ' . ($y - 1) . 'l3 3 3-3" fill="none" stroke="' . self::INK . '"/>' . self::r(14, $y + 12, 132, .8, self::LINE, 0); } break;
            case 'gallery': foreach ([2, 42, 82, 122] as $x) { $b .= self::r($x, 22, 36, 56, $g, 0); } break;
            case 'text': $b = self::r(40, 30, 80, 8, self::INK, 1) . self::r(30, 46, 100, 3, self::LINE, 1) . self::r(36, 53, 88, 3, self::LINE, 1) . self::r(62, 64, 36, 9, self::HI, 2); break;
            case 'newsletter': $b = self::r(10, 14, 60, 72, $g, 2) . self::r(84, 34, 60, 7, self::INK, 1) . self::box(84, 50, 44, 12, self::LINE, 2) . self::r(128, 50, 20, 12, self::HI, 2); break;
        }

        return '<svg viewBox="0 0 160 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><rect width="160" height="100" fill="#f8fafc"/>' . $b . '</svg>';
    }

    private static function card(string $v): string
    {
        $out = '';
        foreach ([18, 88] as $x) {
            $w = 54;
            switch ($v) {
                case 'bordered':
                    $out .= self::box($x - 4, 8, $w + 8, 86, self::LINE, 3) . self::tile($x, 12, $w, 64) . self::r($x, 82, $w, 7, self::HI, 3);
                    break;
                case 'shadow':
                    $out .= '<rect x="' . ($x - 4) . '" y="8" width="' . ($w + 8) . '" height="86" rx="4" fill="#fff" style="filter:drop-shadow(0 2px 4px rgba(0,0,0,.18))"/>' . self::tile($x, 12, $w, 64) . self::r($x, 82, $w, 7, self::HI, 3);
                    break;
                case 'minimal':
                    $out .= self::r($x, 8, $w, 52, self::SOFT, 0) . self::r($x + 9, 66, 36, 3, self::INK, 1) . self::r($x + 17, 73, 20, 3, self::LINE, 1) . self::r($x + 15, 84, 24, 1.4, self::HI, 0);
                    break;
                case 'overlay':
                    $out .= self::r($x, 8, $w, 60, self::SOFT, 2) . self::r($x + 4, 56, $w - 8, 8, self::HI, 3) . self::r($x, 74, 32, 3, self::INK, 1) . self::r($x + 40, 74, 14, 3, self::INK, 1);
                    break;
                case 'boxed':
                    $out .= self::r($x - 4, 8, $w + 8, 86, '#f1f5f9', 8) . self::r($x, 12, $w, 52, '#fff', 6) . self::r($x, 70, 36, 3, self::INK, 1) . self::r($x, 77, 20, 3, self::HI, 1) . '<circle cx="' . ($x + $w - 6) . '" cy="84" r="6" fill="' . self::HI . '"/>';
                    break;
                case 'editorial':
                    $out .= self::r($x, 6, $w, 66, self::SOFT, 0) . self::r($x + 8, 78, 38, 2.4, self::INK, 1) . self::r($x + 18, 84, 18, 2.4, self::LINE, 1);
                    break;
                case 'framed':
                    $out .= self::box($x - 4, 8, $w + 8, 86, self::LINE, 0) . self::r($x, 12, $w, 50, self::SOFT, 0) . self::r($x, 66, 40, 3, self::INK, 1) . self::r($x, 73, 18, 3, self::LINE, 1) . self::box($x, 81, $w, 9, self::INK, 0);
                    break;
                case 'compact':
                    $out .= self::box($x - 4, 14, $w + 8, 72, self::LINE, 3) . self::r($x, 18, $w, 40, self::SOFT, 2) . self::r($x, 63, 40, 3, self::INK, 1) . self::r($x, 69, 30, 3, self::INK, 1) . self::r($x, 76, 16, 3, self::LINE, 1) . self::r($x + $w - 9, 72, 9, 9, self::HI, 2);
                    break;
                case 'label':
                    $out .= self::r($x, 6, $w, 66, self::SOFT, 0) . self::r($x, 77, 30, 3, self::INK, 1) . self::r($x + $w - 12, 77, 12, 3, self::INK, 1)
                        . self::box($x, 84, 22, 8, self::INK, 1) . self::r($x, 84, 2, 8, self::HI, 0) . '<circle cx="' . ($x + $w - 3) . '" cy="88" r="2.5" fill="' . self::INK . '"/>';
                    break;
                default:
                    $out .= self::tile($x, 8, $w, 72) . self::r($x, 84, $w, 8, self::HI, 3);
            }
        }

        return $out;
    }
}
