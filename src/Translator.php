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

    /**
     * @api / {"method":"GET"} Get all Translations from actual or specific Language
     * @param string $lang {query} Description
     * @return array
     */
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
     * @api /use {"method":"GET"}
     */
    function get() {
        if (isset($_COOKIE[self::COOKIE]))
            return $_COOKIE[self::COOKIE];
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
            return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        return self::DEFAULT_LANG;
    }

    /**
     * @api /use {"method":"POST"}
     * @param string $lang {"type":"query"} The language to use on the server
     * @return bool
     */
    function set($lang) {
        return setcookie(self::COOKIE, $lang, time()+(60*60*24*30));
    }

    /**
     * @api /translate {"method":"GET"} Get Translation
     * @param string $code {"type":"query"} Code of Translation
     * @return string
     */
    function translate($code) {
        $translations = $this->getTranslations();
        return isset($translations[$code]) ? $translations[$code] : $code;
    }


    /**
     * @api /translate {"method":"POST"} Add Translation
     * @param string $code {"type":"body"} Code Of Translation
     * @param string $lang {"type":"body"} Code Of Lang
     * @param string $message {"type":"body"} Message
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