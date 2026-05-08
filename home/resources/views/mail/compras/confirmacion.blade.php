<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pedido - OutfitGo</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 0; color: #374151;">

    <div style="max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        
        <div style="background-color: #111827; padding: 30px 20px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 1px;">OutfitGo</h1>
        </div>

        <div style="padding: 30px 30px 20px;">
            <h2 style="margin: 0 0 15px; font-size: 20px; color: #111827;">¡Gracias por tu compra, {{ $pedido->user->name ?? $pedido->nombre }}!</h2>
            <p style="margin: 0; line-height: 1.6; color: #4b5563;">
                Estamos preparando tu pedido y te avisaremos tan pronto como salga de nuestro almacén. A continuación, encontrarás los detalles de tu compra.
            </p>
        </div>

        <div style="padding: 0 30px;">
            <div style="background-color: #f3f4f6; border-radius: 6px; padding: 20px; display: table; width: 100%; box-sizing: border-box; margin-bottom: 30px;">
                <div style="display: table-cell; width: 50%;">
                    <p style="margin: 0 0 5px; font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600;">Número de Pedido</p>
                    <p style="margin: 0; font-size: 16px; font-weight: 600; color: #111827;">#{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
                <div style="display: table-cell; width: 50%;">
                    <p style="margin: 0 0 5px; font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: 600;">Método de Pago</p>
                    <p style="margin: 0; font-size: 16px; font-weight: 600; color: #111827;">Tarjeta de Crédito</p>
                </div>
            </div>
        </div>

        <div style="padding: 0 30px 20px;">
            <h3 style="margin: 0 0 15px; font-size: 16px; color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px;">Artículos en tu pedido</h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                @php
                    $subtotal = 0;
                @endphp
                @foreach($pedido->orderItems as $item)
                    @php
                        $subtotal += $item->precio_unitario * $item->cantidad;
                    @endphp
                    <tr>
                        <td style="padding: 15px 0; border-bottom: 1px solid #f3f4f6;">
                            <p style="margin: 0 0 5px; font-weight: 600; color: #111827; font-size: 15px;">{{ $item->variante->producto->nombre ?? 'Producto' }}</p>
                            <p style="margin: 0; font-size: 13px; color: #6b7280;">
                                Cantidad: {{ $item->cantidad }} | 
                                Talla: {{ $item->variante->talla->nombre ?? 'N/A' }} | 
                                Color: {{ $item->variante->color->nombre ?? 'N/A' }}
                            </p>
                        </td>
                        <td style="padding: 15px 0; border-bottom: 1px solid #f3f4f6; text-align: right; vertical-align: top;">
                            <p style="margin: 0; font-weight: 600; color: #111827;">{{ number_format($item->precio_unitario * $item->cantidad, 2) }} €</p>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>

        <div style="padding: 0 30px 30px;">
            @php
                $gastosEnvio = $pedido->gastos_envio ?? 0;
                $descuento = $pedido->descuento ?? 0;
            @endphp
            <table style="width: 100%; border-collapse: collapse; margin-left: auto; max-width: 250px;">
                <tr>
                    <td style="padding: 5px 0; color: #6b7280; font-size: 14px;">Subtotal:</td>
                    <td style="padding: 5px 0; text-align: right; color: #111827; font-size: 14px;">{{ number_format($subtotal, 2) }} €</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #6b7280; font-size: 14px;">Gastos de envío:</td>
                    <td style="padding: 5px 0; text-align: right; color: #111827; font-size: 14px;">
                        @if($gastosEnvio == 0)
                            Gratis
                        @else
                            {{ number_format($gastosEnvio, 2) }} €
                        @endif
                    </td>
                </tr>
                @if($descuento > 0)
                <tr>
                    <td style="padding: 5px 0; color: #ef4444; font-size: 14px;">Descuento:</td>
                    <td style="padding: 5px 0; text-align: right; color: #ef4444; font-size: 14px;">-{{ number_format($descuento, 2) }} €</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 15px 0 5px; color: #111827; font-weight: 700; font-size: 16px; border-top: 1px solid #e5e7eb;">Total pagado:</td>
                    <td style="padding: 15px 0 5px; text-align: right; color: #111827; font-weight: 700; font-size: 18px; border-top: 1px solid #e5e7eb;">{{ number_format($pedido->total, 2) }} €</td>
                </tr>
            </table>
        </div>

        <div style="background-color: #f9fafb; padding: 30px; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
            <h3 style="margin: 0 0 10px; font-size: 16px; color: #111827;">Dirección de envío</h3>
            <p style="margin: 0; color: #4b5563; font-size: 14px; line-height: 1.6;">
                {{ $pedido->nombre }} {{ $pedido->apellidos }}<br>
                {{ $pedido->direccion }}<br>
                {{ $pedido->ciudad }}, {{ $pedido->provincia }} {{ $pedido->codigo_postal }}<br>
                España
            </p>
            @if($pedido->telefono)
            <p style="margin: 5px 0 0; color: #4b5563; font-size: 14px;">
                Teléfono: {{ $pedido->telefono }}
            </p>
            @endif
        </div>

        <div style="padding: 40px 30px; text-align: center;">
            <a href="#" style="background-color: #2563eb; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px; display: inline-block;">Hacer seguimiento de mi pedido</a>
        </div>

        <div style="background-color: #f3f4f6; padding: 20px; text-align: center;">
            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                © {{ date('Y') }} OutfitGo. Todos los derechos reservados.<br>
                Este es un correo automático, por favor no respondas.
            </p>
        </div>

    </div>
</body>
</html>