<?php
use Lead\Box\BoxException;
use Lead\Box\Box;

$defineFuctions = true;

if (getenv('BOX_DISABLE_FUNCTIONS') || (defined('BOX_DISABLE_FUNCTIONS') && BOX_DISABLE_FUNCTIONS)) {
    $defineFuctions = false;
}

if (defined('BOX_FUNCTIONS_EXIST') && BOX_FUNCTIONS_EXIST) {
    $defineFuctions = false;
}

if ($defineFuctions) {
    define('BOX_FUNCTIONS_EXIST', true);

    function box($name = '', $box = null) {
        static $boxes = [];

        if (func_num_args() === 1) {
            if ($name === false) {
                $boxes = [];
                return;
            }
            if (is_object($name)) {
                return $boxes[''] = $name;
            }
            if (isset($boxes[$name])) {
                return $boxes[$name];
            }
            throw new BoxException("Unexisting box `'{$name}'`.");
        }
        if (func_num_args() === 2) {
            if ($box === false) {
                unset($boxes[$name]);
                return;
            }
            return $boxes[$name] = $box;
        }
        if (!isset($boxes[''])) {
            $boxes[''] = new Box();
        }
        return $boxes[''];
    }

}
