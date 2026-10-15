# FleetLogix · SIGCT

Aplicación Lumen para administrar una empresa de transporte: dashboard, flotilla, viajes, operadores, usuarios y consumo de combustible. Los módulos guardan sus datos en MySQL.

## Requisitos

- PHP 8.1 o posterior, con `pdo_mysql`, `mbstring` y `openssl`.
- Composer.
- MySQL o MariaDB.

## Instalación local

1. Instala las dependencias con `composer install`.
2. Crea una base MySQL vacía llamada `lumen_palindromo` con juego de caracteres `utf8mb4`.
3. Copia `.env.example` como `.env`, genera `APP_KEY` con `php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"` y configura el usuario y la contraseña locales de MySQL.
4. Ejecuta `php artisan migrate`.
5. Inicia el servidor con `php -S 127.0.0.1:8000 -t public`.
6. Abre `http://127.0.0.1:8000/registro` para registrar la empresa y la cuenta administradora.

## Datos y seguridad

- Las contraseñas se guardan con `password_hash`; el archivo `.env` y los archivos de base de datos locales están excluidos de Git.
- Las rutas operativas requieren sesión iniciada y los formularios POST validan token CSRF.
- No hay datos de muestra precargados. El dashboard empieza vacío y se completa con las operaciones registradas.
- El diseño de referencia corresponde al proyecto privado **FleetLogix SaaS TMS** en Stitch.
