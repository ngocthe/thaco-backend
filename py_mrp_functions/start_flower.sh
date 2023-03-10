#!/bin/bash

celery -A celery_workers.celery flower --debug=False --address=0.0.0.0 --port=3555 --logfile=log/flower.log