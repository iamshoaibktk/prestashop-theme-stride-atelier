<?php
/**
 * @author    iamshoaibktk@gmail.com (WhatsApp: +923459016286)
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HbkitItem extends ObjectModel
{
    public $id_hbkit_section;
    public $position = 0;
    public $active = 1;
    /** @var string JSON: image, link, icon, id_category, id_product, id_manufacturer, rating, tone, align... */
    public $data = '{}';
    public $title;
    public $subtitle;
    public $text;
    public $button;
    public $url;

    public static $definition = [
        'table' => 'hbkit_item',
        'primary' => 'id_hbkit_item',
        'multilang' => true,
        'fields' => [
            'id_hbkit_section' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'data' => ['type' => self::TYPE_STRING, 'size' => 65535],
            'title' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255],
            'subtitle' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255],
            'text' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 65535],
            'button' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128],
            'url' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isUrlOrEmpty', 'size' => 512],
        ],
    ];

    public function getData(): array
    {
        $d = json_decode((string) $this->data, true);

        return is_array($d) ? $d : [];
    }

    public function add($autoDate = true, $nullValues = false)
    {
        if (!$this->position) {
            $this->position = 1 + (int) Db::getInstance()->getValue('SELECT MAX(position) FROM `' . _DB_PREFIX_ . 'hbkit_item` WHERE id_hbkit_section = ' . (int) $this->id_hbkit_section);
        }

        return parent::add($autoDate, $nullValues);
    }

    public static function getForSection(int $idSection, ?int $idLang = null, bool $activeOnly = true): array
    {
        $q = new DbQuery();
        $q->select('i.*')->from('hbkit_item', 'i')->where('i.id_hbkit_section = ' . $idSection)->orderBy('i.position ASC, i.id_hbkit_item ASC');
        if ($idLang) {
            $q->select('il.title, il.subtitle, il.text, il.button, il.url')
                ->leftJoin('hbkit_item_lang', 'il', 'il.id_hbkit_item = i.id_hbkit_item AND il.id_lang = ' . $idLang);
        }
        if ($activeOnly) {
            $q->where('i.active = 1');
        }

        return Db::getInstance()->executeS($q) ?: [];
    }
}
