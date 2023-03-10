#!/bin/bash
# export SECRET ENV
while read line; do export "$line"; done <<< "$(cat .env)"

set -e
# Run our defined exec if args empty
if [ -z "$1" ]; then
    cd /var/www/html

    role=${CONTAINER_ROLE:-app}
    env=${APP_ENV:-staging}

    HOST_IPV4=`curl http://169.254.169.254/latest/meta-data/local-ipv4`
    echo "MRP_URL=$HOST_IPV4:3508" >> .env
    php artisan config:clear && php artisan config:cache

    if [ "$env" = "local" ]; then
        composer install
    else
        echo "Caching configuration..."
        (php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear)
        (php artisan config:cache && php artisan event:cache && php artisan route:cache && php artisan view:cache)
    fi

    if [ "$role" = "api" ]; then

        echo "Building app..."

        if [ "$env" = "local" ]; then
            php artisan migrate
            php artisan passport:install
        fi
        service nginx start
        php-fpm


#    elif [ "$role" = "queue" ]; then
#
#        echo "Running the queue..."
#        if [ "$env" = "staging" ]; then
#            exec supervisord --nodaemon --configuration=/etc/supervisor/supervisord-queue-stg.conf
#        elif [ "$env" = "stg" ]; then
#            exec supervisord --nodaemon --configuration=/etc/supervisor/supervisord-queue-stg.conf
#        elif [ "$env" = "production" ]; then
#            exec supervisord --nodaemon --configuration=/etc/supervisor/supervisord-queue-prod.conf
#        elif [ "$env" = "prod" ]; then
#            exec supervisord --nodaemon --configuration=/etc/supervisor/supervisord-queue-prod.conf
#        else
#            exec supervisord --nodaemon --configuration=/etc/supervisor/supervisord-queue-dev.conf
#        fi

    elif [ "$role" = "cron" ]; then
        if [ "$env" = "staging" ]; then
           exec supervisord --nodaemon --configuration=/etc/supervisor/supervisord-batch-stg.conf
       elif [ "$env" = "stg" ]; then
           exec supervisord --nodaemon --configuration=/etc/supervisor/supervisord-batch-stg.conf
       elif [ "$env" = "production" ]; then
           exec supervisord --nodaemon --configuration=/etc/supervisor/supervisord-batch-prod.conf
       elif [ "$env" = "prod" ]; then
           exec supervisord --nodaemon --configuration=/etc/supervisor/supervisord-batch-prod.conf
       else
           exec supervisord --nodaemon --configuration=/etc/supervisor/supervisord-batch-dev.conf
       fi
    else
        echo "Could not match the container role \"$role\""
        exit 1
    fi

else
    exec "$@"
fi

