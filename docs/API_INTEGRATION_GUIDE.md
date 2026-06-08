# 📘 Guía de Integración de API para Frontend (Angular)

Bienvenido a la guía oficial de integración de la API RESTful de **OutfitGo** (Core Engine v2.0). Este documento unifica toda la información necesaria para consumir los servicios del backend desde la aplicación en Angular, cubriendo el catálogo de productos, el sistema de autenticación, el carrito de compras, el checkout con la pasarela de Stripe y las reseñas de portada.

---

## 🌐 1. Configuración Base

*   **URL Base (Producción/Staging)**: `https://outfitgo.duckdns.org/api` (o la IP asignada)
*   **URL Base (Local)**: `http://localhost:8000/api`
*   **CORS**: Configurado para aceptar peticiones desde cualquier origen (`allowed_origins => ['*']`), por lo que no experimentarás bloqueos en entorno local (`localhost:4200`) ni en staging.
*   **Autenticación**:
    *   **Pública**: Las rutas del catálogo de productos y testimonios de portada no requieren autenticación.
    *   **Privada (Sanctum)**: El carrito, los pedidos, el perfil de usuario y la confirmación de compras requieren un Token de Acceso en las cabeceras HTTP.

### Cabecera de Autenticación Obligatoria
Para todas las rutas protegidas, tu cliente Angular debe adjuntar el token de la siguiente manera:
```http
Authorization: Bearer {tu_access_token}
```

---

## 🛍️ 2. Catálogo de Productos (Rutas Públicas)

La API utiliza **Eager Loading** para retornar el producto con todas sus relaciones asociadas en una sola llamada, evitando peticiones adicionales.

### 2.1 Obtener Catálogo Paginado
*   **Método**: `GET`
*   **Ruta**: `/api/productos`
*   **Descripción**: Retorna una lista paginada de productos (12 items por página) ordenados por fecha de creación.

#### Parámetros de Filtrado Soportados (Query Params)
Puedes concatenar y combinar los siguientes parámetros en la URL:

| Parámetro | Ejemplo | Descripción |
| :--- | :--- | :--- |
| `q` | `?q=zapatillas` | Búsqueda por texto (nombre, descripción o marca). |
| `publico` | `?publico=infantil` | Filtra por segmento: `adulto`, `infantil`, `unisex`. |
| `marca_id` | `?marca_id=2` | Filtra por el ID de la marca. |
| `categoria_id` | `?categoria_id=5` | Filtra por el ID de la categoría. |
| `talla` | `?talla=M,L` | Muestra productos con al menos una de las tallas indicadas. |
| `color` | `?color=Azul,Rojo` | Filtra por el nombre del color. |
| `precio_min` | `?precio_min=20` | Precio mínimo del producto. |
| `precio_max` | `?precio_max=150` | Precio máximo del producto. |

### 2.2 Obtener Detalle de un Producto
*   **Método**: `GET`
*   **Ruta**: `/api/productos/{slug}`
*   **Ejemplo**: `/api/productos/zapatillas-runner-nike`

### 2.3 Obtener Productos Recomendados (Sugerencias)
*   **Método**: `GET`
*   **Ruta**: `/api/productos/{id}/recomendados`

### 2.4 Obtener Historial de Precios
*   **Método**: `GET`
*   **Ruta**: `/api/productos/{id}/historial`
*   **Descripción**: Retorna las variaciones de precio históricas del producto, ideal para construir gráficos comparativos de evolución de precio en el frontend.

---

## 🔐 3. Autenticación de Usuarios (Rutas Públicas)

### 3.1 Registro de Usuario
*   **Método**: `POST`
*   **Ruta**: `/api/register`
*   **Body (JSON)**:
```json
{
  "name": "Juan Perez",
  "email": "juan@example.com",
  "password": "Password123!",
  "password_confirmation": "Password123!",
  "direccion": "Calle de la Moda 123",
  "ciudad": "Madrid",
  "provincia": "Madrid",
  "codigo_postal": "28001",
  "telefono": "600123456"
}
```
> *Nota: Los campos de dirección son opcionales durante el registro, pero son recomendados para rellenar de forma automática el formulario de compra posterior.*

### 3.2 Inicio de Sesión
*   **Método**: `POST`
*   **Ruta**: `/api/login`
*   **Body (JSON)**:
```json
{
  "email": "juan@example.com",
  "password": "Password123!"
}
```
*   **Respuesta Exitosa (`200 OK`)**:
```json
{
  "message": "Inicio de sesión exitoso",
  "user": {
      "id": 1,
      "name": "Juan Perez",
      "email": "juan@example.com"
  },
  "access_token": "1|abcdef1234567890...",
  "token_type": "Bearer"
}
```
> *Acción en Angular: Almacenar `access_token` en local storage o state manager y adjuntarlo mediante un HTTP Interceptor a las rutas privadas.*

---

## 🛒 4. Carrito de Compras (Rutas Privadas 🔒)

### 4.1 Obtener items del Carrito
*   **Método**: `GET`
*   **Ruta**: `/api/cart`
*   **Respuesta Exitosa (`200 OK`)**:
```json
{
  "data": [
    {
      "id": 15,
      "cantidad": 2,
      "subtotal": 120.00,
      "producto": {
        "id": 5,
        "nombre": "Camiseta Deportiva",
        "slug": "camiseta-deportiva",
        "precio": "60.00",
        "url_imagen_principal": "https://...",
        "stock": 10
      }
    }
  ]
}
```

### 4.2 Añadir Producto al Carrito
*   **Método**: `POST`
*   **Ruta**: `/api/cart`
*   **Body (JSON)**:
```json
{
  "producto_id": 5,
  "cantidad": 1
}
```

### 4.3 Eliminar Producto del Carrito
*   **Método**: `DELETE`
*   **Ruta**: `/api/cart/{id_del_cart_item}`
*   **Ejemplo**: `/api/cart/15`

---

## 💳 5. Checkout con Stripe (Rutas Privadas 🔒)

El proceso de compra en OutfitGo consta de **dos pasos obligatorios** para garantizar que los pagos se procesan de forma segura a través de los servidores de Stripe.

### 5.1 PASO 1: Iniciar Pago y Redirección
*   **Método**: `POST`
*   **Ruta**: `/api/checkout/iniciar`
*   **Descripción**: Verifica el stock del carrito, crea el pedido temporal con estado `pendiente` y genera un link de pago único de Stripe.
*   **Body (JSON)**:
```json
{
  "nombre": "Juan",
  "apellidos": "Pérez García",
  "telefono": "600123456",
  "direccion": "Calle Falsa 123, 3ºB",
  "ciudad": "Madrid",
  "provincia": "Madrid",
  "codigo_postal": "28080",
  "notas": "Dejar en conserjería si no estoy"
}
```
*   **Respuesta Exitosa (`200 OK`)**:
```json
{
  "url": "https://checkout.stripe.com/pay/cs_test_a1b2c3d4..."
}
```
> *Acción en Angular: Al recibir la URL de Stripe, redirigir al usuario fuera de la SPA usando `window.location.href = respuesta.url`.*

### 5.2 PASO 2: Confirmar Pago en Staging/Retorno
Una vez que el usuario paga, Stripe lo redirige de vuelta a Angular (ej. a la ruta `/checkout/success?session_id=cs_test...`).
*   **Método**: `POST`
*   **Ruta**: `/api/checkout/confirmar`
*   **Descripción**: Valida el pago con Stripe, cambia el estado del pedido a `pagado`, vacía el carrito del usuario y resta el stock de los productos.
*   **Body (JSON)**:
```json
{
  "session_id": "cs_test_a1b2c3d4..."
}
```
*   **Respuesta Exitosa (`200 OK`)**:
```json
{
  "message": "¡Pago verificado y compra completada con éxito!",
  "order": {
    "id": 101,
    "total": "120.00",
    "estado": "pagado"
  }
}
```
> *Acción en Angular: Mostrar un Spinner de carga mientras responde este endpoint, y no mostrar el mensaje de éxito final hasta recibir el 200 OK.*

---

## 📦 6. Historial de Pedidos (Rutas Privadas 🔒)

### 6.1 Listar Pedidos del Usuario
*   **Método**: `GET`
*   **Ruta**: `/api/pedidos`
*   **Respuesta Exitosa (`200 OK`)**:
```json
{
  "message": "Historial de pedidos recuperado con éxito.",
  "pedidos": [
    {
      "id": 101,
      "user_id": 1,
      "total": "120.00",
      "estado": "pagado",
      "created_at": "2026-03-10T12:00:00.000000Z",
      "updated_at": "2026-03-10T12:00:00.000000Z"
    }
  ]
}
```

### 6.2 Cancelar un Pedido
*   **Método**: `POST`
*   **Ruta**: `/api/pedidos/{id}/cancelar`

### 6.3 Solicitar Devolución de un Pedido
*   **Método**: `POST`
*   **Ruta**: `/api/pedidos/{id}/devolver`

---

## 💬 7. Testimonios / Reseñas de Portada (Ruta Pública)

*   **Método**: `GET`
*   **Ruta**: `/api/resenas-pagina`
*   **Descripción**: Devuelve hasta 3 testimonios de clientes que el administrador ha marcado para mostrar en la portada principal de la web, ordenados de más recientes a más antiguos.
*   **Respuesta Exitosa (`200 OK`)**:
```json
[
  {
    "id": 12,
    "user_id": 5,
    "puntuacion": 5,
    "comentario": "¡Una experiencia de compra excelente, la ropa llegó rapidísimo!",
    "visible_en_portada": true,
    "created_at": "2026-04-13T10:00:00.000000Z",
    "user": {
      "id": 5,
      "name": "Ana"
    }
  }
]
```

---

## ⚠️ 8. Reglas Críticas de Negocio para la Interfaz (UI)

1.  **Longitud Obligatoria de Descripciones**:
    *   La base de datos garantiza que toda `descripcion` de producto posee estrictamente entre **300 y 500 caracteres**.
    *   **Tu maquetación en Angular DEBE prever textos largos** en tarjetas y vistas de catálogo usando truncados con CSS (`line-clamp`, `text-overflow`), paneles colapsables o tooltips.
2.  **Visualización de Tallas**:
    *   La API devuelve las tallas disponibles en el array `tallas`.
    *   Para productos de la categoría `infantil` (ej: `publico = "infantil"`), las tallas serán de edad (ej. "4Y", "6Y"). Para adultos, serán tallas clásicas ("S", "M") o numéricas. El frontend debe limitarse a renderizar los strings tal cual vengan en el array.
3.  **Protección de Stock**:
    *   El stock del producto **NO se reserva** mientras esté en el carrito del cliente ni cuando esté en la pasarela de Stripe. El stock se deduce definitivamente en el **PASO 2 (Confirmar Pago)**. Si dos clientes pagan por la última unidad simultáneamente, la API rechazará una de las confirmaciones con un código HTTP `422` y Stripe reembolsará el cobro.
4.  **Manejo de Errores Comunes**:
    *   `401 Unauthorized`: Token de sesión inválido o expirado. El frontend debe limpiar `localStorage` y redirigir al login.
    *   `422 Unprocessable Entity`: Error de validación (campos del checkout vacíos o stock agotado). Mostrar toast con el error JSON devuelto por la API.
