<?php

/*
Plugin Name: Blocksy DB Migration
Description: This plugin will allow you to correctly migrate your database from Blocksy v1 to v2.
Version: 1.0.2
Author: CreativeThemes
Author URI: https://creativethemes.com
Text Domain: blocksy-companion
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

define('BLOCKSY_DB_MIGRATION__FILE__', __FILE__);
define('BLOCKSY_DB_MIGRATION_PLUGIN_BASE', plugin_basename(BLOCKSY_DB_MIGRATION__FILE__));
define('BLOCKSY_DB_MIGRATION_PATH', plugin_dir_path(BLOCKSY_DB_MIGRATION__FILE__));
define('BLOCKSY_DB_MIGRATION_URL', plugin_dir_url(BLOCKSY_DB_MIGRATION__FILE__));

require(BLOCKSY_DB_MIGRATION_PATH . 'includes/api.php');
require(BLOCKSY_DB_MIGRATION_PATH . 'includes/db-search-replace.php');
require(BLOCKSY_DB_MIGRATION_PATH . 'includes/db-search-replacer.php');
require(BLOCKSY_DB_MIGRATION_PATH . 'includes/utils.php');
require(BLOCKSY_DB_MIGRATION_PATH . 'plugin.php');


register_activation_hook(
	__FILE__,
	function () {
		add_option('blocksy_db_migration_activation_redirect', true);
	}
);

