/* jshint node:true */

module.exports = function (grunt) {

	// load all grunt tasks matching the `grunt-*` pattern
	require('load-grunt-tasks')(grunt);

	grunt.initConfig({

		pkg: grunt.file.readJSON( 'package.json' ),

		shell: {
			commit: {
				command: 'git add . --all && git commit -m "Version <%= pkg.version %>"'
			},
			tag: {
				command: 'git tag -a <%= pkg.version %> -m "Version <%= pkg.version %>"'
			}
		},

		copy: {
			build: {
				files: [
					{
						expand: true,
						cwd: '.',
						src: [
							'*',
							'!**package.json',
							'!**Gruntfile.js',
							'!**{changelog,CONTRIBUTING,README}.md**',
							'!**/node_modules/**',
							'!wp-assets',
							'!build',
							'!**/picturefill/**',
							'**/picturefill/dist/**',
						],
						dest: 'build'
					}
				]
			}
		},

		clean:{
			build: {
				src: [ 'build' ]
			}
		},

		wp_deploy: {
			deploy: {
				options: {
					plugin_slug: 'high-resoloution-images-with-srcset',
					build_dir: 'build', //relative path to your build directory
					assets_dir: 'wp-assets', //relative path to your assets directory (optional).
				},
			}
		},

	});

	// Package a new release
	grunt.registerTask( 'release', [
		'copy:build',
		'wp_deploy',
		'clean:build'
	] );

};