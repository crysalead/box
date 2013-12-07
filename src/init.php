<?php
use box\BoxException;

if (!defined('BOX_DISABLE_FUNCTIONS') || !BOX_DISABLE_FUNCTIONS) {

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
