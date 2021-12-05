# Gearman simple example

## How to

1. build the docker image:
```
make build-and-tag-image
``` 

2. spin up the conainers:
```
docker-compose up
```

3. connect to the client container (in a new window):
```
docker exec -ti gearman-client bash
``` 

4. in the client container run the command:
```
php reverse_client_task.php 
```

## Some useful commands

* find the `php.ini`:
```
make start-container-for-inspection
php -i | grep .ini
```

## repcached

Resources:
* https://github.com/yokogawa-k/repcached
* https://www.howtoforge.com/how-to-install-repcached-memcached-replication-for-high-availability-over-2-nodes-on-ubuntu-11.04
* https://www.globo.tech/learning-center/memcached-repcached-patch-high-availability-ubuntu-14/
* https://www.mynotes.kr/memcached-replication-failover/
* https://developpaper.com/repcached-of-memcached-dual-master-model/
* https://serverfault.com/questions/249728/repcached-didnt-work-when-replicate-with-2-servers
* https://www.programmerall.com/article/9161129808/
* http://tutorialspots.com/how-to-use-memcached-with-replication-on-centos-4783.html
* https://www.slideshare.net/gear6memcached/implementing-high-availability-services-for-memcached-1911077

#### Tests
* we can store keys on both instances 

```
$ docker exec -ti gearman-server /bin/bash

root@84158ce325e9:/app# apt-get install -y telnet
root@84158ce325e9:/app# telnet memcached-1 11211
Trying 192.168.96.4...
Connected to memcached-1.
Escape character is '^]'.
set foo 0 0 3
bar
STORED
quit

root@84158ce325e9:/app# telnet memcached-2 11211
Trying 192.168.96.5...
Connected to memcached-2.
Escape character is '^]'.
get foo
VALUE foo 0 3
bar
END
quit

root@84158ce325e9:/app# telnet memcached-2 11211
Trying 192.168.240.2...
Connected to memcached-2.
Escape character is '^]'.
set tutorialspoint 0 900 9
memcached
STORED
quit

root@84158ce325e9:/app# telnet memcached-1 11211
Trying 192.168.240.5...
Connected to memcached-1.
Escape character is '^]'.
get tutorialspoint
VALUE tutorialspoint 0 9
memcached
END
quit
```
* if we resrt one instance the keys will be synchronized again

```
docker restart memcached-1

root@84158ce325e9:/app# telnet memcached-1 11211
Trying 192.168.240.5...
Connected to memcached-1.
Escape character is '^]'.
get foo
VALUE foo 0 3
bar
END
get tutorialspoint
VALUE tutorialspoint 0 9
memcached
END
quit
```

## Gearman worker graceful shutdown

As per the following links:
* https://gist.github.com/juneym/6558745
* https://github.com/mmoreram/GearmanBundle/issues/134
* https://dpb587.me/post/2013/01/14/terminating-gearman-workers-in-php/

the graceful shutdown of gearman workers is perceived like "the worker should stop receiving more jobs once it gets a termination signal and exit once the current jobs finish". This is done using the existing code. 