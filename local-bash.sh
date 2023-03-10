#!/bin/bash

set -eu

cd `dirname $0`
ROOT_DIR=`pwd`

cd laradock

docker-compose exec --user=laradock workspace ${@:1}
