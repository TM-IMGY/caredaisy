#!/bin/bash
set eux

NOW=`date '+%Y%m%d_%H%M%S'`
DEP_DIR="caredaisy_${NOW}"
mv -f /var/www/deploy /var/www/${DEP_DIR}

cd /var/www/${DEP_DIR}

aws s3 cp s3://caredaisy-prd-deploy/confidentials/.env.prd .env
aws s3 cp s3://caredaisy-prd-deploy/oauth/oauth-private.key storage/
aws s3 cp s3://caredaisy-prd-deploy/oauth/oauth-public.key storage/
chmod 750 .env

touch storage/logs/laravel.log

chown -R apache /var/www/${DEP_DIR}
chgrp -R apache /var/www/${DEP_DIR}

# キャッシュの再設定
php artisan config:cache
php artisan route:cache

cd /var/www/

# 今回デプロイしたソースコード以外は全て削除
find /var/www/caredaisy_[0-9]* -maxdepth 0 -type d -not -name ${DEP_DIR} | xargs rm -rf

ln -nfs ${DEP_DIR} caredaisy-web
sudo service httpd graceful