# OutfitGo Backend

Este repositorio contiene la API RESTful de la plataforma de e-commerce OutfitGo, desarrollada sobre Laravel 11. Se encarga de proveer los datos del catálogo, procesar pagos mediante Stripe, gestionar la base de datos MySQL y la integración de Inteligencia Artificial para el asistente de estilo.

## 🛠️ Acerca de este Fork

He hecho un fork de este repositorio para arreglar cosas que vea oportunas, corregir posibles fallos de configuración y poder desplegarlo en producción.

## ⚠️ Funciones no operativas o limitadas

La base del backend es robusta, pero hay ciertas funciones que se han desactivado o no están operativas:

- **Asistente de IA:** La integración y generación de outfits con Inteligencia Artificial **tampoco va**.
- **Envío de Correos:** Básicamente **no funciona el tema de los correos** (SMTP, envíos de confirmación de compra, registro, etc.).
- **Administración:** Aunque el código dispone de un **sistema completo de gestión de usuarios y artículos** (controladores y rutas de admin), **no voy a dejar acceder** a este sistema en el despliegue. Y creo que nada más.

## 🚀 Entorno Docker

El proyecto está preparado para ejecutarse rápidamente con Docker Compose:

1. Iniciar los contenedores:
   ```bash
   docker compose up -d
   ```

2. Migrar y poblar la base de datos (con catálogo de prueba):
   ```bash
   docker compose exec app php artisan migrate:fresh --seed
   ```

El servidor local estará corriendo y expuesto en el puerto `8000`.
