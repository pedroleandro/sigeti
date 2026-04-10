# Como rodar o SIGETI com Docker

## Pré-requisitos
- Docker e Docker Compose instalados

O MySQL roda dentro de um container próprio. Os dados ficam num **volume Docker persistente** — ou seja, `docker compose down` e `docker compose up` não apagam nada.

---

## 1. Copie os arquivos para o projeto

Coloque o `Dockerfile` e o `docker-compose.yml` na **raiz do projeto**:

```
sigeti/
├── Dockerfile
├── docker-compose.yml
├── index.php
├── .env
├── composer.json
└── ...
```

---

## 2. Ajuste o `.env`

O container PHP acessa o banco pelo nome do serviço `db` (rede interna do Docker):

```env
DB_HOST=db
DB_PORT=3306
DB_DATABASE=sigeti
DB_USERNAME=root
DB_PASSWORD=root
```

---

## 3. Suba os containers

```bash
docker compose up -d --build
```

Na primeira vez o Docker vai baixar a imagem do MySQL e criar o banco `sigeti` automaticamente. Aguarde alguns segundos até o healthcheck passar.

---

## 4. Acesse no navegador

```
http://localhost:8080/sigeti
```

---

## Conectando pelo DataGrip (ou DBeaver, Workbench...)

O MySQL está exposto na porta **3306 da sua máquina**, então basta criar uma conexão normal:

| Campo    | Valor       |
|----------|-------------|
| Host     | `localhost` |
| Port     | `3306`      |
| Database | `sigeti`    |
| User     | `root`      |
| Password | `root`      |

---

## Sobre a persistência dos dados

Os dados do MySQL ficam num volume Docker chamado `sigeti_db_data`.

```bash
docker compose down        # para e remove containers — dados PRESERVADOS ✅
docker compose down -v     # para, remove containers E apaga os dados ⚠️
```

Para ver os volumes existentes:
```bash
docker volume ls
```

---

## Comandos úteis

```bash
# Subir (ou reconstruir após mudança no Dockerfile)
docker compose up -d --build

# Ver logs em tempo real
docker compose logs -f

# Ver só logs do PHP/Apache
docker compose logs -f app

# Acessar o terminal do container PHP
docker compose exec app bash

# Acessar o MySQL pelo terminal
docker compose exec db mysql -uroot -proot sigeti

# Parar sem apagar dados
docker compose down
```

---

## Solução de problemas

**App não conecta no banco (erro PDO):**
- Confirme que o `.env` está com `DB_HOST=db`
- Veja se o container db subiu: `docker compose ps`
- Aguarde o healthcheck: `docker compose logs db`

**Página em branco ou erro 500:**
- `docker compose logs -f app`

**`vendor/` não encontrado:**
```bash
docker compose exec app composer install
```
