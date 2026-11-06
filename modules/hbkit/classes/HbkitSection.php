<?php
/**
 * @author    iamshoaibktk@gmail.com (WhatsApp: +923459016286)
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HbkitSection extends ObjectModel
{
    public $id_shop = 1;
    public $type;
    public $position = 0;
    public $active = 1;
    /** @var string JSON */
    public $settings = '{}';
    public $title;
    public $subtitle;
    public $content;

    public static $definition = [
        'table' => 'hbkit_section',
        'primary' => 'id_hbkit_section',
        'multilang' => true,
        'fields' => [
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'type' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'settings' => ['type' => self::TYPE_STRING, 'size' => 16777215],
            'title' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255],
            'subtitle' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255],
            'content' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 16777215],
        ],
    ];

    public function getSettings(): array
    {
        $s = json_decode((string) $this->settings, true);

        return is_array($s) ? $s : [];
    }

    public function add($autoDate = true, $nullValues = false)
    {
        if (!$this->position) {
            $this->position = 1 + (int) Db::getInstance()->getValue('SELECT MAX(position) FROM `' . _DB_PREFIX_ . 'hbkit_section` WHERE id_shop = ' . (int) $this->id_shop);
        }

        return parent::add($autoDate, $nullValues);
    }

    public function delete()
    {
        foreach (HbkitItem::getForSection((int) $this->id, null, false) as $row) {
            (new HbkitItem((int) $row['id_hbkit_item']))->delete();
        }

        return parent::delete();
    }

    public static function getAll(int $idShop, ?int $idLang = null, bool $activeOnly = false): array
    {
        $q = new DbQuery();
        $q->select('s.*')->from('hbkit_section', 's')->where('s.id_shop = ' . $idShop)->orderBy('s.position ASC, s.id_hbkit_section ASC');
        if ($idLang) {
            $q->select('sl.title, sl.subtitle, sl.content')
                ->leftJoin('hbkit_section_lang', 'sl', 'sl.id_hbkit_section = s.id_hbkit_section AND sl.id_lang = ' . $idLang);
        }
        if ($activeOnly) {
            $q->where('s.active = 1');
        }

        return Db::getInstance()->executeS($q) ?: [];
    }
}
