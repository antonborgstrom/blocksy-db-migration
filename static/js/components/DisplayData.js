import { createElement, useState, useEffect } from '@wordpress/element';

import saveAs from '../utils/save-as';

let replacements = [
	{
		old: 'paletteColor',
		new: 'theme-palette-color-',
	},

	{
		old: 'var(--color)',
		new: 'var(--theme-text-color)',
	},

	{
		old: 'buttonInitialColor',
		new: 'theme-button-background-initial-color',
	},

	{
		old: '--fontFamily',
		new: '--theme-font-family',
	},

	{
		old: '--linkInitialColor',
		new: '--theme-link-initial-color',
	},

	{
		old: '--container-width',
		new: '--theme-container-width',
	},

	{
		old: '--normal-container-max-width',
		new: '--theme-normal-container-max-width',
	},

	{
		old: '--narrow-container-max-width',
		new: '--theme-narrow-container-max-width',
	},

	{
		old: '--buttonFontFamily',
		new: '--theme-button-font-family',
	},

	{
		old: '--buttonFontSize',
		new: '--theme-button-font-size',
	},

	{
		old: '--buttonFontWeight',
		new: '--theme-button-font-weight',
	},

	{
		old: '--buttonFontStyle',
		new: '--theme-button-font-style',
	},

	{
		old: '--buttonLineHeight',
		new: '--theme-button-line-height',
	},

	{
		old: '--buttonLetterSpacing',
		new: '--theme-button-letter-spacing',
	},

	{
		old: '--buttonTextTransform',
		new: '--theme-button-text-transform',
	},

	{
		old: '--buttonTextDecoration',
		new: '--theme-button-text-decoration',
	},

	{
		old: '--buttonTextInitialColor',
		new: '--theme-button-text-initial-color',
	},

	{
		old: '--button-border',
		new: '--theme-button-border',
	},

	{
		old: '--buttonInitialColor',
		new: '--theme-button-background-initial-color',
	},

	{
		old: '--buttonMinHeight',
		new: '--theme-button-min-height',
	},

	{
		old: '--buttonBorderRadius',
		new: '--theme-button-border-radius',
	},

	{
		old: '--button-padding',
		new: '--theme-button-padding',
	},

	{
		old: '--button-border-hover-color',
		new: '--theme-button-border-hover-color',
	},

	{
		old: '--buttonTextHoverColor',
		new: '--theme-button-text-hover-color',
	},

	{
		old: '--buttonHoverColor',
		new: '--theme-button-background-hover-color',
	},
];

const DisplayData = ({ data }) => {
	console.log('here', { data });

	const [migrationStatus, setMigrationStatus] = useState(null);
	const [isMigrating, setIsMigrating] = useState(
		// false | true | 'success'
		false
	);

	const [isRegenerating, setIsRegenerating] = useState(
		// false | true | 'success'
		false
	);

	const [tablesData, setTablesData] = useState(null);

	let allTables = data.all_tables.filter(
		(table) =>
			table.indexOf('woocommerce_') === -1 &&
			table.indexOf('wc_') === -1 &&
			table.indexOf('nextend2_') === -1 &&
			table.indexOf('rank_math_') === -1 &&
			table.indexOf('trp_') === -1 &&
			table.indexOf('_wf') === -1 &&
			table.indexOf('yoast') === -1 &&
			table.indexOf('wpforms') === -1 &&
			table.indexOf('_e_') === -1 &&
			table.indexOf('_em_') === -1 &&
			table.indexOf('wpmailsmtp') === -1 &&
			table.indexOf('mystickyelement_') === -1 &&
			table.indexOf('fluentform_') === -1 &&
			table.indexOf('gglcptch_') === -1 &&
			table.indexOf('tutor_') === -1 &&
			table.indexOf('wdp_') === -1 &&
			table.indexOf('tec_') === -1 &&
			table.indexOf('bwfan_automation') === -1 &&
			table.indexOf('bwf_') === -1 &&
			table.indexOf('glsr_') === -1 &&
			table.indexOf('actionscheduler_') === -1
	);

	// allTables = [`${data.prefix}options`];

	const fetchTablesData = async () => {
		setTablesData({
			totalTables: allTables.length,
			loadedTables: 0,

			tables: null,
			total: 0,
		});

		let finalData = {};

		for (const tableName of allTables) {
			let tableTotal = 0;

			for (const replacement of replacements) {
				const response = await fetch(
					`${wp.ajax.settings.url}?action=get_table_status&table=${tableName}&old=${replacement.old}&new=${replacement.new}`,

					{
						method: 'POST',
					}
				);

				const data = await response.json();

				tableTotal += data.data.result.total;
			}

			setTablesData((tablesData) => ({
				...tablesData,
				loadedTables: tablesData.loadedTables + 1,
			}));

			finalData[tableName] = {
				total: tableTotal,
			};
		}

		setTablesData({
			tables: finalData,
			total: Object.values(finalData).reduce(
				(acc, curr) => acc + curr.total,
				0
			),
		});
	};

	useEffect(() => {
		// fetchTablesData();
	}, []);

	console.log('here', { allTables, tablesData });

	return (
		<>
			<div className="ct-table-container">
				{!tablesData && (
					<>
						<h4>Step 1: Scan database</h4>

						<button
							onClick={(e) => {
								e.preventDefault();
								fetchTablesData();
							}}
							className="button button-primary">
							Start to scan the database
						</button>
					</>
				)}

				{null && JSON.stringify(data, null, 2)}

				{tablesData && !tablesData.tables && (
					<div>
						<h4>
							Step 1: Scanning database... (
							{tablesData.loadedTables} of{' '}
							{tablesData.totalTables} tables)
						</h4>

						<p>
							Please wait, this process may take a while (this
							also depends on your server resources) so don't
							close this tab/window untill the migration process
							will end.
						</p>
					</div>
				)}

				{((tablesData && tablesData.tables && tablesData.total === 0) ||
					isMigrating === 'success') && (
					<div>
						<h4>
							Congratulations! Your site is properly migrated to
							Blocksy 2 🤩
						</h4>
						<p>
							In case you will have any other questions, please
							submit a support ticket{' '}
							<a
								href="https://creativethemes.com/blocksy/support/"
								target="_blank">
								here
							</a>{' '}
							and we will assist you.
						</p>
					</div>
				)}

				{tablesData &&
					tablesData.tables &&
					tablesData.total > 0 &&
					isMigrating !== 'success' && (
						<div>
							<h4>
								Step 2: We detected some tables that were not
								migrated properly:
							</h4>

							<ul>
								{allTables
									.sort()
									.filter(
										(table) =>
											tablesData.tables[table].total > 0
									)
									.map((tableName) => {
										return (
											<li key={tableName}>
												{tableName} -{' '}
												{
													tablesData.tables[tableName]
														.total
												}{' '}
												occurences
												{migrationStatus &&
													migrationStatus[
														tableName
													] &&
													migrationStatus[tableName]
														.status && (
														<span>
															{` - Status: ${migrationStatus[
																tableName
															].status.toUpperCase()}`}
														</span>
													)}
											</li>
										);
									})}
							</ul>

							<button
								className="button button-primary"
								disabled={isMigrating}
								onClick={async (e) => {
									e.preventDefault();

									setIsMigrating(true);

									let tablesForMigration = allTables
										.sort()
										.filter(
											(table) =>
												tablesData.tables[table].total >
												0
										);

									setMigrationStatus(
										tablesForMigration.reduce(
											(acc, curr) => {
												return {
													...acc,
													[curr]: {
														// idle | running | done
														status: 'idle',
													},
												};
											},
											{}
										)
									);

									for (const tableName of tablesForMigration) {
										setMigrationStatus(
											(migrationStatus) => ({
												...migrationStatus,
												[tableName]: {
													...migrationStatus[
														tableName
													],
													status: 'running',
												},
											})
										);

										for (const replacement of replacements) {
											await fetch(
												`${wp.ajax.settings.url}?action=migrate_table&table=${tableName}&old=${replacement.old}&new=${replacement.new}`,

												{
													method: 'POST',
												}
											);
										}

										setMigrationStatus(
											(migrationStatus) => ({
												...migrationStatus,
												[tableName]: {
													...migrationStatus[
														tableName
													],
													status: 'done',
												},
											})
										);
									}

									await fetch(
										`${wp.ajax.settings.url}?action=regenerate_css`,

										{
											method: 'POST',
										}
									);

									setIsMigrating('success');
								}}>
								{isMigrating === 'success'
									? 'Done! Refresh the page to see the changes.'
									: isMigrating
									? 'Migrating...'
									: 'Migrate all tables in sequence'}
							</button>
						</div>
					)}
			</div>

			<div className="ct-buttons-group">
				<button
					className="button"
					onClick={(e) => {
						e.preventDefault();

						var blob = new Blob([JSON.stringify(data, null, 2)], {
							type: 'application/octet-stream;charset=utf-8',
						});

						saveAs(blob, `${location.host}-site-status.json`);
					}}>
					Export data for customer support
				</button>

				{null && (
					<button
						className="button"
						disabled={isRegenerating || tablesData}
						onClick={async (e) => {
							e.preventDefault();

							setIsRegenerating(true);

							await fetch(
								`${wp.ajax.settings.url}?action=regenerate_css`,

								{
									method: 'POST',
								}
							);

							setIsRegenerating(false);
						}}>
						{isMigrating === 'success'
							? 'Done! Check your site.'
							: isMigrating
							? 'Migrating...'
							: 'Re-generate CSS and clear caches'}
					</button>
				)}
			</div>
		</>
	);
};

export default DisplayData;
