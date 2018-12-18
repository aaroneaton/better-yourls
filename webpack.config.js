const merge             = require( 'webpack-merge' );
const ExtractTextPlugin = require( 'extract-text-webpack-plugin' );

module.exports = ( env ) => {
	let config = {
		mode: env,
		entry: {
			'main': './assets/src/index.js',
		},
		output: {
			path: __dirname,
			filename: 'assets/dist/[name].js',
			publicPath: '/',
			libraryTarget: 'this',
		},
		resolve: { extensions: [ '.js' ] },
		module: {
			rules: [ {
				test: /\.jsx?$/,
				loader: 'babel-loader',
				exclude: /(node_modules|bower_components)/,
				options: {
					babelrc: false,
					presets: [
						[ require( 'babel-preset-env' ), {
							modules: false,
							targets: { browsers: [ 'extends @wordpress/browserslist-config' ] },
						} ],
					],
					plugins: [
						require( 'babel-plugin-transform-object-rest-spread' ),
						require( 'babel-plugin-transform-react-jsx' ),
						require( 'babel-plugin-transform-runtime' ),
						require( 'babel-plugin-lodash' ),
						[
							'@wordpress/babel-plugin-makepot',
							{
								'output': 'languages/gutenberg-widgets.pot',
							},
						],
					],
				},
			} ],
		},
		performance: { assetFilter: assetFilename => !( /\.map$/.test( assetFilename ) ) },
		externals: {
			react:                   'React',
			'react-dom':             'ReactDOM',
			wp:                      'wp',
		},
	};

	// Dev server.
	if ( env === 'development' ) {
		config = merge( config, {
			devtool: 'cheap-module-eval-source-map',
			devServer: {
				port: 8884,
				inline: true,
				hot: false,
				https: true,
				watchOptions: { ignored: /node_modules/ },
			},
			performance: {
				maxAssetSize: 1000000, // 1 mB.
				maxEntrypointSize: 1000000,
			},
		});
	}

	// CSS.
	if ( env === 'development' ) {
		config = merge( config, {
			module: {
				rules: [ {
					test: /\.css$/,
					use: [
						{ loader: 'style-loader' },
						{
							loader: 'css-loader',
							options: {
								importLoaders: 1,
								sourceMap: true,
							},
						},
					],
				} ],
			},
		});
	} else {
		config = merge( config, {
			module: {
				rules: [ {
					test: /\.css$/,
					loader: ExtractTextPlugin.extract({
						use: [
							{
								loader: 'css-loader',
								options: {
									importLoaders: 1,
									sourceMap: true,
								},
							},
						],
					}),
				} ],
			},
			plugins: [
				new ExtractTextPlugin( 'assets/dist/[name].css' ),
			],
		});
	}

	return config;
}