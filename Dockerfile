FROM php:7.3.27-fpm-buster

RUN apt-get update &&\
    apt-get install -y wget dumb-init gearman-job-server libgearman-dev gearman-tools procps &&\
    apt-get purge -y --auto-remove &&\
    pecl install -o -f gearman &&  rm -rf /tmp/pear &&  docker-php-ext-enable gearman &&\
    wget https://github.com/bakins/gearman-exporter/releases/download/v0.5.0/gearman-exporter.linux.amd64 &&\
    mv gearman-exporter.linux.amd64 /usr/local/bin/gearman-exporter &&\
    chmod +x /usr/local/bin/gearman-exporter

RUN wget https://www.php.net/distributions/php-7.3.27.tar.gz &&\
    tar -xvf php-7.3.27.tar.gz &&\
    cd php-7.3.27/ext/pcntl/ &&\
    phpize &&\
    ./configure &&\
    make &&\
    cp modules/pcntl.so /usr/local/lib/php/extensions/no-debug-non-zts-20180731/ &&\
    echo "extension=pcntl.so" > /usr/local/etc/php/conf.d/docker-php-ext-pcntl.ini 

COPY . /app
WORKDIR /app
RUN chmod +x entrypoint.sh && chmod +x entrypoint-worker.sh 

ENTRYPOINT ["/usr/bin/dumb-init", "--", "./entrypoint.sh"]