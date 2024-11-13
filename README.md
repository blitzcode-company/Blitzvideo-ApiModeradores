# Blitzvideo-ApiModeradores

<p align="center">
    <img src="https://drive.google.com/uc?export=download&id=1yyVoEHmLQgzYpDJJJvjtpo1MHdZNP84k" width="200">
</p>

### Configuración del proyecto

-   Para comenzar, clona el repositorio de GitHub a tu máquina local. Abre una terminal y ejecuta el siguiente comando:

`Vía SSH:`

```
git clone git@github.com:blitzcode-company/Blitzvideo-ApiModeradores.git
```

`Vía HTTPS:`

```
git clone https://github.com/blitzcode-company/Blitzvideo-ApiModeradores.git
```

-   Ingresamos al proyecto `cd Blitzvideo-ApiModeradores` y ejecutamos:

```
composer install
```

-   Dentro del directorio del proyecto de Laravel, generamos el archivo .env con el siguiente comando:

```
cp .env.example .env
```

-   Configuramos la base de datos dentro del archivo .env:

```
DB_HOST=172.8.0.7
DB_PORT=3306
```

- Generar la clave de la aplicación

```
php artisan key:generate
```

-   Realizamos las migraciones con el comando:

```
php artisan migrate
php db:seed
```

## Dependencia en Oauth-api

Este proyecto depende del servicio de autenticación OAuth proporcionado por el repositorio [Oauth-api](https://github.com/blitzcode-company/OauthApi-Moderadores).

## Docker Compose

Inicia el proyecto con el siguiente comando:

```
sudo docker-compose up -d
```
El proyecto estará corriendo en el puerto **8004**. Puede corroborarlo ingresando a `http://localhost:8004/`. 

**Nota:** Luego debes iniciar el proyecto de Oauth-api.
