sociableCore
============

Ultra lightweight flexible full stack framework wrote in PHP5 (5.4)

Demo project here http://dev.nbonnici.info/

Features




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
