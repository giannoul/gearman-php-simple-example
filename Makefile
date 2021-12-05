.PHONY: build-and-tag-images
build-and-tag-images: 
	docker build -t gearman-pod-experiment . &&\
	docker build -f Dockerfile.memcached -t memcached-pod-experiment . 

.PHONY: run-container
run-container: 
	docker run --rm gearman-pod-experiment

.PHONY: run-test-client-command
run-test-client-command: 
	docker exec -ti `docker ps | grep gearman-pod-experiment | awk '{print $$1}'` /bin/bash -c "php reverse_client_task.php"

.PHONY: inspect-running-container
inspect-running-container: 
	docker exec -ti `docker ps | grep gearman-pod-experiment | awk '{print $$1}'` /bin/bash

.PHONY: start-container-for-inspection
start-container-for-inspection: 
	docker run --rm -ti --entrypoint /bin/bash gearman-pod-experiment

.PHONY: docker-compose-start
docker-compose-start: 
	docker-compose up --detach

.PHONY: docker-compose-logs
docker-compose-logs: 
	docker-compose logs -f 

.PHONY: docker-compose-stop
docker-compose-stop: 
	docker-compose stop && docker-compose down