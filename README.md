# Zpot - Alquiler de parking

Proyecto del Grupo 3. Aplicación web para el alquiler temporal de plazas de aparcamiento privadas.

## Tecnologías
- Frontend: HTML, CSS, JavaScript
- Backend: PHP
- Base de datos: SQL

## Mapa
El mapa usa **Leaflet** con fondos **CARTO Voyager** (gratis, sin API key). Las plazas se muestran con coordenadas por defecto en Málaga; no hace falta configurar nada.

## Base de datos (un solo archivo)
Todo está en `database/zpot_bd.sql`. Ejecutar una vez (con MySQL/MariaDB en marcha):
```bash
mysql -u root -p < database/zpot_bd.sql
```
Crea el usuario `admin` (contraseña `admin`), la base `zpot_bd`, las tablas (USUARIO, PLAZA, RESERVA) y datos de ejemplo: un usuario demo y 8 plazas en Málaga para probar el mapa.

## Estado del proyecto
Actualmente el proyecto ha avanzado a la creación de la base de datos y las páginas de registro e inicio de sesión de la aplicación web.
