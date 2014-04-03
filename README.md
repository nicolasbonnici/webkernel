sociableCore
============

Ultra lightweight flexible full stack framework wrote in PHP5 (5.4)

Demo project here http://dev.nbonnici.info/

- On the fly ORM
- Bundle architecture (Bundle/Controller/actions)
- Connect to any relational SGBD structure and scaffold your entities
- Full scaffolding of your models, controllers, forms, views and also translations
- Out of the box CRUD and listing methods on any project Entity
- Autocast every SGBD data types to PHP and auto validate data integrity of your objects attributes on CRUD actions
- Flexible ACL managment for the CRUD actions
- Command line tool to update project, libraries or bundle and also clear client cache (and other developer's tools)
- Namespaces
- MVC pattern, modules enabled with a common couch to each layers
- Yaml and ini static config format
- PSR compliant
- Use composer to manage bundles
- Full flexible hybrid UX based on Twitter Bootstrap and jQuery 1.10+
- Lightweight render engine (Haanga), this framework can render pages that fetch a hundred objects under 0.002 secs 

Dependancy

Curl, Git & composer See https://getcomposer.org/ for more infos

Memcache To install memcache support for PHP5 on Linux you need to install those packages: memcached git php5-memcache php-pear build-essential pecl Then run pecl install memcache && echo "extension=memcache.so" | sudo tee /etc/php5/conf.d/memcache.ini

Note that the path for your php installation may be different on your distribution


Installation

Clone this skeleton web application https://github.com/nicolasbonnici/Skeleton.git then just run ./app/bin/console and choose install.
