WebKernel
============

This framework has only 3 guidelines: Performance, security and flexibility.

#Features#

* On the fly <strong>CRUD</strong>

* Bundle architectured project structure

* Connect to any relational SGBD structure and <strong>scaffold your entities</strong>

* <strong>Out of the box CRUD and listing methods</strong> on any project Entity

* <strong>Out of the box REST API

* <strong>Full scaffolding</strong> of your custom bundles (models, controllers, forms, views and also translations)

* <strong>Autocast every SGBD data types to PHP</strong> and auto validate data integrity of your objects
    attributes on CRUD actions
    
* Flexible <strong>ACL managment for users and groups</strong>

* <strong>Command line tool</strong> to install or update project, libraries and bundles or also clear render
    engine cache (and other developer's tools)
    
* <strong>SMVC</strong> pattern, bundles enabled (bundle/controller/action with services onto a container layer).
* Yaml and ini static config format

* <strong>PSR</strong> compliant

* Use <strong>composer</strong> to manage dependencies

* <strong>Lightweight fast</strong> render engine (Haanga HuHu no more officially maintained -.-)

* This framework can render pages that fetch a hundred entities under 0.002 seconds

* Each component is unit tested

#Dependancy#

Curl, Git & composer See https://getcomposer.org/ for more infos, you need to install composer on your global binaries path.

Memcache
To install memcache support for PHP5 or PHP7.

On Linux you need to install those packages: memcached git php5-memcache php-pear build-essential pecl Then run pecl install memcache && echo "extension=memcache.so" | sudo tee /etc/php5/conf.d/memcache.ini

Note that the path for your php installation may be different on your distribution

#Installation#

Clone this skeleton web application https://github.com/nicolasbonnici/Skeleton.git then just run ./app/bin/console and choose install.

mkdir myproject
git clone git@github.com:nicolasbonnici/skeleton.git ./myproject/ && ./myproject/app/bin/console

Just choose "install" then "deploy assets".

Demo project here http://dev.nbonnici.info/

#Warning#

Do not use this framework on a production environment, the stable release fully tested with the PHPUnit Framework is currently at work.
