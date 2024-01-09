import { createElement, useState, useEffect } from '@wordpress/element';

import DisplayData from './DisplayData';

const Dashboard = () => {
	const [data, setData] = useState(null);

	const fetchData = async () => {
		const response = await fetch(
			`${wp.ajax.settings.url}?action=get_site_status`,

			{
				method: 'POST',
			}
		);

		const data = await response.json();

		setData(data.data);
	};

	useEffect(() => {
		fetchData();
	}, []);

	return (
		<div>
			<div className="ct-migrator-info">
				<span className="ct-migrator-info-icon">
					<svg
						width="20"
						height="20"
						fill="currentColor"
						viewBox="0 0 24 24">
						<path d="M22.3,1.7H1.7C0.8,1.7,0,2.5,0,3.4v17.1c0,0.9,0.8,1.7,1.7,1.7h20.6c0.9,0,1.7-0.8,1.7-1.7V3.4C24,2.5,23.2,1.7,22.3,1.7zM22.3,20.6H8.6v-5.1H6.9v5.1H1.7v-7.7h13.9l-3.1,3.1l1.2,1.2l5.1-5.1l-5.1-5.1l-1.2,1.2l3.1,3.1H1.7V3.4h5.1v5.1h1.7V3.4h13.7V20.6z" />
					</svg>
				</span>

				<span className="ct-migrator-info-text">
					<h3>Blocksy Migrator</h3>

					<p>
						This tool will help those setups where the initial migrator process (from the theme) didn't work well after updating to version 2.
					</p>
				</span>
			</div>

			{!data && (
				<>
				<div className="ct-table-container">
					<h4>Step 1: Scan database</h4>
					<button
						onClick={(e) => {
							e.preventDefault();
						}}
						className="button button-primary">
						Start to scan the database
					</button>
				</div>

				<div className="ct-buttons-group">
					<button className="button" disabled>
						Export data for customer support
					</button>
				</div>
				</>
			)}
			{data && <DisplayData data={data} />}
		</div>
	);
};

export default Dashboard;
