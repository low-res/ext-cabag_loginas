<?php

if (!defined('TYPO3')) {
	die ('Access denied.');
}

if (\TYPO3\CMS\Core\Http\ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
	// register the class as toolbar item
	$GLOBALS['TYPO3_CONF_VARS']['BE']['toolbarItems']['cabag_loginas'] = \Cabag\CabagLoginas\Hook\ToolbarItemHook::class;
}
