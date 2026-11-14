const fs = require('fs');
const path = require('path');
const webpack = require('webpack');
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const CopyPlugin = require("copy-webpack-plugin");
const ImageMinimizerPlugin = require("image-minimizer-webpack-plugin");
const SVGSpritemapPlugin = require('svg-spritemap-webpack-plugin');
const cheerio = require('cheerio');
const chokidar = require('chokidar');

const jpgQuality = { quality: [70, 80] };
const pngQuality = { quality: [0.7, 0.8] };
const gifQuality = { optimizationLevel: 3 } // Level 1: Fast compression but less optimization. Level 2: Default optimization level, balances quality and speed. Level 3: Highest optimization, better quality but slower.

// Function to process an SVG file
async function processSVG(filePath) {
    try {
        // Read the contents of the SVG file
        const svgData = await fs.promises.readFile(filePath, 'utf8');

        // Load the SVG into cheerio
        const $ = cheerio.load(svgData, {
            xmlMode: true,
            normalizeWhitespace: true,
        });

        // Find all child path elements
        $('path').each((index, element) => {
            const pathElement = $(element);

            // Get the current values of fill and stroke attributes
            const fillValue = pathElement.attr('fill');
            const strokeValue = pathElement.attr('stroke');

            // Replace fill and stroke attributes with 'currentColor' if they exist and are not 'none'
            if (fillValue !== undefined && fillValue !== 'none') {
                pathElement.attr('fill', 'currentColor');
            }
            if (strokeValue !== undefined && strokeValue !== 'none') {
                pathElement.attr('stroke', 'currentColor');
            }
        });

        // Convert the updated SVG code back to a string
        const modifiedSVG = $.xml();

        // Save the modified SVG back to the file
        await fs.promises.writeFile(filePath, modifiedSVG);

        console.log(`SVG file ${filePath} processed successfully.`);
    } catch (error) {
        console.error(`An error occurred while processing the SVG file ${filePath}:`, error);
    }
}

// Main function to process all SVG files in the specified directory
async function processSVGFilesInDirectory(directoryPath) {
    try {
        // Get a list of files in the directory
        const files = await fs.promises.readdir(directoryPath);

        // Filter only SVG files
        const svgFiles = files.filter(file => file.toLowerCase().endsWith('.svg'));

        // Process each SVG file
        for (const file of svgFiles) {
            const filePath = path.join(directoryPath, file);
            await processSVG(filePath);
        }
    } catch (error) {
        console.error('An error occurred:', error);
    }
}

// Paths to folders and the final file
const baseDir = path.join(__dirname, 'src', 'styles');
const adminScssPath = path.join(baseDir, 'admin.scss');
const importFolders = ['element', 'section']; // List of folders to import
function generateAdminScss() {
    try {
        let imports = '';

        // Iterate through all folders
        importFolders.forEach(folder => {
            const folderPath = path.join(baseDir, folder);
            if (fs.existsSync(folderPath)) {
                const files = fs.readdirSync(folderPath)
                    .filter(file => file.endsWith('.scss'))
                    .map(file => file.replace('.scss', ''));

                // Add file imports
                const folderImports = files
                    .map(file => `\t@import '${folder}/${file}';`)
                    .join('\n');

                imports += `/* ${folder.toUpperCase()} FILES */\n${folderImports}\n\n`;
            }
        });

        // Final content for admin.scss
        const adminScssContent = `
.wp-block-post-content .acf-block-preview {
${imports.trim()}
}
`;

        // Write the content to admin.scss
        fs.writeFileSync(adminScssPath, adminScssContent.trim());
        console.log('admin.scss updated successfully!');
    } catch (error) {
        console.error('Error generating admin.scss:', error);
    }
}
function watchFolders() {
    const foldersToWatch = importFolders.map(folder => path.join(baseDir, folder));

    chokidar.watch(foldersToWatch).on('all', (event, filePath) => {
        console.log(`File ${filePath} was changed (${event}). Regenerating admin.scss...`);
        generateAdminScss();
    });
}
generateAdminScss(); // Generate on the first run
watchFolders(); // Monitor changes


// Specify the path to the directory containing your SVG files
const directoryPath = './src/icons/';

// Process all SVG files in the specified directory
processSVGFilesInDirectory(directoryPath);


function fileExists(filePath) {
    try {
        return fs.existsSync(filePath);
    } catch(err) {
        return false;
    }
}

// Search all files in dir
function getFilesFromDir(dir, fileTypes) {
    let filesToReturn = [];

    function walkDir(currentPath, basePath) {
        const files = fs.readdirSync(currentPath);
        for (let i in files) {
            const curFile = path.join(currentPath, files[i]);
            if (fs.statSync(curFile).isFile() && fileTypes.includes(path.extname(curFile)) && !path.basename(curFile).startsWith('_')) {
                filesToReturn.push(curFile.replace(basePath + path.sep, '').replace(path.sep, '-'));
            } else if (fs.statSync(curFile).isDirectory()) {
                walkDir(curFile, basePath);
            }
        }
    }

    walkDir(dir, dir);
    return filesToReturn;
}

const directoryPathStyles = path.join(__dirname, 'src', 'styles');
const directoryPathScripts = path.join(__dirname, 'src', 'scripts');

const scssFiles = getFilesFromDir(directoryPathStyles, ['.scss']);
const jsFiles = getFilesFromDir(directoryPathScripts, ['.js']);

const EntryCss = scssFiles.reduce((entries, file) => {
    const entryKey = file.replace('.scss', '');
    entries[entryKey] = './src/styles/' + file.replace(/-/g, '/');
    return entries;
}, {});
const EntryJs = jsFiles.reduce((entries, file) => {
    const entryKey = file.replace('.js', '');
    entries[entryKey] = './src/scripts/' + file.replace(/-/g, '/');
    return entries;
}, {});

//Change 'webpack' name in the proxy settings to your folder name
const settings = {
	host: 'localhost',
	port: 5500,
	proxy: '/wp-content/themes/general/frontend/'
};

module.exports = (env) => [
	{
		mode: 'development',
		entry: EntryCss,
		output: {
			filename: './node_modules/[name].log',
			path: path.resolve(__dirname),
			// assetModuleFilename: "[name][ext]",
		},
		module: {
			rules: [
				{
					test: /\.(css|sass|scss)$/,
					use: [
						MiniCssExtractPlugin.loader,
						{ loader: "css-loader", options: { sourceMap: true, url: false } },
						{ loader: "sass-loader", options: { sourceMap: true} },
						"postcss-loader",
					],
					sideEffects: true
				},
				{
					test: /\.(woff(2)?|ttf|eot)$/,
					type: 'asset/resource',
					generator: {
						filename: './assets/fonts/[name][ext][query]',
					}
				},
			],
		},
		plugins: [
			new SVGSpritemapPlugin('./src/icons/**/*.svg', {
				output: {
					filename: './assets/icons/sprite.svg',
					svg: {
						attributes: {
							fill: 'currentColor',
							stroke: 'currentColor',
						}
					}
				},
				sprite: {
					prefix: false,
				},

			}),
			new CleanWebpackPlugin({
				cleanOnceBeforeBuildPatterns: ['./assets/css/*']
			}),
			new MiniCssExtractPlugin({
				filename: './assets/css/[name].css',
			}),
			new MiniCssExtractPlugin({
				filename: './assets/css/[name].min.css',
			}),
			new BrowserSyncPlugin({
				host: settings.host,
				port: settings.port,
				proxy: settings.proxy,
				files: ['./**/*', '!./node_modules', '!./package.json'],
				notify: false,
				injectCss: true,
				reloadDelay: 0
			}),
			new webpack.SourceMapDevToolPlugin({
				filename: '[file].map[query]',
				exclude: /\.min\.css$/i
			}),
			new CopyPlugin({
				patterns: [
					{
						from: 'src/img/*',
						to: 'assets/img/[name][ext]',
						filter: async (resourcePath) => {
							const outputPath = path.join(__dirname, 'dest', path.basename(resourcePath));
							return !fileExists(outputPath);
						},
					},
				],
			}),
		],
		optimization: {
			minimizer: [
				new CssMinimizerPlugin({
					minimizerOptions: {
						preset: ['default', { discardComments: { removeAll: true }, mergeRules: true }],
					},
					include: /\.min\.css$/i,
					exclude: './assets/css/[name].css',
				}),
				new ImageMinimizerPlugin({
					loader: true,
					deleteOriginalAssets: false,
					minimizer: {
						implementation: ImageMinimizerPlugin.imageminMinify,
						options: {
							plugins: [
								["imagemin-gifsicle", gifQuality],
								["imagemin-mozjpeg", jpgQuality],
								["imagemin-pngquant", pngQuality],
								["imagemin-svgo", { }]
							],
						},
					},
					generator: [
						{
							type: "asset",
							implementation: ImageMinimizerPlugin.imageminGenerate,
							options: {
								plugins: [
									"imagemin-gifsicle",
									"imagemin-mozjpeg",
									"imagemin-pngquant",
									"imagemin-svgo",
								],
							},
						},
					],
				}),
			],

			minimize: true,
		},
	},
	{
		entry: EntryJs,
		output: {
			filename: './assets/js/[name].js',
			path: path.resolve(__dirname),
			assetModuleFilename: "./assets/img/[name][ext]",
		},
		devtool: false,
		plugins: [
			new CleanWebpackPlugin({
				cleanOnceBeforeBuildPatterns: ['./assets/js/*']
			}),
		],
		module: {
			rules: [
				{
					test: /\.js$/,
					exclude: /(node_modules)/,
					use: {
						loader: 'babel-loader',
						options: {
							presets: ['@babel/preset-env']
						}
					}
				},
			]
		}
	},
	{
		entry: EntryJs,
		output: {
			filename: './assets/js/[name].min.js',
			path: path.resolve(__dirname),
			assetModuleFilename: "./assets/img/[name][ext]",
		},
		devtool: false,
		module: {
			rules: [
				{
					test: /\.js$/,
					exclude: /(node_modules)/,
					use: {
						loader: 'babel-loader',
						options: {
							presets: ['@babel/preset-env'],
						}
					}
				},
			]
		},
		optimization: {
			minimizer: [
				new TerserPlugin({
					terserOptions: {
						format: {
							comments: false,
						},
						compress: {
							drop_console: true,
						},
					},
					extractComments: false,
				}),
			],
			minimize: true,
		}
	}

];


//Update dependency
// npm i -g npm-check-updates
// ncu -u
// npm install
