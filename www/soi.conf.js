
'use strict';

// 配置线上路径
//soi
//  .addRule(/merchant\/img\/.*\.png$/, {
//    to: 'static/images/'
//  })
//  .addRule(/merchant\/(.*)\/.*\.js$/, {
//    to: 'static/js/'
//  });

const TPLLoader = require('et-plugin-tplloader').TPLLoader;
const SimpleTPLCompiler = require('et-plugin-tplloader').TPLCompiler;
soi.addCompiler('TPL', SimpleTPLCompiler);

// 资源表中包含的资源类型
soi.config.set('types', ['JS', 'CSS', 'TPL']);

soi.release.task('dev',
    {
      dir: './',
      mapTo: './config/resource.json',
      domain: '',
      scandirs: ['src'],
      loaders: [
        new soi.Loaders.CSSLoader(),
        new soi.Loaders.JSLoader(),
        new TPLLoader()
      ],
      pack: {
        '/dist/static/pkg/build.css': ['src/static/css/*.css']
      }
    })
    .addRule(/src\/page\/.*\.tpl/, {
      to : '/template/page/'
    })
    .addRule(/src\/widget\/.*\.tpl/, {
      to : '/template/widget/'
    })
    .addRule(/src\/widget\/.*\.js/, {
      to : '/template/static/widget/'
    })
    // .addRule(/src\/static\/.*/, {
    //   to : '/dist/static/'
    // })
    .addRule(/src\/static\/(.*)\/.*/, {
      to : '/dist/static/$1/'
    })
    .use('wrapper', {
      define: '__d'
    })
    .use('css')
    .use('less')
    .use('messid', {
      ext: ['JS', 'CSS']
    })
    .use('uglify')
    .use('hash', {
      noname: false,
      algorithm: 'sha1'
    })
    .use('packager', {
      noname: false,
      algorithm: 'sha1'
    });