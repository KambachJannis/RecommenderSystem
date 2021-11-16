<?php

/**
 * Class DatabaseAbstraction
 */
abstract class DatabaseAbstraction
{
    /** @var Database $Database */
    protected static $Database = null;

    /**
     * @param Database $db
     */
    public static function Init($db)
    {
        self::$Database = $db;

        if (!$db->table_exists('user'))
        {
            exit('import is needed');
        }
    }

    /**
     * @param string $string
     * @return mixed
     */
    protected static function Filter($string)
    {
        return self::$Database->filter($string);
    }

    /**
     * @param string $string
     * @return string
     */
    protected static function Clean($string)
    {
        return self::$Database->clean($string);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected static function Escape($string)
    {
        return self::$Database->escape($string);
    }
}