# SIGETI — Sistema de Gestão de Chamados de TI

Sistema web para abertura, acompanhamento e resolução de chamados de suporte de TI em ambiente escolar. Desenvolvido em PHP puro com arquitetura MVC, o sistema atende dois perfis de usuário: **professores**, que abrem chamados, e **técnicos**, que os gerenciam.

---

## Funcionalidades

### Autenticação
- Login com e-mail e senha
- Registro de conta (perfil professor)
- Redefinição de senha via e-mail com link tokenizado (expira em 2 horas)
- Sessão com timeout automático de 2 horas
- Proteção CSRF em todos os formulários
- Controle de acesso por perfil (professor / técnico)

### Painel do Técnico
- Dashboard com métricas do ano corrente:
  - Quantidade de chamados por status
  - Chamados por mês (gráfico)
  - Taxa de resolução
  - Tempo médio de resolução por mês
  - Distribuição por prioridade e status
- CRUD completo de **chamados**
- CRUD completo de **categorias**
- CRUD completo de **escolas**
- CRUD completo de **usuários**
- Gerenciamento de **comentários** nos chamados (criar, editar, excluir)
- Atualização de perfil e alteração de senha

### Painel do Professor
- Dashboard com listagem dos próprios chamados
- Abertura de novos chamados
- Acompanhamento de chamados abertos
- Comentários nos chamados
- Atualização de perfil e alteração de senha

### Fluxo de Status dos Chamados

```
Aberto ──► Em Andamento ──► Aguardando ──► Em Andamento
                │                               │
                └──────────────────► Resolvido ─► Finalizado ─► Arquivado
```

Toda transição de status é validada — não é possível pular etapas fora do fluxo permitido.

---

## Tecnologias

| Camada | Tecnologia |
|--------|-----------|
| Linguagem | PHP 8.2+ |
| Banco de dados | MySQL 8.0 |
| Roteamento | CoffeeCode Router |
| Template engine | League Plates |
| ORM / Query Builder | AbstractModel customizado (PDO) |
| Paginação | CoffeeCode Paginator |
| E-mail | PHPMailer + SendGrid |
| Variáveis de ambiente | vlucas/phpdotenv |
| Frontend | Bootstrap 5 + ApexCharts |
| Servidor | Apache + mod_rewrite |

---

## Estrutura do Projeto

```
sigeti/
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── ErrorController.php
│   │   ├── Teacher/           # Controllers do professor
│   │   └── Technician/        # Controllers do técnico
│   ├── Core/
│   │   ├── AbstractModel.php  # ORM base com PDO
│   │   ├── Auth.php           # Gerenciamento de sessão autenticada
│   │   ├── Connection.php     # Singleton de conexão PDO
│   │   ├── Controller.php     # Controller base
│   │   ├── Email.php          # Wrapper do PHPMailer
│   │   ├── Message.php        # Flash messages
│   │   ├── Session.php        # Gerenciamento de sessão
│   │   └── SessionTimeoutMiddleware.php
│   ├── Helpers/               # Funções auxiliares globais
│   ├── Models/                # Modelos de dados
│   └── Views/                 # Templates PHP (League Plates)
├── config/
│   └── app.php                # Carrega .env e define constantes
├── public/                    # Assets públicos (CSS, JS)
├── resources/themes/          # Tema e componentes de UI
├── routes/
│   ├── web.php
│   ├── auth.php
│   ├── teacher.php
│   └── technician.php
├── storage/sessions/          # Arquivos de sessão PHP
├── vendor/                    # Dependências (Composer)
├── .env.example               # Modelo de variáveis de ambiente
├── .htaccess                  # Reescrita de URL para o index.php
├── composer.json
└── index.php                  # Entry point da aplicação
```

---

## Pré-requisitos

- PHP >= 8.2 com extensões `pdo`, `pdo_mysql`, `mbstring`
- MySQL 8.0+
- Composer
- Apache com `mod_rewrite` habilitado

---

## Instalação

### 1. Clone o repositório

```bash
git clone https://github.com/seu-usuario/sigeti.git
cd sigeti
```

### 2. Instale as dependências

```bash
composer install
```

### 3. Configure o ambiente

Copie o arquivo de exemplo e preencha com suas configurações:

```bash
cp .env.example .env
```

```env
APP_NAME="SIGETI - Sistema de Gestão de Chamados de TI"
APP_ENV=local
APP_URL=http://localhost/sigeti

APP_TIMEZONE=America/Sao_Paulo

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sigeti
DB_USERNAME=root
DB_PASSWORD=sua_senha

EMAIL_SEND=seu@email.com
EMAIL_NAME=Seu Nome
USERNAME_SENDGRID=apikey
PASSWORD_SENDGRID=sua_chave_sendgrid
```

### 4. Crie o banco de dados

```sql
CREATE DATABASE sigeti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Em seguida, importe o arquivo SQL do projeto (se disponível) ou execute as migrations.

### 5. Configure o Apache

O arquivo `.htaccess` já está configurado. Certifique-se de que `AllowOverride All` está habilitado no Virtual Host:

```apache
<Directory /var/www/html/sigeti>
    AllowOverride All
    Require all granted
</Directory>
```

### 6. Acesse no navegador

```
http://localhost/sigeti
```

---

## Rodando com Docker

O projeto inclui configuração Docker com MySQL persistente.

```bash
# Suba os containers
docker compose up -d --build

# Acesse em
http://localhost:8080/sigeti
```

No `.env`, use `DB_HOST=db` para apontar para o container MySQL.

Consulte o arquivo `DOCKER.md` para instruções completas, incluindo como conectar via DataGrip ou outro cliente de banco.

---

## Perfis de Usuário

| Perfil | Acesso | Descrição |
|--------|--------|-----------|
| `tecnico` | `/tecnico/*` | Gerencia todo o sistema: chamados, usuários, escolas e categorias |
| `professor` | `/professor/*` | Abre e acompanha os próprios chamados |

Novos cadastros feitos pela tela de registro recebem automaticamente o perfil **professor** e o status **registrado**. Um técnico precisa ativar a conta manualmente.

---

## Status e Prioridades dos Chamados

**Status disponíveis:** `aberto` · `em_andamento` · `aguardando` · `resolvido` · `finalizado` · `arquivado`

**Prioridades:** `baixa` · `media` · `alta`

---

## Segurança

- Senhas armazenadas com `password_hash()` (bcrypt)
- Proteção contra CSRF em todos os formulários POST
- Soft delete em usuários, chamados e demais entidades
- Timeout de sessão configurável (padrão: 2 horas)
- Token de reset de senha com hash SHA-256 e expiração

---

## Autor

**Pedro Leandro** — Curso Técnico em Informática Para Internet
