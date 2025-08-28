<?php

namespace ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem;

use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\Exceptions\ItemNotFoundById;
use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\Exceptions\ItemTypeNotFound;
use ModulesGarden\TTSGGSModule\Core\UI\Formatters\RelatedItem\Exceptions\WrongItemTypeFound;

class RelatedItem
{
    //Types
    public const TYPE_ADMIN         = 'admin';
    public const TYPE_CLIENT        = 'client';
    public const TYPE_SERVICE       = 'service';
    public const TYPE_HOSTING       = 'hosting';
    public const TYPE_ADDON         = 'addon';
    public const TYPE_DOMAIN        = 'domain';
    public const TYPE_INVOICE       = 'invoice';
    public const TYPE_TICKET        = 'ticket';
    public const TYPE_ORDER         = 'order';
    public const TYPE_PRODUCT       = 'product';
    public const TYPE_PRODUCT_ADDON = 'productAddon';

    protected const ITEM_TYPES_DIR = "ItemTypes";

    static string $defaultReturn = '-';

    public static function format($type, $id):string
    {
        try
        {
            $itemType = self::buildItem($type, $id);

            return self::formatFromItem($itemType);
        }
        catch (WrongItemTypeFound | ItemTypeNotFound)
        {
            return static::$defaultReturn;
        }
    }

    public static function formatFromData(array $data):string
    {
        foreach ($data as $type => $id)
        {
            try
            {
                $itemType = self::buildItem($type, $id);

                return self::formatFromItem($itemType);
            }
            catch (WrongItemTypeFound | ItemTypeNotFound)
            {
                continue;
            }
        }

        return static::$defaultReturn;
    }

    public static function formatFromItem(ItemTypeInterface $item):string
    {
        try
        {
            return sprintf("<a href='%s'>%s</a>", $item->generateUrl(), $item->generateName());
        }
        catch (ItemNotFoundById)
        {
            return static::$defaultReturn;
        }
    }

    public static function buildItem(string $type, $id):ItemTypeWithModel
    {
        $itemTypeClass = self::foundItemTypeClass($type);

        $itemType = new $itemTypeClass($id);

        if (!$itemType instanceof ItemTypeWithModel)
        {
            throw new WrongItemTypeFound();
        }

        return $itemType;
    }

    protected static function foundItemTypeClass(string $itemType):string
    {
        $className = __NAMESPACE__ . '\\' . self::ITEM_TYPES_DIR . '\\' . ucfirst($itemType);

        if (!class_exists($className) )
        {
            throw new ItemTypeNotFound();
        }

        return $className;
    }
}