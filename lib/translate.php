<?
/* Class to manage alternative languages efficiently */

class Translate {
    function __construct($includeroot, $language) {
        if (! $includeroot)
            throw new TranslatorError("Empty includeroot.");
        if (! $language)
            throw new TranslatorError("Empty language.");
        $this->includeroot = $includeroot;
        $this->setlanguage($language);
    }

    /**
     * Checks to see if there is a translation in $lang.
     * If not, simply returns $default.
     **/
    public function __($default) {
        if (array_key_exists($default, $this->lang)) {
            return $this->lang[$default];
        } else {
            return $default;
        }
    }

    /**
     * Set the language code to something new
     * and load the needed $lang variable.
     * */
    public function setlanguage($newlanguage) {
        $this->language_code = $newlanguage;
        unset($this->lang);
        require("{$this->includeroot}/lang/lang.{$newlanguage}.php");
        $this->lang = $lang;
    }

    /**
     * Add or change a translation
     */
    public function newTranslation($key, $value) {
        $this->lang[$key] = $value;
    }
}


