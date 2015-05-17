<?php
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
        require("{$this->includeroot}/lang/lang.{$this->language_code}.php");
        $this->lang = $lang;
    }

    /**
     * Add or change a translation
     */
    public function newTranslation($key, $value) {
        $this->lang[$key] = $value;
    }
}

require_once("./Exceptions.php");
class TranslatorError extends FlexicalException {
}

function __() {
    /* Takes one or more arguments:
     * - First argument is translation key
     * - Second .. n args are progressive indexes
     *   which will be used to look up an item in translation value
     * - For translations expecting a string, use only first argument
     * - For translations expecting an array, others may be used
     *   to obtain a specific indexed item.
     */
    global $translator;
    $numargs = func_num_args();
    if ($numargs < 1) {
        return "Can't translate nothing!";
    }
    $args = func_get_args();
    $translation = $translator->__(array_shift($args));
    while (count($args)) {
        if (is_array($translation)) {
            $translation = $translation[array_shift($args)];
        } else {
            return "Can't index '{$translation}' as array!";
        }
    }
    return $translation;
}

