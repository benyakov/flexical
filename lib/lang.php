<?php
$_ = "__";
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

