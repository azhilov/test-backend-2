# Запуск

### окружение:
- `docker-compose up`
- `composer install`
- `php bin/console doctrine:migrations:migrate`
- `openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096`
- `openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout`
- прописать кодовую фразу для сертификата в .env (параметр **JWT_PASSPHRASE**)
- `symfony server:start`

### тесты:
- `php vendor/bin/phpunit`
