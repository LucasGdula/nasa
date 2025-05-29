FROM debian:bookworm

RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections
RUN echo 'deb http://deb.debian.org/debian bookworm-backports main' > /etc/apt/sources.list.d/backports.list
RUN apt-get update --fix-missing
RUN apt-get install -y apt-utils
RUN apt-get clean all -y
RUN apt-get update -y
RUN apt-get install software-properties-common -y
RUN apt-get dist-upgrade -y

RUN apt-get update --fix-missing -y
# Helper tools
#RUN apt-get install -y gcc make autoconf libc-dev pkg-config libzip-dev composer git mc

#php8 repo
RUN apt install apt-transport-https lsb-release ca-certificates software-properties-common wget -y --fix-missing
RUN apt install -y gnupg2 git unzip curl --fix-missing
RUN apt-get update --fix-missing
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/sury-php.list
RUN wget -qO - https://packages.sury.org/php/apt.gpg | apt-key add -
RUN apt update



# Apache
RUN apt-get install -y apache2
#RUN apt install php8.3-fpm -y
RUN apt install php8.3 -y

# Extensions
RUN apt install -y php8.3-bz2
RUN apt install -y php8.3-bcmath
RUN apt install -y php8.3-tidy
RUN apt install -y php8.3-dba
RUN apt install -y php8.3-imap
RUN apt install -y php8.3-readline
RUN apt install -y php8.3-xdebug
RUN apt install -y php8.3-dev
RUN apt install -y php8.3-snmp
RUN apt install -y php8.3-xml
RUN apt install -y php8.3-cgi
RUN apt install -y php8.3-enchant
RUN apt install -y php8.3-opcache
RUN apt install -y php8.3-intl
RUN apt install -y php8.3-soap
RUN apt install -y php8.3-xsl
RUN apt install -y php8.3-cli
RUN apt install -y php8.3-zip
RUN apt install -y php8.3-common
RUN apt install -y php8.3-gd
RUN apt install -y php8.3-mbstring
RUN apt install -y php8.3-curl
RUN apt install -y php8.3-mysql
RUN apt install php8.3-imagick -y
RUN apt install php-mysql -y

#RUN apt install -y php8.3-gmp
#RUN apt install -y php8.3-pspell
#RUN apt install -y php8.3-sybase
#RUN apt install -y php8.3-phpdbg
#RUN apt install -y php8.3-ldap
#RUN apt install -y php8.3-sqlite3
#RUN apt install -y php8.3-fpm
#RUN apt install -y php8.3-pgsql
#RUN apt install -y php8.3-interbase
#RUN apt install -y php8.3-odbc

#RUN apt install -y php8.3-bcmath-dbgsym
#RUN apt install -y php8.3-bz2-dbgsym
#RUN apt install -y php8.3-cgi-dbgsym
#RUN apt install -y php8.3-cli-dbgsym
#RUN apt install -y php8.3-curl-dbgsym
#RUN apt install -y php8.3-dba-dbgsym
#RUN apt install -y php8.3-enchant-dbgsym
#RUN apt install -y php8.3-fpm-dbgsym

#RUN apt install -y php8.3-interbase-dbgsym
#RUN apt install -y php8.3-snmp-dbgsym
#RUN apt install -y php8.3-xml-dbgsym
#RUN apt install -y php8.3-odbc-dbgsym
#RUN apt install -y php8.3-opcache-dbgsym
#RUN apt install -y php8.3-intl-dbgsym
#RUN apt install -y php8.3-soap-dbgsym
#RUN apt install -y php8.3-pgsql-dbgsym
#RUN apt install -y php8.3-zip-dbgsym
#RUN apt install -y php8.3-ldap-dbgsym
#RUN apt install -y php8.3-sqlite3-dbgsym
#RUN apt install -y php8.3-gd-dbgsym
#RUN apt install -y php8.3-phpdbg-dbgsym
#RUN apt install -y php8.3-mbstring-dbgsym
#RUN apt install -y php8.3-sybase-dbgsym
#RUN apt install -y php8.3-imap-dbgsym
#RUN apt install -y php8.3-readline-dbgsym
#RUN apt install -y php8.3-tidy-dbgsym
#RUN apt install -y php8.3-mysql-dbgsym
#RUN apt install -y php8.3-pspell-dbgsym
#RUN apt install -y php8.3-gmp-dbgsym

# PHP Config
RUN echo 'upload_max_filesize = 25M' >> /etc/php/8.3/apache2/php.ini
RUN echo 'post_max_size = 25M' >> /etc/php/8.3/apache2/php.ini
RUN echo 'opcache.enable=0' >> /etc/php/8.3/apache2/php.ini

# Libraries
RUN apt-get install imagemagick -y

# Tools
RUN apt-get install -y mc

# Set apache root dir
RUN sed -i 's/html/html\/public/g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's/html/html\/public/g' /etc/apache2/sites-available/default-ssl.conf
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Enable rewrite rules and PHP
RUN a2enmod rewrite
RUN a2enmod ssl
RUN a2enmod headers
#RUN ln -s /etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-enabled/000-default-ssl.conf
RUN a2ensite default-ssl

#NOTICE: a2enmod proxy_fcgi setenvif
#NOTICE: a2enconf php8.3-fpm

# NodeJS
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && apt-get install -y nodejs
RUN npm install -g npm
RUN npm install -g gulp-cli
RUN npm install -g sass

#PHPCS and PHPStan aliases
RUN echo "alias cs='vendor/bin/phpcs'" >> ~/.bashrc
RUN echo "alias stan='vendor/bin/phpstan'" >> ~/.bashrc
RUN echo "alias test='vendor/bin/phpunit --testdox'" >> ~/.bashrc

#WORKDIR '/var/www/html/'
#RUN cd composer update
#RUN cd composer dump-autoload

#composer
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer


EXPOSE 80
EXPOSE 443


CMD /usr/sbin/apache2ctl -D FOREGROUND
