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