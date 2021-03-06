/**
 * Boilerplate Gruntfile.js
 */
module.exports = function(grunt) {

    require('time-grunt')(grunt);
    require('load-grunt-tasks')(grunt);

    /**
     * Setup some basic variables to use later
     */
    var pkg = grunt.file.readJSON('package.json');
    var slug = process.cwd().substr(process.cwd().lastIndexOf('/') + 1);
    var remote = 'git@github.com:PageLines-Team/' + slug + '.git'
    var build_msg = grunt.option('message') || ''

    /**
     * Main Grunt config start
     */
    grunt.initConfig({

      // clean task, an array of folders to clean
      clean: ['dist','src'],
      // watch task, in this case we just watch one file build.less and when it changes run less compiler


      // copy task, copy all files into a dist folder.
      // uses pkg.copyIgnores variable which is an array of files and folders to ignore.
      // They are set in package.json folder
      copy: {
        build: {
          files: [ {
            expand: true,
            src: [ '**', pkg.copyIgnores ],
            dest: 'dist/',
            filter: 'isFile'
          } ]
        }
      },
      // build task, pushes contents of dist folder to remote 'build' branch.
      buildcontrol: {
        options: {
          dir: 'dist',
          commit: true,
          push: true,
          message: build_msg + ' [Built from commit "%sourceCommit%"]'
        },
        production: {
          options: {
            remote: remote,
            branch: 'build'
          }
        },
        staging: {
          options: {
            remote: remote,
            branch: 'staging'
          }
        }
      }
    });
    // Default grunt task,
    grunt.registerTask( 'default', [ 'clean'] );
    // grunt build task for production
    grunt.registerTask( 'build', [ 'clean', 'copy:build', 'buildcontrol:production'] );
    // grunt build task for staging
    grunt.registerTask( 'test', [ 'clean', 'copy:build'] );
}
