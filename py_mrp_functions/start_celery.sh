#!/bin/bash

celery -A celery_workers.celery worker --loglevel=info --max-tasks-per-child=10 --concurrency=2 --logfile=log/celery.log -E
