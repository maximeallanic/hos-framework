<?php

/**
 * Created by PhpStorm.
 * User: mallanic
 * Date: 23/09/15
 * Time: 17:13
 */

namespace Hos;

class Translator
{
    CONST TRANSLATION_DIR = Option::APP_DIR."translation";
    CONST TRANSLATION_FILE = self::TRANSLATION_DIR."/%s.json";

    CONST COOKIE = "language";
    CONST DEFAULT_LANG = "fr";
    private $languages = [];
    private $hasChange = [];

    function __construct() {
        if (!file_exists(self::TRANSLATION_DIR))
            mkdir(self::TRANSLATION_DIR);
    }

    public function getTranslations($lang = null) {

        if (!$lang)
            $lang = $this->get();
        if (isset($this->languages[$lang]))
            return $this->languages[$lang];

        $file = sprintf(self::TRANSLATION_FILE, $lang);
        if (!file_exists($file)) {
            $lang = self::DEFAULT_LANG;
            $file = sprintf(self::TRANSLATION_FILE, self::DEFAULT_LANG);
            if (!file_exists($file))
                return [];
        }
        $this->languages[$lang] = json_decode(file_get_contents($file), true);
        return $this->languages[$lang];
    }

    /**
     * @api
     */
    function get() {
        if (isset($_COOKIE[self::COOKIE]))
            return $_COOKIE[self::COOKIE];
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
            return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        return self::DEFAULT_LANG;
    }

    /**
     * @url POST
     */
    function set($lang) {
        return setcookie(self::COOKIE, $lang, time()+(60*60*24*30));
    }

    /**
     * @api
     * @param $code
     * @return string
     */
    function translate($code) {
        $translations = $this->getTranslations();
        return isset($translations[$code]) ? $translations[$code] : $code;
    }


    /**
     * @url POST translate/add
     * @param $code
     * @param $lang
     * @param $message
     * @return bool
     */
    function addTranslation($code, $lang, $message) {
        $this->getTranslations($lang);
        if (!isset($this->languages[$lang]))
            $this->languages[$lang] = [];
        $this->languages[$lang][$code] = $message;
        $this->hasChange[$lang] = true;
    }

    function __destruct()
    {
        foreach($this->hasChange as $key=>$value) {
            if (isset($this->languages[$key]) && $value) {
                $content = json_encode($this->languages[$key]);
                file_put_contents(sprintf(self::TRANSLATION_FILE, $key), $content);
            }
        }
    }
}