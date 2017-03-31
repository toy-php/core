<?php

namespace Core\Locale;

class I18n
{

    protected static $dictionary;

    /**
     * Загрузить словарь
     * @param \ArrayAccess $dictionary
     */
    public static function loadDictionary(\ArrayAccess $dictionary)
    {
        self::$dictionary = $dictionary;
    }

    /**
     * Перевести фразу
     * @param string $phrase
     * @return string
     */
    public static function t($phrase)
    {
        return (!empty(static::$dictionary) and isset(static::$dictionary[$phrase]))
            ? static::$dictionary[$phrase]
            : $phrase;
    }
}