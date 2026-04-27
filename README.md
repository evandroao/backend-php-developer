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

O **entrypoint** já executa internamente:
- criação das pastas `bootstrap/cache` e `storage/*` com permissões corretas
- geração da `APP_KEY` (se não existir)
- execução das migrations (se o banco estiver acessível)
- geração da documentação Swagger

> **Nota:** se aparecer o erro `Please provide a valid cache path`, basta reiniciar o container (`docker-compose restart app`) que o entrypoint recria as pastas automaticamente.

### 4. Acesse a aplicação

- API: `http://localhost:9012`
- Swagger UI: `http://localhost:9012/api/documentation`

### 5. Comandos úteis (watch & sync)

```bash
# Live-reload clássico via bind mount (já funciona com up)
docker compose up -d

# Sync otimizado via Docker Watch (ignora vendor, cache, storage)
docker compose watch
```

## Autenticação

A API utiliza JWT Bearer Token.

### Login

```bash
curl -X POST http://localhost:9012/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "admin"}'
```

Use o token retornado no header `authorization: bearer <token>` nas demais requisições.

### Permissões

| Role | Permissões |
|------|------------|
| **admin** | acesso total (crud de usuários, sincronização de shows) |
| **user** | leitura de shows e episódios apenas |

## Endpoints principais

| Método | Endpoint | Descrição | Role |
|--------|----------|-----------|------|
| POST | `/api/auth/login` | login e geração de jwt | público |
| GET | `/api/users` | listar usuários paginados | admin |
| POST | `/api/users` | criar usuário | admin |
| PUT | `/api/users/{id}` | atualizar usuário | admin |
| DELETE | `/api/users/{id}` | remover usuário | admin |
| GET | `/api/shows` | listar shows paginados | admin, user |
| GET | `/api/shows/{id}` | detalhes de um show com episódios | admin, user |
| POST | `/api/shows` | sincronizar show da tvmaze | admin |
| GET | `/api/episodes/average` | média de rating por temporada | admin, user |

## Testes

```bash
# Rodar todos os testes
docker-compose exec app php artisan test

# Rodar testes específicos
docker-compose exec app php artisan test --filter=ShowControllerTest
```

## Documentação Swagger

A documentação OpenAPI é gerada **automaticamente na inicialização do container** a partir das anotações nos controllers.

Caso precise regenerar manualmente (ex: após alterar anotações sem reiniciar):

```bash
docker-compose exec app php artisan l5-swagger:generate
```
