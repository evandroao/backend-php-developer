#!/bin/sh
set -e

# Cria diretórios necessários para o Laravel (caso o bind mount os tenha sobrescrito vazios)
mkdir -p /app/bootstrap/cache
mkdir -p /app/storage/app/public
mkdir -p /app/storage/framework/cache/data
mkdir -p /app/storage/framework/sessions
mkdir -p /app/storage/framework/testing
mkdir -p /app/storage/framework/views
mkdir -p /app/storage/logs

# Ajusta permissões
chown -R www-data:www-data /app/bootstrap/cache /app/storage
chmod -R 775 /app/bootstrap/cache /app/storage

# Gera a chave da aplicação se não existir
if [ -z "$(grep '^APP_KEY=' /app/.env | cut -d '=' -f2)" ]; then
    php /app/artisan key:generate --no-interaction 2>/dev/null || true
fi

# Executa migrations automaticamente se o banco estiver acessível
php /app/artisan migrate --force --no-interaction 2>/dev/null || true

# Gera documentação Swagger a partir das anotações
php /app/artisan l5-swagger:generate --no-interaction 2>/dev/null || true

# Inicia o servidor
exec "$@"
