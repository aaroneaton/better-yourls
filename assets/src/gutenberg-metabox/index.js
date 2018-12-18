import React, { Fragment } from 'react';

// Get the components to add custom menu items and sidebars
const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;

// Export the plugin name
export const name = 'starter-kit-plugin';

// Create a basic plugin placeholder with
// more menu item and a custom sidebar
export const settings = {

	// The plugin icon
	icon: 'smiley',

	// This function returns the component to render
	render() {
		return (
			<Fragment>
				<PluginSidebarMoreMenuItem
					target="example-plugin"
				>
					Example Plugin
				</PluginSidebarMoreMenuItem>
				<PluginSidebar
					name="example-plugin"
					title="Example Plugin"
				>
					Content of the example plugin sidebar
				</PluginSidebar>
			</Fragment>
		)
	},

};