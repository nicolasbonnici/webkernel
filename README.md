sociableCore
============

Ultra lightweight flexible full stack framework wrote in PHP5 (5.4)

Demo project here http://dev.nbonnici.info/

Features

* On the fly <strong>ORM</strong>

* Bundle architectured project structure

* Connect to any relational SGBD structure and <strong>scaffold your entities</strong>

* <strong>Out of the box CRUD and listing methods</strong> on any project Entity

* <strong>Full scaffolding</strong> of your custom bundles (models, controllers, forms, views and also translations)

* <strong>Autocast every SGBD data types to PHP</strong> and auto validate data integrity of your objects
    attributes on CRUD actions
    
* Flexible <strong>ACL managment for users and groups</strong>

* <strong>Command line tool</strong> to install or update project, libraries and bundles or also clear render
    engine cache (and other developer's tools)
    
* <strong>MVC</strong> pattern, bundles enabled (bundle/controller/action) with a app namespace common couch to each layers

* Yaml and ini static config format

* <strong>PSR</strong> compliant

* Use <strong>composer</strong> to manage bundles

* <strong>Full flexible hybrid HTML5 UX</strong> based on Twitter Bootstrap and jQuery 1.10+

* Lightweight <strong>fast render engine</strong> (Haanga)

* This framework can render pages that fetch a hundred entities under 0.002 seconds

Dependancy

Curl, Git & composer See https://getcomposer.org/ for more infos, you need to install composer on your global binaries path.

Memcache
To install memcache support for PHP5 on Linux you need to install those packages: memcached git php5-memcache php-pear build-essential pecl Then run pecl install memcache && echo "extension=memcache.so" | sudo tee /etc/php5/conf.d/memcache.ini

Note that the path for your php installation may be different on your distribution


Installation

Clone this skeleton web application https://github.com/nicolasbonnici/Skeleton.git then just run ./app/bin/console and choose install.

mkdir myproject
git clone git@github.com:nicolasbonnici/skeleton.git ./myproject/ && ./myproject/app/bin/console

Then just choose "install" and "deploy assets" 
