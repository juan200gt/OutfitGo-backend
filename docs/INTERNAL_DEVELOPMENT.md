# 🛠️ Manual de Desarrollo Interno (Backend Laravel)

Este manual técnico está dirigido a los desarrolladores del Backend de **OutfitGo**. Contiene la guía de arquitectura, los estándares de codificación del proyecto, las especificaciones de base de datos, las reglas del panel de administración y el funcionamiento de la lógica de negocio avanzada como las transacciones del checkout y el sistema de notificaciones de stock.

---

## 🎯 1. Visión del Proyecto y Stack Tecnológico

OutfitGo es un e-commerce headless de moda donde el backend y el frontend se comunican estrictamente a través de peticiones HTTP en formato JSON.

*   **Framework Principal**: Laravel 11.
*   **Base de Datos**: MySQL 8.0 / Amazon RDS.
*   **Entorno de Ejecución**: Docker & Docker Compose.
*   **Manejo de Autenticación**: Laravel Sanctum (Tokens persistentes en base de datos).
*   **Pasarela de Pago**: Stripe SDK (API Checkout redirigido).
*   **Sistema de Colas (Queues)**: Utilizado para envíos de correo en segundo plano, evitando bloqueos por peticiones concurrentes.

---

## 👥 2. Estándares y Flujo de Trabajo

### Convenciones de Idioma y Codificación
1.  **Tablas y Modelos (Español)**: Los nombres de tablas y modelos de base de datos se crean en español para alinearse con el dominio del negocio (ej. `Producto`, `Talla`, `Color`, `Pedido`).
2.  **Lógica y Controladores (Inglés/Spanglish)**: Se permite el uso de lógica en inglés o clases mixtas tradicionales (ej: `ProductoController`, `CartController`, `AddressController`).
3.  **JSON Estandarizado**: Las respuestas de la API deben retornar siempre claves consistentes y códigos de estado HTTP semánticos (ej: `200` para éxito, `201` para recurso creado, `401` para tokens inválidos, `422` para fallos de validación).

### Flujo de Desarrollo (Git Workflow)
*   **Ramas**: Todo cambio se desarrolla en ramas específicas (`feature/nombre-rama` o `fix/nombre-fix`).
*   **Despliegue**: La rama `main` está protegida. Al fusionar un PR, GitHub Actions ejecuta los tests locales y, si pasan con éxito, actualiza automáticamente el servidor de AWS EC2 mediante SSH.

---

## 📦 3. Lógica Transaccional de Compras

La API implementa un proceso de checkout altamente transaccional encapsulado para evitar problemas de inconsistencia de datos (por ejemplo, cobrar al usuario pero no guardar el pedido si falla el servidor).

### 3.1 Checkout con Stripe (Paso a Paso)
Ubicación: `app/Http/Controllers/Api/CheckoutController.php`

1.  **Inicio de Pago (`iniciarPago`)**:
    *   Se envuelve la operación en `DB::beginTransaction()`.
    *   Se comprueba la existencia de stock para cada artículo del carrito.
    *   Se calcula el total económico del pedido.
    *   Se inserta un registro en la tabla `orders` con estado `pendiente` y los detalles en `order_items`.
    *   Se solicita a Stripe una sesión de checkout y se obtiene la URL de pago.
    *   Si todo es correcto, se hace `DB::commit()` y se retorna la URL de Stripe al frontend.
2.  **Confirmación de Pago (`confirmarPago`)**:
    *   Recibe el `session_id` de Stripe desde el frontend.
    *   Consulta el estado del pago directamente con la API de Stripe.
    *   Si el pago fue exitoso:
        *   Cambia el estado de la orden a `pagado` (o `completado`).
        *   Resta el stock físico del inventario.
        *   Vacía el carrito (`cart_items`) del usuario.
        *   Confirma la transacción.

---

## 🛡️ 4. Panel de Administración (`/admin`)

El Backend incluye un panel web clásico para que el equipo comercial controle el inventario.

### 4.1 Seguridad y Acceso
*   **Controlador**: `app/Http/Controllers/Admin/AuthController.php`
*   **Middleware**: `AdminAuth` (`app/Http/Middleware/AdminAuth.php`).
*   El acceso está limitado al email `adminProductos@gmail.com`.
*   Al iniciar sesión correctamente, se guarda la clave `admin_identificado` en la sesión local. El middleware protege todas las rutas del panel y redirige al formulario de login `/admin/login` si la variable de sesión no existe.

### 4.2 Lógica de CRUD y Sincronización
El controlador principal de la administración es `AdminProductoController`.
*   **Sincronización de Variantes**: En los formularios de creación y edición se utilizan checkboxes múltiples para seleccionar tallas y colores. El controlador hace uso del método `sync()` de Eloquent:
    ```php
    $producto->tallas()->sync($request->input('tallas', []));
    $producto->colores()->sync($request->input('colores', []));
    ```
*   **Carga y Eliminación de Fotos**: Las imágenes de productos se almacenan físicamente en `/storage/app/public/productos`. Al editar un producto y subir una nueva foto, la lógica elimina el archivo físico antiguo del disco usando `Storage::disk('public')->delete(...)` para evitar sobrecargar el espacio de almacenamiento del servidor.

---

## 🔔 5. Notificaciones de Stock (Back in Stock)

Cuando un producto con stock en 0 vuelve a estar disponible (`stock > 0`), el sistema envía automáticamente una notificación por correo electrónico a todos los usuarios que tienen dicho producto marcado en su lista de **Favoritos**.

### 5.1 Observador de Producto
Ubicación: `app/Observers/ProductoObserver.php`

El observador monitorea las actualizaciones del modelo `Producto`. Si se detecta un cambio en el campo `stock` y el valor original era `0`:
```php
public function updated(Producto $producto): void
{
    // Historial de precios (Ya implementado)
    if ($producto->wasChanged('precio')) {
        // Guarda registro en tabla de historial
    }

    // Alerta de reposición de stock
    if ($producto->wasChanged('stock') && $producto->getOriginal('stock') == 0 && $producto->stock > 0) {
        $this->notifyUsersBackInStock($producto);
    }
}
```

### 5.2 Lógica de Notificación Asíncrona (Colas/Queues)
Para evitar bloqueos o que la petición del administrador dé timeout si hay cientos de usuarios suscritos, los correos electrónicos se envían a través de colas:
```php
protected function notifyUsersBackInStock(Producto $producto)
{
    // Obtener favoritos de este producto incluyendo la relación del usuario
    $favorites = $producto->favorites()->with('user')->get();

    foreach ($favorites as $favorite) {
        $user = $favorite->user;
        // Se encola el correo asíncronamente
        Mail::to($user->email)->queue(new ProductBackInStockMail($producto));
    }
}
```
*   **Mailable**: `app/Mail/ProductBackInStockMail.php`
*   **Plantilla Markdown**: `resources/views/emails/products/back_in_stock.blade.php`
*   **Diseño**: Se hace uso del componente de cabecera de correo predeterminado del framework (`<x-mail::header>`), el cual carga de forma automática el logo institucional de **OutfitGo** configurado previamente en la carpeta de recursos.
