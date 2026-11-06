<?php
/**
 * Theme Kit 1.1.1: registers actionOutputHTMLBefore, which repairs the Product structured data that PrestaShop 9.2
 * prints empty when a feature value contains letters such as Polish "ą" or Russian "х".
 * @author iamshoaibktk@gmail.com  @license AFL-3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_1($module)
{
    return $module->ensureHooks();
}
