# Configuración de Coolify para SinFiltro

## Requisitos previos

- Servidor Hetzner con Coolify instalado
- Proyecto en GitHub/GitLab

## Paso 1: Subir código a GitHub

```bash
cd /Applications/MAMP/htdocs/sinfiltro
git add .
git commit -m "Add Puppeteer scraper for JS-rendered pages"
git push origin main
```

## Paso 2: Configurar en Coolify

1. Ve a tu panel de Coolify
2. Crea un nuevo proyecto o usa uno existente
3. Añade un nuevo recurso: **Application**
4. Conecta tu repositorio de GitHub
5. Selecciona el branch (main)

## Paso 3: Configuración del build

En la sección **Build Pack**, selecciona:
- **Build Pack**: PHP
- **PHP Version**: 8.2 o superior

### Variables de entorno necesarias:

En Coolify, ve a **Environment Variables** y añade:

```
APP_ENV=production
GEMINI_API_KEY=tu_api_key_de_gemini
CLAUDE_API_KEY=tu_api_key_de_claude
```

### Pre-build script:

En la sección **Pre-build script**, añade:

```bash
#!/bin/bash
cd /app/www/src/scraper
npm install
```

(O usa el script existente en scripts/build.sh)

## Paso 4: Configurar el directorio web

En **Settings** del recurso:
- **Directory**: `/app/www/public`
- **Port**: 80
- **Type**: Classic (Apache/Nginx)

## Paso 5: Desplegar

1. Click en **Deploy**
2. Espera a que termine el build
3. Revisa los logs para verificar que Puppeteer se instaló correctamente

## Solución de problemas

### Error: Puppeteer no puede lanzar Chrome

Asegúrate de que el servidor tenga las dependencias necesarias:

```bash
# En el servidor, ejecutar:
cd /app/www/src/scraper
npx puppeteer browsers install chrome
```

### Error: Memoria insuficiente

En Coolify, aumenta la memoria del servidor o configura un swap:

```bash
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
```
