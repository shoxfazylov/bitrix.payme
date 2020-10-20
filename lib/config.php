<?php
namespace Qsoft\Payme;

/**
 * Class Config
 * @package Qsoft\Payme
 */
class Config
{
    protected static $settings = [];
    protected static $isLoad = false;
    protected static $site = null;
    protected static $config = [];
    protected static $modes = ['file' => 'payme.php'];


    /**
     * Загружает конфигурацию
     * @throws \Exception
     */
    protected static function load()
    {
        if (!static::$isLoad) {
            $mode = static::$modes;

            if (empty($mode)) throw new \Exception("Тип конфигурации " . $typeMode . " не обнаружен.");

            $file = realpath(__DIR__ . '/../') . '/config/' . $mode['file'];

            if (!file_exists($file)) throw new \Exception("Путь до конфигурации " . $typeMode . " [" . $file . "] не обнаружен.");

            static::$config = include_once($file);

            if (empty(static::$config)) throw new \Exception("Конфигурация " . $typeMode . " не обнаружена.");

            static::$isLoad = true;
        }
    }

    /**
     * Возвращает значение параметра
     * @param $paramPath
     * @return mixed
     * @throws \Exception
     */
    public static function getError($errorCode)
    {
    	if(is_null($errorCode)) return null;
        if (!static::$isLoad)
        {
            static::load();
        }

        if (!(
            (!empty($errorCode) && isset(static::$config[$errorCode]))
        )) {
            throw new \Exception('Не найден параметр в файле конфигурации по коду ' . $paramPath);
        }

        return static::$config[$errorCode];
    }
}