# TACSAY - Sistema de Gestión de Lavandería

TACSAY es una aplicación web para la gestión de órdenes de lavandería desarrollada con Laravel y Vue.js. El sistema permite gestionar el flujo completo de órdenes desde su creación hasta su finalización, pasando por diferentes estados de procesamiento.

## Características Principales

- **Gestión de Órdenes**: Creación, seguimiento y actualización de órdenes de lavandería
- **Flujo de Estados**: Las órdenes pasan por diferentes estados (pendiente, en proceso lavado, en proceso planchado, finalizado)
- **Exportación de Datos**: Exportación de órdenes a formatos CSV y PDF con filtros por estado y rango de fechas
- **Validación de Datos**: Sistema robusto de validación de datos de clientes y contraseñas
- **Integración con RENIEC**: Consulta de datos de clientes por DNI a través de la API de RENIEC
- **Perfil de Cliente**: Gestión de perfil de usuario con estadísticas de órdenes y actualización de datos personales
- **Pasarela de Pago**: Integración con Culqi para procesar pagos con tarjeta de crédito/débito

## Requisitos Previos

- PHP >= 8.2
- Composer
- Node.js 18+ y npm
- SQLite (configurado en el archivo .env)

## Nuevas Funcionalidades

### Perfil de Cliente

La aplicación ahora cuenta con un sistema completo de gestión de perfil para los clientes, que incluye:

- **Visualización de datos personales**: Nombre, correo, teléfono, dirección
- **Actualización de información**: Posibilidad de editar datos personales
- **Cambio de contraseña**: Sistema seguro para actualizar credenciales
- **Estadísticas de órdenes**: Resumen de órdenes totales, pendientes, en proceso y finalizadas

### Pasarela de Pago con Culqi

Se ha implementado una integración completa con Culqi para procesar pagos con tarjeta:

- **Selección de método de pago**: Al crear una orden, se puede elegir "Tarjeta" como método
- **Procesamiento seguro**: Los datos de tarjeta se procesan directamente en Culqi
- **Confirmación de pago**: Notificaciones de éxito o error al procesar el pago
- **Actualización automática**: El estado del pago se actualiza en el sistema

Para utilizar esta funcionalidad, es necesario configurar las llaves de API de Culqi en el archivo `.env`.

## Instalación

Sigue estos pasos para instalar y configurar el proyecto en tu entorno local:

### 1. Clonar el repositorio

```bash
git clone https://github.com/jlescanog/lavanderia.git
cd lavanderia
```

### 2. Configurar el archivo .env

```bash
cp .env.example .env
```

Edita el archivo `.env` para configurar la conexión a la base de datos SQLite y las claves de API:

```
DB_CONNECTION=sqlite
# Comenta o elimina las siguientes líneas de configuración de MySQL
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

# Configuración de RENIEC API (opcional)
RENIEC_API_TOKEN=tu_token_de_reniec

### Proveedor RENIEC actual

La consulta de DNI usa el proveedor definido en `RENIEC_API_URL` y `RENIEC_API_TOKEN` (por ejemplo `api.decolecta.com`). Ajusta ambos valores en `.env` según tu proveedor vigente.

- `RENIEC_API_URL`: endpoint que recibe `numero` del DNI, por ejemplo `https://api.decolecta.com/v1/reniec/dni?numero={numero}`
- `RENIEC_API_TOKEN`: token del proveedor

Después de editar `.env`:

```bash
php artisan config:clear
```

Prueba local del endpoint (backend en `http://localhost:8000`):

```bash
curl "http://localhost:8000/api/consultar-dni?numero=12345678"
```

Notas:
- El proveedor ya no es `apis.net.pe`; usa el que definas en `RENIEC_API_URL`.
- Puedes cambiar de proveedor sin tocar código; solo ajusta `.env`.

# Configuración de Culqi (para procesar pagos)
CULQI_PUBLIC_KEY=pk_test_tu_llave_publica
CULQI_PRIVATE_KEY=sk_test_tu_llave_privada
```

Asegúrate de crear el archivo de base de datos SQLite:

```bash
touch database/database.sqlite
```

### 3. Instalar dependencias de PHP (backend - Laravel)

```bash
composer install
```

### 4. Generar la clave de la aplicación

```bash
php artisan key:generate
```

### 5. Ejecutar las migraciones y seeders

```bash
php artisan migrate
php artisan db:seed
```

### 6. Instalar dependencias de Node.js (frontend)

```bash
npm install
```

### 7. Compilar los assets del frontend

```bash
npm run dev
```

### 8. Iniciar el servidor de desarrollo

```bash
php artisan serve
```

La aplicación estará disponible en [http://localhost:8000](http://localhost:8000)

## Estructura del Proyecto

- **app/**: Contiene los modelos, controladores y lógica de negocio
- **resources/js/**: Componentes Vue.js y lógica del frontend
- **resources/views/**: Plantillas Blade y vistas de la aplicación
- **routes/**: Definición de rutas API y web
- **database/**: Migraciones y seeders para la base de datos

## Uso Básico

1. Registra un nuevo cliente o busca uno existente por DNI
2. Crea una nueva orden seleccionando prendas y servicios
3. Sigue el flujo de la orden a través de los diferentes estados
4. Exporta informes de órdenes según necesites

## Contribución

Si deseas contribuir a este proyecto, por favor:

1. Haz un fork del repositorio
2. Crea una rama para tu funcionalidad (`git checkout -b feature/nueva-funcionalidad`)
3. Haz commit de tus cambios (`git commit -am 'Añade nueva funcionalidad'`)
4. Haz push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crea un nuevo Pull Request

## Arranque directo (Windows)
Para evitar pasos manuales:

- Opción rápida (todo en paralelo):
```bash
composer run dev
```

- Script one-command:
```bash
powershell -ExecutionPolicy Bypass -File .\start-dev.ps1
```
Este script instala dependencias, prepara la base de datos y arranca todo.

## Usuarios de prueba
- Empleado (creado por seeders):
	- Correo: empleado@demo.com
	- Clave: Empleado123!
	- Fuente: [database/seeders/EmpleadoSeeder.php](database/seeders/EmpleadoSeeder.php)

## Verificación rápida
1. Abre http://localhost:8000
2. Inicia sesión y crea/consulta una orden
3. Prueba la página de pago del cliente: `/cliente/pago/{orden_id}`

## Problemas comunes
- PowerShell bloquea scripts de npm:
	- Solución (ejecuta en PowerShell):
		```powershell
		Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned -Force
		```

## Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo LICENSE para más detalles.
