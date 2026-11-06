# Stride Atelier: Free PrestaShop 9 Theme for Shoe and Sneaker Stores

Stride Atelier is a free PrestaShop 9 theme for shoe, sneaker and boot shops. It is a responsive Hummingbird child theme where every shoe carries a shoebox label with its materials, sole and fit, EU sizes work as a quick filter, and shoes with a stitched sole show a Resolable stamp. It comes with AJAX add to bag in a side cart, a one-click demo in 10 languages and the free Hummingbird Theme Kit module.

**[Live demo](https://iamshoaibktk.github.io/prestashop-theme-stride-atelier/)** | **[Download the free theme](https://github.com/iamshoaibktk/prestashop-theme-stride-atelier/releases/latest)**

![Stride Atelier: a Hummingbird child theme for PrestaShop 9](screenshots/readme-cover.jpg)

![Stride Atelier category page, product page, add to cart and the Theme Kit back office](screenshots/readme-pages.jpg)

![Stride Atelier on a phone: home, category and product](screenshots/readme-phones.jpg)


## Features of this free PrestaShop 9 theme

- **The shoebox label**: product cards end in a label with the reference and fit, and the product page shows the upper, sole, fit, care and origin as a ruled label.
- **Fit notes and EU sizes**: each shoe says if it runs small, true to size or large, right under the size buttons, and EU sizes are the quick filter on category pages.
- **Resolable stamp**: shoes with a stitched sole carry a stamp, and the homepage explains the resoling service in three steps.
- **AJAX add to bag**: a side bag with the free-delivery bar, and a square "+" on each product card that asks for the size first.
- **Child theme of Hummingbird** (`parent: hummingbird`). No parent templates are copied, so Hummingbird and PrestaShop updates keep working.
- **Hummingbird Theme Kit module** (`hbkit` v1.1.0): colours, fonts, corners, header and footer style as CSS variables, a top bar, and a homepage builder (categories, faq, hero, image text, products, testimonials).
- **Page layouts you can switch in the back office**: side cart, cart page, category pages, product page and product cards, each with several picture-chosen styles. This demo uses: side cart that slides in; classic cart page; full-width grid with a filter button; minimal centred category header; classic product page; details in an accordion; 4 products per row; overlay product cards.
- **One-click demo import**: 24 products with sizes/colours and features, 6 categories, brands, 8 CMS pages, main menu, footer links, reassurance blocks and 7 homepage sections.
- **10 languages** for everything: English, French, Spanish, Italian, Polish, German, Czech, Dutch, Portuguese, Russian.
- Self-hosted fonts (SIL Open Font License), no external font or tracking calls.

## Requirements

- PrestaShop 9.2 or newer with the Hummingbird theme (installed by default)
- PHP 8.1+

## Installation

1. Download the release files: `stride-atelier-theme-v1.0.0.zip` and `hbkit-v1.1.0.zip`.
2. **Modules > Module Manager > Upload a module**: upload `hbkit-v1.1.0.zip`.
3. **Design > Theme & Logo > Add new theme**: upload `stride-atelier-theme-v1.0.0.zip`, then click *Use this theme*.
4. **Design > Theme Kit > Demo import**: pick *Stride Atelier*, type `IMPORT` and click *Import this demo*.
   On a fresh shop keep *Remove existing content first* checked. Orders and customers are never touched.
5. Adjust colours, fonts and page layouts in **Theme Kit > Design**, and every homepage section in **Theme Kit > Homepage sections**.

## Customising safely

- Put your CSS in `themes/stride-atelier/assets/css/custom.css`.
- Colours, fonts, spacing and page layouts are settings in Theme Kit > Design; no template edits needed.
- To change a template, copy only the block you need with `{extends file='parent:...'}` instead of copying the whole file.

## Credits

- Photos: see [CREDITS.md](CREDITS.md) (Pixabay Content License).
- Fonts: SIL Open Font License 1.1 (licence files in `theme/stride-atelier/assets/fonts/`).
- Stride Atelier and its brands are fictional and created for this demo.


## Frequently asked questions

**Is Stride Atelier free?**
Yes. It is a free and open-source PrestaShop theme under the AFL-3.0 licence. You can use it on personal and commercial shops.

**Which PrestaShop versions does it support?**
PrestaShop 9.2 and newer, with PHP 8.1 or newer. It is a child theme of Hummingbird, the default theme of PrestaShop 9.

**Will PrestaShop or Hummingbird updates break it?**
No parent template is copied. The theme only adds its own styles and the few templates it changes, so updates to Hummingbird and PrestaShop keep working.

**Which languages are included?**
All texts and the demo content come in 10 languages: English, French, Spanish, Italian, Polish, German, Czech, Dutch, Portuguese, Russian.

**Can I use it for a different kind of shop?**
Yes. It is made for a shoe and sneaker store, but colours, fonts, page layouts and every homepage section are settings in Theme Kit, and the demo content can be removed.

**Can I see it before installing?**
Yes: the [live demo](https://iamshoaibktk.github.io/prestashop-theme-stride-atelier/) is a static copy of the demo store. The cart, account and search work once the theme is installed on PrestaShop.

**Where can I get help, or a custom PrestaShop theme or module?**
Contact the author below: installation help, custom demos and PrestaShop modules.

## Licence

Academic Free License 3.0 (AFL-3.0), see [LICENSE.md](LICENSE.md).

## More free PrestaShop 9 themes

- [Maison Vellor](https://github.com/iamshoaibktk/prestashop-theme-maison-vellor): free PrestaShop 9 theme for fashion boutiques
- [Northline Outfitters](https://github.com/iamshoaibktk/prestashop-theme-northline-outfitters): free PrestaShop 9 theme for outdoor clothing stores
- [Little Sprout](https://github.com/iamshoaibktk/prestashop-theme-little-sprout): free PrestaShop 9 theme for baby and kids' clothing stores

## Author & support

Muhammad Shoaib — iamshoaibktk@gmail.com · WhatsApp +923459016286 · [Fiverr](https://www.fiverr.com/shoaibktk)
Need installation help, a custom demo or a PrestaShop module? Get in touch.
