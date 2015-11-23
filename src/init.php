<?php
use box\BoxException;

$defineFuctions = true;

if (getenv('BOX_DISABLE_FUNCTIONS') || (defined('BOX_DISABLE_FUNCTIONS') && BOX_DISABLE_FUNCTIONS)) {
    $defineFuctions = false;
}

if (defined('BOX_FUNCTIONS_EXIST') && BOX_FUNCTIONS_EXIST) {
    $defineFuctions = false;
}

if ($defineFuctions) {
    define('BOX_FUNCTIONS_EXIST', true);

    function box($name, $box = null) {
        static $boxes = [];
        if ($name === false) {
            $boxes = [];
            return;
        }
        if ($box === false) {
            unset($boxes[$name]);
            return;
        }
        if ($box) {
            return $boxes[$name] = $box;
        }
        if (isset($boxes[$name])) {
            return $boxes[$name];
        }
        throw new BoxException("Unexisting box `'{$name}'`.");
    }

}
