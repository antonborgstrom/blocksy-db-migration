<?php

namespace BlocksyDBMigration;

class Plugin {
	/**
	 * Blocksy instance.
	 *
	 * Holds the blocksy plugin instance.
	 *
	 * @var Plugin
	 */
	private static $instance = null;

	/**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @static
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function init() {
	}

	public function early_init() {
		add_filter(
			'blocksy:db-versioning:v2:should-migrate-color-palette',
			'__return_false'
		);

		add_action(
			'admin_init',
			function () {
				if (get_option('blocksy_db_migration_activation_redirect', false)) {
					delete_option('blocksy_db_migration_activation_redirect');
					wp_redirect(admin_url('admin.php?page=blocksy-migration'));
				}
			}
		);

		new API();

		add_action('admin_menu', function () {
			add_menu_page(
				'Blocksy Migrator',
				'Blocksy Migrator',
				'manage_options',
				'blocksy-migration',
				[$this, 'render_page'],
				'dashicons-database',
				3
			);
		});

		if ($this->is_dashboard_page()) {
			add_action(
				'admin_enqueue_scripts',
				[$this, 'enqueue_static']
			);
		}

		if ($this->is_dashboard_page()) {
			add_action(
				'admin_print_scripts',
				function () {
					global $wp_filter;

					if (is_user_admin()) {
						if (isset($wp_filter['user_admin_notices'])) {
							unset($wp_filter['user_admin_notices']);
						}
					} elseif (isset($wp_filter['admin_notices'])) {
						unset($wp_filter['admin_notices']);
					}

					if (isset($wp_filter['all_admin_notices'])) {
						unset($wp_filter['all_admin_notices']);
					}
				}
			);
		}
	}

	public function enqueue_static() {
		if (! function_exists('get_plugin_data')) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		$data = get_plugin_data(BLOCKSY_DB_MIGRATION__FILE__);

		$dependencies = [
			'underscore',
			'wp-util',
			'react',
			'react-dom',
			'wp-element',
			'wp-date',
			'wp-i18n',
		];

		wp_enqueue_script(
			'blocksy-migration-scripts',
			BLOCKSY_DB_MIGRATION_URL . '/static/bundle/dashboard.js',
			$dependencies,
			$data['Version'],
            false
		);

		wp_localize_script(
			'blocksy-migration-scripts',
			'ctMigrationLocalization',
			[
				'ajax_url' => admin_url('admin-ajax.php')
			]
		);

		wp_enqueue_style(
			'blocksy-migration-styles',
			BLOCKSY_DB_MIGRATION_URL . '/static/bundle/dashboard.min.css',
			[],
			$data['Version']
		);
	}

	public function is_dashboard_page() {
		return isset($_GET['page']) && 'blocksy-migration' === $_GET['page'];
	}

	public function render_page() {
		?>
		<div class="blocksy-migration-wrapper">

			<div class="ct-migrator-info">
				<span class="ct-migrator-info-icon">
					<svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
						<path d="M22.3,1.7H1.7C0.8,1.7,0,2.5,0,3.4v17.1c0,0.9,0.8,1.7,1.7,1.7h20.6c0.9,0,1.7-0.8,1.7-1.7V3.4C24,2.5,23.2,1.7,22.3,1.7zM22.3,20.6H8.6v-5.1H6.9v5.1H1.7v-7.7h13.9l-3.1,3.1l1.2,1.2l5.1-5.1l-5.1-5.1l-1.2,1.2l3.1,3.1H1.7V3.4h5.1v5.1h1.7V3.4h13.7V20.6z"/>
					</svg>
				</span>

				<span class="ct-migrator-info-text">
					<h3>Blocksy Migrator</h3>

					<p>
						This tool will help those setups where the initial migrator process (from the theme) didn't work well after updating to version 2.
					</p>
				</span>
			</div>

			<div class="ct-table-container">
				<h4>Step 1: Scan database</h4>
				<button class="button button-primary">
					Start to scan the database
				</button>
			</div>

			<div class="ct-buttons-group">
				<button class="button" disabled>
					Export data for customer support
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Plugin constructor.
	 *
	 * Initializing Blocksy plugin.
	 *
	 * @access private
	 */
	private function __construct() {
		$this->early_init();

		add_action('init', [$this, 'init'], 0);
	}
}

Plugin::instance();

