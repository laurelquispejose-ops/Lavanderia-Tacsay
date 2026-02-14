#requires -Version 5.1
$ErrorActionPreference = "Stop"

Write-Host "=== TACSAY: Inicio de entorno de desarrollo ===" -ForegroundColor Cyan

# Verificar Node.js
try {
  $nodeVersion = (node -v)
  Write-Host "Node.js: $nodeVersion"
  $major = [int]($nodeVersion.TrimStart('v').Split('.')[0])
  if ($major -lt 18) {
    Write-Warning "Se recomienda Node >= 18 (ideal 20 LTS)."
  }
} catch {
  Write-Warning "Node.js no encontrado en PATH. Instala Node 18+ antes de continuar."
}

# Verificar Composer
try {
  $composerVersion = (composer -V)
  Write-Host "Composer: $composerVersion"
} catch {
  Write-Warning "Composer no encontrado en PATH. Instálalo antes de continuar (https://getcomposer.org/)."
}

# Copiar .env si no existe
if (-not (Test-Path ".env") -and (Test-Path ".env.example")) {
  Copy-Item ".env.example" ".env"
  Write-Host "Copiado .env desde .env.example" -ForegroundColor Green
}

# Crear SQLite si se usa y no existe
if (-not (Test-Path "database")) { New-Item -ItemType Directory -Path "database" | Out-Null }
if (-not (Test-Path "database/database.sqlite")) {
  New-Item -ItemType File -Path "database/database.sqlite" | Out-Null
  Write-Host "Creado database/database.sqlite" -ForegroundColor Green
}

# Backend: dependencias y preparación
Write-Host "Instalando dependencias de PHP (composer install)" -ForegroundColor Yellow
composer install

Write-Host "Generando APP_KEY" -ForegroundColor Yellow
php artisan key:generate

Write-Host "Ejecutando migraciones" -ForegroundColor Yellow
php artisan migrate

Write-Host "Semillas de base de datos" -ForegroundColor Yellow
php artisan db:seed

Write-Host "Enlazando storage público" -ForegroundColor Yellow
php artisan storage:link

# Frontend: dependencias y dev server
Write-Host "Instalando dependencias de Node (npm install)" -ForegroundColor Yellow
npm install

Write-Host "Levantando entorno concurrente (Laravel + cola + Vite)" -ForegroundColor Cyan
# Ejecutar procesos en paralelo sin Pail (compatible con Windows)
npx concurrently -c "#93c5fd,#fb7185,#fdba74" "php artisan serve" "php artisan queue:listen --tries=1" "npm run dev" --names=server,queue,vite
 