# 🔒 INFORME DE AUDITORÍA DE SEGURIDAD PHP

**Proyecto:** SinFiltros  
**Fecha:** 2026-02-18  
**Auditor:** Expert Security Scanner  
**Tipo:** Auditoría de Vulnerabilidades - Inyección SQL & XSS

---

## 📊 RESUMEN EJECUTIVO

| Categoría | Vulnerabilidades Encontradas | Severidad |
|-----------|------------------------------|-----------|
| Inyección SQL | **0** | - |
| XSS | **2** | Media |
| SSL/TLS Deshabilitado | **6** | Crítica |
| SSRF | **6** | Alta |
| Logging Inseguro | **1** | Baja |

---

## ✅ INYECCIÓN SQL

**Resultado:** No se encontraron vulnerabilidades de Inyección SQL.

El proyecto NO utiliza consultas SQL tradicionales. Es una aplicación que:
- Consume APIs externas de IA (Gemini/Claude)
- Almacena resultados en cache JSON
- NO conecta a base de datos MySQL/PostgreSQL

---

## ⚠️ VULNERABILIDADES XSS (Cross-Site Scripting)

### 1. Variables de salida sin sanitizar en _header.php

| Campo | Valor |
|-------|-------|
| **Archivo** | [`_header.php`](_header.php:117) |
| **Línea** | 117, 131 |
| **Riesgo** | 🔶 Medio |
| **Código Actual** | `<?= $item['icon'] ?>` y `<?= $item['label'] ?>` |
| **Código Sugerido** | `<?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>` y `<?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>` |

**Nota:** Los valores actualmente están hardcodeados en el array `$navItems`, por lo que el riesgo real es bajo. Sin embargo, si en el futuro estos valores provienen de una fuente externa, sería vulnerable.

---

## 🔴 VULNERABILIDADES CRÍTICAS ENCONTRADAS

### 2. Verificación SSL Deshabilitada (CRÍTICO)

| Campo | Valor |
|-------|-------|
| **Archivos Afectados** | [`coche-api.php`](coche-api.php:45), [`hipoteca-api.php`](hipoteca-api.php:45), [`luz-api.php`](luz-api.php:45), [`telco-api.php`](telco-api.php:45), [`seguros-api.php`](seguros-api.php:45), [`inversiones-api.php`](inversiones-api.php:45) |
| **Líneas** | 45-46 |
| **Riesgo** | 🔴 Crítico |
| **Código Actual** | ```php<br>CURLOPT_SSL_VERIFYPEER => false,<br>CURLOPT_SSL_VERIFYHOST => false,``` |
| **Código Sugerido** | ```php<br>CURLOPT_SSL_VERIFYPEER => true,<br>CURLOPT_SSL_VERIFYHOST => 2,``` |

**Descripción:** Esta configuración permite ataques **Man-In-The-Middle (MITM)**. Un atacante en la red podría interceptar y modificar las respuestas del servidor remoto.

---

### 3. Validación Insuficiente de URLs (SSRF)

| Campo | Valor |
|-------|-------|
| **Archivos Afectados** | [`coche-api.php`](coche-api.php:216), [`hipoteca-api.php`](hipoteca-api.php:216), [`luz-api.php`](luz-api.php:216), [`telco-api.php`](telco-api.php:216), [`seguros-api.php`](seguros-api.php:216), [`inversiones-api.php`](inversiones-api.php:216) |
| **Líneas** | 216 |
| **Riesgo** | 🟠 Alto |
| **Código Actual** | `$isUrl = (bool) preg_match('/^https?:\/\//i', $rawOfferText);` |
| **Código Sugerido** | ```php<br>$parsedUrl = @parse_url($rawOfferText);<br>$isUrl = (<br>    isset($parsedUrl['scheme']) &&<br>    isset($parsedUrl['host']) &&<br>    in_array($parsedUrl['scheme'], ['http', 'https'], true) &&<br>    !in_array($parsedUrl['host'], ['localhost', '127.0.0.1', '0.0.0.0', 'metadata.google.internal'], true) &&<br>    !preg_match('/^10\.\d+\.\d+\.\d+$/', $parsedUrl['host']) &&<br>    !preg_match('/^172\.(1[6-9]|2\d|3[01])\.\d+\.\d+$/', $parsedUrl['host']) &&<br>    !preg_match('/^192\.168\.\d+\.\d+$/', $parsedUrl['host'])<br>);``` |

**Descripción:** La validación actual solo verifica que la URL comience con `http://` o `https://`, pero NO verifica:
- Dominios privados/internalos (localhost, 127.0.0.1)
- Redes privadas (10.x.x.x, 192.168.x.x, 172.16-31.x.x)
- Metadatos de cloud (metadata.google.internal)

Esto permite **Server-Side Request Forgery (SSRF)** - un atacante podría hacer que el servidor haga peticiones a servicios internos.

---

### 4. Logging con Entrada del Usuario (BAJO)

| Campo | Valor |
|-------|-------|
| **Archivo** | [`ai-helper.php`](ai-helper.php:142) |
| **Línea** | 142 |
| **Riesgo** | 🟢 Bajo |
| **Código Actual** | `$line = "$ts\t$model\t$tool\tHTTP-$httpCode\t$ip\t$message\n";` |
| **Código Sugerido** | `$line = "$ts\t$model\t$tool\tHTTP-$httpCode\t$ip\t" . preg_replace('/[\r\n\t]/', ' ', $message) . "\n";` |

**Descripción:** El mensaje de error del usuario se escribe directamente al log sin sanitizar. Aunque el riesgo es bajo (solo afecta al log), podría permitir log injection.

---

## 📋 RECOMENDACIONES DE SEGURIDAD

### Prioridad Alta (Corregir Inmediatamente)

1. **Habilitar verificación SSL** - Cambiar `CURLOPT_SSL_VERIFYPEER` y `CURLOPT_SSL_VERIFYHOST` a `true`
2. **Implementar protección SSRF** - Validar que las URLs no apunten a redes privadas

### Prioridad Media (Corregir Pronto)

3. **Sanitizar salidas HTML** - Usar `htmlspecialchars()` en todas las variables que se imprimen en HTML
4. **Validar entradas** - Implementar validación más estricta de los parámetros de entrada

### Buenas Prácticas

5. **Rate Limiting** - Ya implementado correctamente ✅
6. **Rate Limiting por IP** - Ya implementado correctamente ✅
7. **Content-Type headers** - Ya configurados correctamente ✅

---

## 🛡️ MEDIDAS DE SEGURIDAD YA IMPLEMENTADAS

El proyecto cuenta con varias medidas de seguridad positivas:

- ✅ Rate limiting por IP (30 peticiones/hora)
- ✅ Validación de método HTTP (solo POST)
- ✅ Cabeceras de seguridad (X-Content-Type-Options: nosniff)
- ✅ Uso de md5() para claves de cache (previene path traversal)
- ✅ Validación de tipo de contenido JSON
- ✅ Sanitización de HTML en el header del menú
- ✅ no usa eval(), unserialize(), shell_exec() o funciones peligrosas

---

*Informe generado automáticamente. Se recomienda verificar manualmente las correcciones sugeridas.*