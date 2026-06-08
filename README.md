# 🚀 E-Commerce Backend v2.0 | Core Engine (OutfitGo)

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![AWS](https://img.shields.io/badge/AWS-232F3E?style=for-the-badge&logo=amazon-aws&logoColor=white)
![GitHub Actions](https://img.shields.io/badge/CI/CD-GitHub_Actions-2088FF?style=for-the-badge&logo=github-actions&logoColor=white)

Este repositorio contiene el **Core Engine del Backend** para la tienda online **OutfitGo**, construido sobre Laravel 11. Está estructurado como una API RESTful desacoplada para dar servicio al Frontend en Angular 19 y se encuentra desplegado de forma segura en la nube de AWS.

---

## 📚 Índice de Documentación Consolidada

Para consultar el manual específico de cada área del proyecto, accede a las siguientes guías unificadas:

1.  👉 **[Guía de Integración de API (Frontend Angular)](file:///d:/Mis%20Datos/Documentos/GitHub/Backend/docs/API_INTEGRATION_GUIDE.md)**: Lista completa de endpoints (Catálogo, Auth Sanctum, Carrito, Stripe Checkout y Testimonios), formatos JSON de petición/respuesta y reglas críticas que la UI debe cumplir.
2.  👉 **[Manual de Desarrollo Interno (Desarrolladores Backend)](file:///d:/Mis%20Datos/Documentos/GitHub/Backend/docs/INTERNAL_DEVELOPMENT.md)**: Estructura del proyecto, convenciones de base de datos, lógica transaccional de compras, observadores para alertas de stock y funcionamiento interno del panel de administración.
3.  👉 **[Guía de Despliegue y Operaciones (DevOps)](file:///d:/Mis%20Datos/Documentos/GitHub/Backend/docs/DEPLOYMENT_GUIDE.md)**: Pipelines de CI/CD automatizados con GitHub Actions, configuración de variables de producción y diseño detallado del Balanceador de Carga EC2 (Nginx).

---

## 🛠️ Especificaciones Técnicas

| Característica | Estado / Detalle |
| :--- | :--- |
| **Modelo de Negocio** | **E-Commerce Directo**. Productos con precio y stock propio en inventario. |
| **Segmentación** | **Filtros por Público**: Adulto, Infantil, Unisex (mediante Enum de BD). |
| **Calidad de Contenido** | **Descripciones Estrictas**: Longitud obligatoria de entre 300 y 500 caracteres. |
| **Optimización de Consultas**| **Eager Loading**: Carga conjunta de Categoría, Tallas, Colores y Marcas en un único query. |
| **Infraestructura** | **Cloud Native**: Dockerizados y desplegados en instancias AWS EC2 con base de datos RDS. |

---

## 📋 Requisitos Previos

*   [Docker Desktop](https://www.docker.com/products/docker-desktop) instalado y en ejecución en tu máquina local.
*   Git.

---

## 🛠️ Instalación y Arranque en Local (Docker)

Sigue estos comandos para inicializar el entorno de desarrollo local con Docker Compose:

1.  **Clonar el repositorio**:
    ```bash
    git clone https://github.com/OutfitGo-Proyecto/Backend.git
    cd Backend
    ```

2.  **Iniciar los contenedores Docker en segundo plano**:
    ```bash
    docker compose up -d
    ```

3.  **Configuración inicial (Solo la primera vez)**:
    Instala las dependencias y ejecuta las migraciones necesarias dentro del contenedor de la aplicación:
    ```bash
    # Instalar dependencias de Composer (PHP)
    docker exec laravel composer install

    # Crear archivo .env local y generar clave de cifrado
    docker exec laravel php -r "file_exists('.env') || copy('.env.example', '.env');"
    docker exec laravel php artisan key:generate

    # Ejecutar las migraciones (creación de tablas)
    docker exec laravel php artisan migrate
    ```

4.  **Verificar el estado de la aplicación**:
    Abre tu navegador en `http://localhost:8000`. Debería cargar la página de bienvenida de Laravel o la respuesta base de la API.

---

## 🧪 Comandos Útiles de Mantenimiento

*   **Poblar la Base de Datos con Datos Ficticios (Seeders)**:
    Si deseas limpiar el catálogo y generar productos de prueba aleatorios con descripciones coherentes:
    ```bash
    docker exec laravel php artisan migrate:fresh --seed
    ```
*   **Ejecutar los Tests Unitarios y de Integración (PHPUnit)**:
    ```bash
    docker exec laravel php artisan test
    ```

---

## 📂 Estructura de Directorios

*   `home/`: Código fuente de la aplicación Laravel.
*   `docs/`: Guías de integración de API, desarrollo interno y operaciones.
*   `docker/`: Archivos de configuración de Nginx y contenedores para producción.
*   `docker-compose.yaml`: Configuración de servicios locales (Laravel y base de datos).
