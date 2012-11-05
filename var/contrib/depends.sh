#!/bin/bash
SRC_DIR=/home/tristan/coding/github/
SRC_ZMQ3=git://github.com/zeromq/zeromq3-x.git
SRC_PHP_ZMQ=git://github.com/mkoppanen/php-zmq.git
SRC_PHP_REDIS=git://github.com/nicolasff/phpredis.git

BIN_PHPIZE=/usr/local/bin/phpize
BIN_PHP_CONFIG=/usr/local/bin/php-config

CLR_BLUE="\e[0;34m"
CLR_DEFAULT="\e[0;0m"

function output() {
    echo -e "${CLR_BLUE}${*}${SRC_DIR}${CLR_DEFAULT}"
}

output "init the resource download dir: "
[[ ! -d $SRC_DIR ]] && mkdir -p $SRC_DIR

output "install zeromq libraries"
cd $SRC_DIR
if [[ -d "zeromq3-x" ]]; then
    cd zeromq3-x
    git add .
    git checkout -f
    git pull
else
    git clone $SRC_ZMQ3 zeromq3-x
    cd zeromq3-x
fi
./autogen.sh && ./configure && make && make install

output "install php zmq module"
cd $SRC_DIR
if [[ -d "php-zmq" ]]; then
    cd php-zmq
    git add .
    git checkout -f
    git pull
else
    git clone $SRC_PHP_ZMQ php-zmq
    cd php-zmq
fi
${BIN_PHPIZE} && ./configure --with-php-config=${BIN_PHP_CONFIG} && make && make install

output "install php redis module"
cd $SRC_DIR
if [[ -d "phpredis" ]]; then
    cd phpredis
    git add .
    git checkout -f
    git pull
else
    git clone $SRC_PHP_REDIS phpredis
    cd phpredis
fi
${BIN_PHPIZE} && ./configure --with-php-config=${BIN_PHP_CONFIG} && make && make install
