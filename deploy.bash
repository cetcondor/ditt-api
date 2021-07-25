#!/bin/bash

set -e

rsync -av ./bin/ visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev/bin/
rsync -av ./config/ visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev/config/
rsync -av ./node_modules/ visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev/node_modules/
rsync -av ./public/ visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev/public/
rsync -av ./src/ visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev/src/
rsync -av ./templates/ visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev/templates/
rsync -av ./vendor/ visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev/vendor/
# rsync -av ./.env visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev
rsync -av ./.htaccess visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev
rsync -av ./composer.json visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev
rsync -av ./composer.lock visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev
rsync -av ./package.json visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev
rsync -av ./package-lock.json visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev
rsync -av ./RoboFile.php visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev
rsync -av ./symfony.lock visionapps@opal5.opalstack.com:/home/visionapps/apps_data/ditt_api_dev
