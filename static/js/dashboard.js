import { createElement, render } from '@wordpress/element';
import Dashboard from './components/Dashboard';

document.addEventListener('DOMContentLoaded', () => {
	if (document.querySelector('.blocksy-migration-wrapper')) {
		render(
			<Dashboard />,
			document.querySelector('.blocksy-migration-wrapper')
		);
	}
});
