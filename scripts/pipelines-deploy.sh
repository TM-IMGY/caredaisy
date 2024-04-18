apt-get update && apt-get install -qy git zip unzip curl libzip-dev
docker-php-ext-install zip

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
cp ./scripts/${ENV_NAME}_after_install.sh ./scripts/after_install.sh
chmod -R 750 ./*
composer install
npm run dev
zip -ryq laravel.zip .
curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
unzip awscliv2.zip
./aws/install
aws s3 cp laravel.zip s3://${S3_BUCKET}
