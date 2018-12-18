const { registerPlugin } = wp.plugins;

import * as gutenbergMetabox from './gutenberg-metabox';

const plugins = [
	gutenbergMetabox,
];

plugins.forEach( ( { name, settings } ) => {
	registerPlugin( name, settings );
} );