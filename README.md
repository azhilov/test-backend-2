# Запуск

### окружение:
- `docker-compose up`
- `composer install`
- `php bin/console doctrine:migrations:migrate`
- `php bin/console doctrine:fixtures:load`
- `openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096`
- `openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout`
- прописать кодовую фразу (поумолчанию в настройках **123456**) для сертификата в .env (параметр **JWT_PASSPHRASE**)
- `symfony server:start`

### тесты:
- `php vendor/bin/phpunit`

### документация:
- в файле **openapi.yaml**
