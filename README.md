# SP Hub

## Visão geral

`appnave/nave-hub-sp` é um pacote privado de Laravel para sincronização e importação de dados do SP via RabbitMQ.

O consumo é feito em projetos clientes por meio de repositório VCS no `composer.json`.

## Requisitos

- PHP 8.0, 8.1, 8.2 ou 8.3
- Laravel 8, 9, 10, 11 ou 12
- Composer 2
- Acesso ao repositório privado do pacote
- RabbitMQ para uso dos comandos de mensagem e configuração
- Banco de dados acessível para a importação do hub, quando aplicável

## Acesso a Repositórios Privados

No projeto cliente, adicione o repositório VCS no `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/ORG/REPO"
    }
  ]
}
```

Instale o pacote:

```bash
composer require appnave/nave-hub-sp:dev-develop
```

Autenticação local do Composer com GitHub:

```bash
composer config -g github-oauth.github.com <YOUR_TOKEN>
```

GitHub Actions:

```yaml
env:
  COMPOSER_AUTH: '{"github-oauth":{"github.com":"${{ secrets.GITHUB_TOKEN }}"}}'
```

Se o pacote ou alguma dependência privada exigir outro token, use um secret próprio no lugar do `GITHUB_TOKEN`.

## Instalação Local

No projeto cliente:

```bash
composer install
php artisan sp-hub:install
```

Se o ambiente usar RabbitMQ, rode também:

```bash
php artisan sp-hub:configure
```

Depois de ajustar `.env` ou `config/sp-hub.php`, recarregue o cache de configuração se o projeto usar cache:

```bash
php artisan config:clear
php artisan config:cache
```

## Variáveis de Ambiente

```dotenv
RABBITMQ_HOST=
RABBITMQ_PORT=5672
RABBITMQ_USER=
RABBITMQ_PASSWORD=
RABBITMQ_VIRTUALHOST=/
RABBITMQ_EXCHANGE_HUB=hub
RABBITMQ_QUEUE_HUB=
RABBITMQ_USE_SSL=true

HUB_DB_HOST=127.0.0.1
HUB_DB_PORT=3306
HUB_DB_DATABASE=forge
HUB_DB_USERNAME=forge
HUB_DB_PASSWORD=
```

Em ambiente `local`, o worker usa conexão sem SSL automaticamente.

## Useful Commands

```bash
php artisan sp-hub:install
php artisan sp-hub:configure
php artisan rabbitmqworker:hub
php artisan dataimport:hub --select=500 --offset=0 --tables=brands,companies,users,positions,permissions,roles,user_companies,user_company_parent_positions,user_company_real_estate_developments
```

- `sp-hub:install` publica a configuração e as migrations do pacote.
- `sp-hub:configure` cria exchange e fila no RabbitMQ quando o ambiente estiver habilitado.
- `rabbitmqworker:hub` consome e processa mensagens.
- `dataimport:hub` inicia a importação em background.

## Conventions

- O pacote publica migrations para tabelas e colunas relacionadas a users, companies, brands, roles e workers.
- Se o modelo `User` do projeto cliente usar mass assignment, inclua os campos extras suportados pelo pacote no `$fillable`.
- Campos mais usados pelo pacote:

```php
'document',
'address',
'street_number',
'complement',
'city',
'state',
'postal_code',
```

- Para `Company`, as migrations também podem exigir:

```php
'company_name',
'document',
'address',
'street_number',
'complement',
'city',
'state',
'postal_code',
```

- Este pacote é privado e depende da configuração do projeto cliente para repositórios Composer, variáveis de ambiente, RabbitMQ e banco do hub.
