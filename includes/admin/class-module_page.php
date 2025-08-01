<?php

namespace OES\Monadic;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('\OES\Admin\Module_Page')) oes_include('admin/pages/class-module_page.php');

if (!class_exists('Monadic_Module_Page')) :

    class Monadic_Module_Page extends \OES\Admin\Module_Page
    {
        //@oesDevelopment: add help tabs
    }

    new Monadic_Module_Page([
        'name' => 'Monadic Exploration',
        'schema_enabled' => false,
        'file' => (__DIR__ . '/views/view-settings-me.php')
    ]);

endif;