# Demenagement-api

- PHP 8.3
- Symfony 7.2.5

## Installation

```bash
composer install
```

## Configuration

### Database

- MySQL 8 database

### Authentification

- add jwt folder with public and private key

### Mailer

- add MAILER_DSN env variable

## Run

```bash
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
symfony console doctrine:fixtures:load
symfony console cache:clear
symfony console cache:warmup
```

```bash
symfony serve
```

## Usage

**You can use some parameters to filter the boxes** :
- `room` : filter boxes data per room id 
- `element` : filter boxes by only showing the ones containing the searched element.