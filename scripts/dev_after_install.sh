#!/bin/bash
set eux

NOW=`date '+%Y%m%d_%H%M%S'`
DEP_DIR="caredaisy_${NOW}"
cp -rf /var/www/deploy /var/www/${DEP_DIR}

cd /var/www/${DEP_DIR}

aws s3 cp s3://caredaisy-dev-deploy/confidentials/.env.dev .env
aws s3 cp s3://caredaisy-dev-deploy/oauth/oauth-private.key storage/
aws s3 cp s3://caredaisy-dev-deploy/oauth/oauth-public.key storage/
chmod 750 .env

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
