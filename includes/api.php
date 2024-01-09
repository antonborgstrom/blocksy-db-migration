<?php

namespace BlocksyDBMigration;

class API {
	protected $ajax_actions = [
		'get_site_status',
		'migrate_table',
		'get_table_status',
		'regenerate_css',
	];

	public function __construct() {
		$this->attach_ajax_actions();

		// Ensure Blocksy V1 never runs css regeneration during upgrade.
		// This is the cause of many many problems.
		add_action(
			'init',
			function () {
				if (! class_exists('\Blocksy\Plugin')) {
					return;
				}

				$instance = \Blocksy\Plugin::instance();

				if (! $instance || ! $instance->cache_manager) {
					return;
				}

				remove_action(
					'upgrader_process_complete',
					[$instance->cache_manager, 'handle_update'],
					10, 2
				);
			},
			5
		);
	}

	public function migrate_table() {
		$this->check_capability('manage_options');

		if (! isset($_GET['table'])) {
			wp_send_json_error([
				'message' => 'No table provided'
			]);
		}

		if (! isset($_GET['old'])) {
			wp_send_json_error([
				'message' => 'No old value provided'
			]);
		}

		if (! isset($_GET['new'])) {
			wp_send_json_error([
				'message' => 'No new value provided'
			]);
		}

		$r = new SearchReplace();

		$result = $r->invoke([
			'old' => $_GET['old'],
			'new' => $_GET['new'],
			'dry_run' => false,
			'tables' => [$_GET['table']]
		]);

		wp_send_json_success([
			'result' => $result,
		]);
	}

	public function get_table_status() {
		$this->check_capability('manage_options');

		if (! isset($_GET['table'])) {
			wp_send_json_error([
				'message' => 'No table provided'
			]);
		}

		if (! isset($_GET['old'])) {
			wp_send_json_error([
				'message' => 'No old value provided'
			]);
		}

		if (! isset($_GET['new'])) {
			wp_send_json_error([
				'message' => 'No new value provided'
			]);
		}

		$result = $this->migrate_for(
			$_GET['table'],
			$_GET['old'],
			$_GET['new']
		);

		wp_send_json_success([
			'result' => $result,
		]);
	}

	public function migrate_for($table, $old, $new, $dry_run = true) {
		$replacements = [
			[
				'old' => $old,
				'new' => $new
			],
		];

		/*
		$replacements = [
			[
				'old' => 'paletteColor',
				'new' => 'theme-palette-color-',
			],

			[
				'old' => 'buttonInitialColor',
				'new' => 'theme-button-background-initial-color',
			],

			[
				'old' => '--fontFamily',
				'new' => '--theme-font-family'
			]
		];

		$greenshift_variables = [
			'--linkInitialColor' => '--theme-link-initial-color',
			'--container-width' => '--theme-container-width',
			'--normal-container-max-width' => '--theme-normal-container-max-width',
			'--narrow-container-max-width' => '--theme-narrow-container-max-width',
			'--buttonFontFamily' => '--theme-button-font-family',
			'--buttonFontSize' => '--theme-button-font-size',
			'--buttonFontWeight' => '--theme-button-font-weight',
			'--buttonFontStyle' => '--theme-button-font-style',
			'--buttonLineHeight' => '--theme-button-line-height',
			'--buttonLetterSpacing' => '--theme-button-letter-spacing',
			'--buttonTextTransform' => '--theme-button-text-transform',
			'--buttonTextDecoration' => '--theme-button-text-decoration',
			'--buttonTextInitialColor' => '--theme-button-text-initial-color',
			'--button-border' => '--theme-button-border',
			'--buttonInitialColor' => '--theme-button-background-initial-color',
			'--buttonMinHeight' => '--theme-button-min-height',
			'--buttonBorderRadius' => '--theme-button-border-radius',
			'--button-padding' => '--theme-button-padding',
			'--button-border-hover-color' => '--theme-button-border-hover-color',
			'--buttonTextHoverColor' => '--theme-button-text-hover-color',
			'--buttonHoverColor' => '--theme-button-background-hover-color'
		];

		foreach ($greenshift_variables as $greenshift_key => $greenshift_value) {
			$replacements[] = [
				'old' => $greenshift_key,
				'new' => $greenshift_value
			];
		}
		 */

		$r = new SearchReplace();

		$result = [
			'total' => 0,
		];

		foreach ($replacements as $replacement) {
			$replacement_result = $r->invoke([
				'old' => $replacement['old'],
				'new' => $replacement['new'],
				'tables' => [$table],
				'dry_run' => $dry_run
			]);

			$result['total'] += $replacement_result['total'];
		}

		wp_send_json_success([
			'result' => $result
		]);
	}

	public function regenerate_css() {
		$this->check_capability('manage_options');

		do_action('blocksy:cache-manager:purge-all');
		do_action('blocksy:dynamic-css:refresh-caches');

		delete_option('blocksy_db_version');

		do_action('blocksy:db-versioning:trigger-migration');

		wp_send_json_success();
	}

	public function get_site_status() {
		$this->check_capability('manage_options');

		/*
		$r = new SearchReplace();

		$palette_result = $r->invoke([
			'old' => 'paletteColor',
			'new' => 'theme-palette-color-'
		]);

		$button_result = $r->invoke([
			'old' => 'buttonInitialColor',
			'new' => 'theme-button-background-initial-color'
		]);
		 */

		if (! function_exists('get_plugin_data')) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		global $wpdb;

		$data = [
			'theme_version' => '1.0.0',
			'blocksy_db_version' => get_option('blocksy_db_version'),

			'plugin_version' => '1.0.0',

			// 'migration_status' => $palette_result,
			// 'button_result' => $button_result,

			// 'plugins' => get_plugins(),

			'all_tables' => Utils::wp_get_table_names(),

			'prefix' => $wpdb->base_prefix,
		];

		if (defined('BLOCKSY__FILE__')) {
			$plugin_data = get_plugin_data(BLOCKSY__FILE__);
			$data['plugin_version'] = $plugin_data['Version'];
		}

		if (wp_get_theme('blocksy')) {
			$data['theme_version'] = wp_get_theme('blocksy')->get('Version');
		}

		wp_send_json_success($data);
	}

	public function check_capability($cap = 'install_plugins') {
		if (! current_user_can($cap)) {
			wp_send_json_error([
				'message' => __('You are not allowed to do this', 'blocksy-companion')
			]);
		}
	}

	public function attach_ajax_actions() {
		foreach ($this->ajax_actions as $action) {
			add_action(
				'wp_ajax_' . $action,
				[$this, $action]
			);
		}
	}
}

