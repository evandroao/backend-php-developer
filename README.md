# TV Show Manager API

API REST para gerenciamento e sincronização de séries de TV com integração à [TVMaze API](https://www.tvmaze.com/api).

## Tecnologias

- PHP 8.3
- Laravel 12
- PostgreSQL 16
- Docker & Docker Compose
- JWT Auth (tymon/jwt-auth)
- OpenAPI/Swagger (L5-Swagger)

## Pré-requisitos

- Docker
- Docker Compose v2+

## Como executar

### 1. Clone o repositório

```bash
git clone <repo-url>
cd <app-dir>
```

### 2. Configure o ambiente

```bash
cp .env.example .env
```

### 3. Inicie os containers

```bash
docker-compose up --build -d
```

Aguarde o healthcheck do PostgreSQL (o app sobe automaticamente após o banco ficar pronto).

### 4. Gere a chave e execute as migrations

```bash
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
```

### 5. Acesse a aplicação

- API: `http://localhost:9012`
- Swagger UI: `http://localhost:9012/api/documentation`

## Autenticação

A API utiliza JWT Bearer Token.

### Login

```bash
curl -X POST http://localhost:9012/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "admin"}'
```

use o token retornado no header `authorization: bearer <token>` nas demais requisições.

### permissões

| role | permissões |
|------|------------|
| **admin** | acesso total (crud de usuários, sincronização de shows) |
| **user** | leitura de shows e episódios apenas |

## endpoints principais

| método | endpoint | descrição | role |
|--------|----------|-----------|------|
| post | `/api/auth/login` | login e geração de jwt | público |
| get | `/api/users` | listar usuários paginados | admin |
| post | `/api/users` | criar usuário | admin |
| put | `/api/users/{id}` | atualizar usuário | admin |
| delete | `/api/users/{id}` | remover usuário | admin |
| get | `/api/shows` | listar shows paginados | admin, user |
| get | `/api/shows/{id}` | detalhes de um show com episódios | admin, user |
| post | `/api/shows` | sincronizar show da tvmaze | admin |
| get | `/api/episodes/average` | média de rating por temporada | admin, user |

## testes

```bash
# rodar todos os testes
docker-compose exec app php artisan test

# rodar testes específicos
docker-compose exec app php artisan test --filter=showcontrollertest
```

## Documentação Swagger

A documentação OpenAPI é gerada automaticamente a partir das anotações nos controllers.

Para regenerar manualmente:

```bash
docker-compose exec app php artisan l5-swagger:generate
```
