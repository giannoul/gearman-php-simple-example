#!/bin/bash
gearmand --log-file=stderr --verbose=DEBUG --threads=1 --coredump &
gearman-exporter &
php reverse_worker.php 