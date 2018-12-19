import React, { Fragment } from 'react';
import { TextControl } from '@wordpress/components';
import { withState } from '@wordpress/compose';

// Get the components to add custom menu items and sidebars
const { PluginPrePublishPanel } = wp.editPost;

// Export the plugin name
export const name = 'better-yourls';

const YourlsTextControl = withState({
		value: '',
})( ( { value, setState } ) => (
	<TextControl
		label={'YOURLs Keyword'}
		value={value}
		onChange={ ( value ) => setState( { value } ) }
	/>
));

export const settings = {
	render() {
		return (
			<PluginPrePublishPanel
				className="better-yourls-pre-publish-panel"
				title={'Better YOURLs'}
				initialOpen={true}
			>
				<YourlsTextControl/>
			</PluginPrePublishPanel>
		)
	}
}

