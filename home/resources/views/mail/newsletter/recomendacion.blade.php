<!DOCTYPE html>
<html>

<body style="font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f9fafb;">
    <div
        style="max-width: 600px; margin: 40px auto; padding: 30px; border: 1px solid #e5e7eb; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">

        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #4f46e5; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.025em;">OutfitGo
            </h1>
            <p style="color: #6b7280; font-size: 14px; margin-top: 5px;">Tu estilo a tu medida</p>
        </div>

        <h2 style="color: #1f2937; font-size: 20px; font-weight: 700; margin-top: 0; margin-bottom: 10px;">¡Hola,
            {{ $user->name }}!
        </h2>

        <p style="font-size: 16px; line-height: 1.5; color: #4b5563; margin-top: 0; margin-bottom: 25px;">
            Como estás suscrito a nuestra newsletter, queremos recomendarte algunas prendas exclusivas de nuestra nueva
            colección que creemos que te encantarán y combinarán genial con tu estilo:
        </p>

        <div style="margin-bottom: 30px;">
            @foreach($recomendaciones as $prod)
                <div
                    style="display: flex; align-items: center; padding: 15px; border: 1px solid #f3f4f6; border-radius: 8px; margin-bottom: 15px; background-color: #f9fafb;">
                    <img src="{{ $prod->url_imagen_principal }}" alt="{{ $prod->nombre }}"
                        style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; margin-right: 15px;" />
                    <div style="flex-grow: 1;">
                        <h3 style="margin: 0 0 5px 0; font-size: 16px; color: #111827;">{{ $prod->nombre }}</h3>
                        <p style="margin: 0; font-size: 14px; color: #4f46e5; font-weight: bold;">{{ $prod->precio }} €</p>
                    </div>
                    <div>
                        <a href="{{ $frontendUrl }}/producto/{{ $prod->slug }}"
                            style="display: inline-block; padding: 8px 12px; background-color: #4f46e5; color: #ffffff; text-decoration: none; font-size: 12px; font-weight: bold; border-radius: 6px;">Ver
                            prenda</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ $frontendUrl }}"
                style="display: inline-block; padding: 12px 24px; background-color: #111827; color: #ffffff; text-decoration: none; font-size: 14px; font-weight: bold; border-radius: 8px;">Visitar
                la tienda</a>
        </div>

        <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 30px 0 15px 0;">
        <p style="font-size: 11px; color: #9ca3af; text-align: center; margin: 0;">
            Recibes este correo porque te suscribiste a la newsletter de OutfitGo, Muchas gracias!!
        </p>
    </div>
</body>

</html>