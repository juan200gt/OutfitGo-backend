<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Marca;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Talla;
use App\Models\Color;
use App\Models\Cupon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
// 1. Crear usuario administrador General
        User::updateOrCreate(
            ['email' => 'admin@outfitgo.com'], 
            [                                 
                'name' => 'Admin General',
                'password' => Hash::make('1234'), 
                'rol' => 'admin'
            ]
        );

        // Creo Usuario Administrador de productos
        User::updateOrCreate(
            ['email' => 'adminProductos@gmail.com'],
            [
                'name' => 'Admin Productos',
                'password' => Hash::make('productos123'),
                'rol' => 'admin_productos'
            ]
        );

        // Creo Usuario Administrador de Usuarios
        User::updateOrCreate(
            ['email' => 'adminUsuarios@gmail.com'],
            [
                'name' => 'Admin Usuarios',
                'password' => Hash::make('usuarios123'),
                'rol' => 'admin_usuarios'
            ]
        );

        // Creo Usuario Cliente
        User::updateOrCreate(
            ['email' => 'cliente@gmail.com'],
            [
                'name' => 'Usuario Cliente',
                'password' => Hash::make('cliente123'),
                'rol' => 'cliente'
            ]
        );

        User::updateOrCreate(
            ['email' => 'verificado@gmail.com'],
            [
                'name' => 'Usuario Verificado',
                'password' => Hash::make('cliente123'),
                'rol' => 'cliente',
                'email_verified_at' => now(),
            ]
        );
// 2. Crear Categorías Reales
        $categorias = [
            'Zapatillas' => Categoria::updateOrCreate(['slug' => 'zapatillas'], ['nombre' => 'Zapatillas']),
            'Camisetas' => Categoria::updateOrCreate(['slug' => 'camisetas'], ['nombre' => 'Camisetas']),
            'Sudaderas' => Categoria::updateOrCreate(['slug' => 'sudaderas'], ['nombre' => 'Sudaderas']),
            'Pantalones' => Categoria::updateOrCreate(['slug' => 'pantalones'], ['nombre' => 'Pantalones']),
            'Chaquetas' => Categoria::updateOrCreate(['slug' => 'chaquetas'], ['nombre' => 'Chaquetas']),
            'Vestidos' => Categoria::updateOrCreate(['slug' => 'vestidos'], ['nombre' => 'Vestidos']),
            'Accesorios' => Categoria::updateOrCreate(['slug' => 'accesorios'], ['nombre' => 'Accesorios']),
        ];

        // 3. Crear Marcas Reales
        $marcas = [
            'Nike' => Marca::updateOrCreate(['slug' => 'nike'], ['nombre' => 'Nike', 'url_logo' => 'https://upload.wikimedia.org/wikipedia/commons/a/a6/Logo_NIKE.svg']),
            'Adidas' => Marca::updateOrCreate(['slug' => 'adidas'], ['nombre' => 'Adidas', 'url_logo' => 'https://upload.wikimedia.org/wikipedia/commons/2/20/Adidas_Logo.svg']),
            'Puma' => Marca::updateOrCreate(['slug' => 'puma'], ['nombre' => 'Puma', 'url_logo' => 'https://upload.wikimedia.org/wikipedia/commons/8/88/Puma_Logo.svg']),
            'Zara' => Marca::updateOrCreate(['slug' => 'zara'], ['nombre' => 'Zara', 'url_logo' => 'https://upload.wikimedia.org/wikipedia/commons/f/fd/Zara_Logo.svg']),
            'Levis' => Marca::updateOrCreate(['slug' => 'levis'], ['nombre' => 'Levis', 'url_logo' => 'https://upload.wikimedia.org/wikipedia/commons/4/41/Levi%27s_logo.svg']),
            'New Balance' => Marca::updateOrCreate(['slug' => 'new-balance'], ['nombre' => 'New Balance', 'url_logo' => 'https://upload.wikimedia.org/wikipedia/commons/e/ea/New_Balance_logo.svg']),
            'Vans' => Marca::updateOrCreate(['slug' => 'vans'], ['nombre' => 'Vans', 'url_logo' => 'https://upload.wikimedia.org/wikipedia/commons/9/9d/Vans_logo.svg']),
        ];
        
        // 4. Crear Tallas Reales
        $tallasData = [
            'Adulto' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            'Infantil' => ['4Y', '6Y', '8Y', '10Y', '12Y', '14Y'],
            'Calzado' => ['36', '37', '38', '39', '40', '41', '42', '43', '44', '45']
        ];

        $tallasObjects = [
            'Adulto' => [],
            'Infantil' => [],
            'Calzado' => []
        ];

        foreach ($tallasData as $tipo => $tallas) {
            foreach ($tallas as $t) {
                // Usamos firstOrCreate para evitar duplicados si algún nombre coincide
                $tallasObjects[$tipo][$t] = Talla::firstOrCreate(['nombre' => $t]);
            }
        }

// 5. Crear Colores
        $colores = [
            'Negro' => Color::updateOrCreate(['nombre' => 'Negro'], ['hex_code' => '#000000']),
            'Blanco' => Color::updateOrCreate(['nombre' => 'Blanco'], ['hex_code' => '#FFFFFF']),
            'Gris' => Color::updateOrCreate(['nombre' => 'Gris'], ['hex_code' => '#808080']),
            'Azul Marino' => Color::updateOrCreate(['nombre' => 'Azul Marino'], ['hex_code' => '#000080']),
            'Rojo' => Color::updateOrCreate(['nombre' => 'Rojo'], ['hex_code' => '#FF0000']),
            'Beige' => Color::updateOrCreate(['nombre' => 'Beige'], ['hex_code' => '#F5F5DC']),
            'Verde Oliva' => Color::updateOrCreate(['nombre' => 'Verde Oliva'], ['hex_code' => '#556B2F']),
            'Rosa' => Color::updateOrCreate(['nombre' => 'Rosa'], ['hex_code' => '#FFC0CB']),
        ];

        // 6. CREAR PRODUCTOS REALISTAS COMPLETOS
        $makeSlug = function ($string) {
            return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
        };

        $productosReales = [
            [
                'nombre' => 'Zapatillas Nike Air Force 1 Clásicas Blancas',
                'descripcion' => 'Vive la leyenda con las icónicas zapatillas Nike Air Force 1. Un diseño clásico e impecable completamente en blanco que combina con absolutamente cualquier conjunto de ropa de tu armario. Están confeccionadas con cuero genuino de alta durabilidad, costuras cosidas meticulosamente y la emblemática amortiguación Nike Air oculta en la suela gruesa, garantizando una comodidad insuperable en cada uno de tus pasos diarios. Perfectas para un estilo casual, urbano y atemporal.',
                'publico' => 'hombre',
                'url_imagen_principal' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&q=80',
                'precio' => 119.99,
                'stock' => 45,
                'marca' => $marcas['Nike']->id,
                'categoria' => $categorias['Zapatillas']->id,
                'colores' => [$colores['Blanco']->id],
                'tallas' => [$tallasObjects['Calzado']['38']->id, $tallasObjects['Calzado']['39']->id, $tallasObjects['Calzado']['40']->id, $tallasObjects['Calzado']['41']->id, $tallasObjects['Calzado']['42']->id, $tallasObjects['Calzado']['43']->id]
            ],
            [
                'nombre' => 'Sudadera con Capucha Adidas Originals Trefoil',
                'descripcion' => 'Acurrúcate con estilo gracias a esta sudadera clásica de Adidas Originals. Destaca por exhibir el icónico y gigantesco logotipo del Trifolio (Trefoil) estampado en pleno pecho, convirtiéndose en el centro de todas las miradas. Fabricada con una rica mezcla de felpa y algodón de origen sostenible que acaricia la piel. Dispone de una práctica capucha ajustable con cordón grueso, además de un amplio bolsillo de canguro frontal para mantener las manos calientes en las frías mañanas de invierno urbano.',
                'publico' => 'hombre',
                'url_imagen_principal' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=800&q=80',
                'precio' => 64.95,
                'stock' => 30,
                'marca' => $marcas['Adidas']->id,
                'categoria' => $categorias['Sudaderas']->id,
                'colores' => [$colores['Negro']->id, $colores['Gris']->id],
                'tallas' => [$tallasObjects['Adulto']['S']->id, $tallasObjects['Adulto']['M']->id, $tallasObjects['Adulto']['L']->id, $tallasObjects['Adulto']['XL']->id]
            ],
            [
                'nombre' => 'Pantalón Vaquero Levi\'s 501 Original Fit',
                'descripcion' => 'La prenda que lo empezó absolutamente todo hace más de un siglo. Los genuinos e irremplazables vaqueros Levi\'s número 501 Original Fit representan el plano base a partir del cual todos los demás jeans han sido confeccionados en la historia moderna. Corte recto indiscutible en la cadera y el muslo, con la clásica y mundialmente conocida bragueta de botones remachados. Una tela denim de peso pesado, súper resistente que envejece con muchísima personalidad adaptándose a tu forma corporal única con el tiempo.',
                'publico' => 'hombre',
                'url_imagen_principal' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=800&q=80',
                'precio' => 89.00,
                'stock' => 80,
                'marca' => $marcas['Levis']->id,
                'categoria' => $categorias['Pantalones']->id,
                'colores' => [$colores['Azul Marino']->id, $colores['Negro']->id],
                'tallas' => [$tallasObjects['Adulto']['M']->id, $tallasObjects['Adulto']['L']->id, $tallasObjects['Adulto']['XL']->id, $tallasObjects['Adulto']['XXL']->id]
            ],
            [
                'nombre' => 'Camiseta Zara Kids Estampado Dinosaurios',
                'descripcion' => 'Despierta la imaginación prehistórica de los más pequeños con esta divertida camiseta de manga corta perteneciente a nuestra colección exclusiva Zara Kids. Presenta un vibrante, colorido y muy detallado estampado gráfico múltiple protagonizado por simpáticos dinosaurios repartidos por todo el torso de la prenda. Está cuidadosamente confeccionada utilizando punto de puro algodón cien por ciento orgánico certificado, completamente suave, ligero y totalmente transpirable. Cuello elástico que no agobia y corte súper libre.',
                'publico' => 'infantil',
                'url_imagen_principal' => 'https://images.unsplash.com/photo-1519241047957-be31d7379a5d?w=800&q=80',
                'precio' => 12.99,
                'stock' => 120,
                'marca' => $marcas['Zara']->id,
                'categoria' => $categorias['Camisetas']->id,
                'colores' => [$colores['Blanco']->id, $colores['Verde Oliva']->id],
                'tallas' => [$tallasObjects['Infantil']['4Y']->id, $tallasObjects['Infantil']['6Y']->id, $tallasObjects['Infantil']['8Y']->id]
            ],
            [
                'nombre' => 'Zapatillas de Running Puma Velocity Nitro 2',
                'descripcion' => 'Experimenta la sensación real de flotar sobre el asfalto cuando sales a correr utilizando las zapatillas de alto rendimiento Puma Velocity Nitro 2. Este excepcional calzado deportivo neutro integra en toda la entresuela la innovadora espuma inyectada con gas nitrógeno patentada por la marca, garantizando de este modo un retorno de energía supremo, una amortiguación absurdamente suave y sobre todo una ligereza incomparable. El exterior de malla súper técnica envuelve cuidadosamente todo tu pie proporcionando increíble sujeción.',
                'publico' => 'hombre',
                'url_imagen_principal' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=800&q=80',
                'precio' => 135.50,
                'stock' => 25,
                'marca' => $marcas['Puma']->id,
                'categoria' => $categorias['Zapatillas']->id,
                'colores' => [$colores['Rojo']->id, $colores['Negro']->id],
                'tallas' => [$tallasObjects['Calzado']['40']->id, $tallasObjects['Calzado']['41']->id, $tallasObjects['Calzado']['42']->id, $tallasObjects['Calzado']['43']->id, $tallasObjects['Calzado']['44']->id]
            ],
            [
                'nombre' => 'Zapatillas Vans Old Skool Classic Skate',
                'descripcion' => 'Liderando ininterrumpidamente el estilo callejero urbano y abrazando por completo la genuina cultura de los deportes extremos de California encontramos a las maravillosas zapatillas Vans Old Skool. Primer calzado legendario en lucir públicamente la inconfundible franja lateral ondulada Sidestripe de la compañía. Se han fabricado minuciosamente con fuertes empeines mezclando tela de lona súper duradera e inserciones protectoras frontales de gamuza auténtica. Suela inyectada de caucho puro con la textura patentada Waffle.',
                'publico' => 'mujer',
                'url_imagen_principal' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?w=800&q=80',
                'precio' => 75.00,
                'stock' => 50,
                'marca' => $marcas['Vans']->id,
                'categoria' => $categorias['Zapatillas']->id,
                'colores' => [$colores['Negro']->id, $colores['Blanco']->id],
                'tallas' => [$tallasObjects['Calzado']['37']->id, $tallasObjects['Calzado']['38']->id, $tallasObjects['Calzado']['39']->id, $tallasObjects['Calzado']['40']->id, $tallasObjects['Calzado']['41']->id]
            ],
            [
                'nombre' => 'Vestido Zara Mujer Textura Midi Fluido',
                'descripcion' => 'Deslumbra elegantemente y acapara todas las atenciones apostando enormemente por este sofisticado y polivalente vestido de corte midi perteneciente al catálogo Zara Woman. Presenta una tela meticulosamente hilada que destaca sobre todo por un maravilloso tejido muy fluido y vaporoso con sutiles pero efectivas micro texturas. Dispone de un favorecedor escote central terminado delicadamente en forma de letra pico y delicados tirantes ultra finos que permiten lucir abiertamente los hombros durante largas cenas.',
                'publico' => 'mujer',
                'url_imagen_principal' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=800&q=80',
                'precio' => 39.95,
                'stock' => 40,
                'marca' => $marcas['Zara']->id,
                'categoria' => $categorias['Vestidos']->id,
                'colores' => [$colores['Beige']->id, $colores['Negro']->id],
                'tallas' => [$tallasObjects['Adulto']['XS']->id, $tallasObjects['Adulto']['S']->id, $tallasObjects['Adulto']['M']->id]
            ],
            [
                'nombre' => 'Chaqueta Deportiva Infantil Nike Windrunner',
                'descripcion' => 'Prepara meticulosamente y equipa plenamente a tus queridos hijos para poder afrontar sin ningún tipo de miedo aquellos días más fríos y húmedos gracias a esta indispensable chaqueta tipo cortavientos infantil modelo Windrunner lanzada por Nike. Cuenta históricamente con el mítico e inconfundible diseño principal en forma de V grande plasmado sobre el pecho. Increíblemente ligera, repelente del agua, dispone forro cómodo de malla interior. Cremallera frontal centralizada integral para ponérsela y quitársela.',
                'publico' => 'infantil',
                'url_imagen_principal' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=800&q=80',
                'precio' => 54.90,
                'stock' => 20,
                'marca' => $marcas['Nike']->id,
                'categoria' => $categorias['Chaquetas']->id,
                'colores' => [$colores['Azul Marino']->id, $colores['Rojo']->id],
                'tallas' => [$tallasObjects['Infantil']['8Y']->id, $tallasObjects['Infantil']['10Y']->id, $tallasObjects['Infantil']['12Y']->id]
            ],
            [
                'nombre' => 'Zapatillas Deportivas New Balance 574 Core',
                'descripcion' => 'Abraza la silueta inconfundible, inigualable y probablemente la más importante creada jamás en toda la extensísima historia cronológica de zapatillas New Balance, hablamos nada menos que del magistral modelo 574 Core. Este zapato icónico de aspecto retro fusiona exitosamente de manera experta el confort con unas líneas francamente limpias y atractivas. Empeine transpirable cuidadosamente mezclado de gamuza real premium con ventilación técnica de malla. Entresuela dotada de soporte especial de espuma EVA encapsulada.',
                'publico' => 'hombre',
                'url_imagen_principal' => 'https://images.unsplash.com/photo-1539185441755-769473a23570?w=800&q=80',
                'precio' => 95.00,
                'stock' => 60,
                'marca' => $marcas['New Balance']->id,
                'categoria' => $categorias['Zapatillas']->id,
                'colores' => [$colores['Gris']->id, $colores['Azul Marino']->id],
                'tallas' => [$tallasObjects['Calzado']['39']->id, $tallasObjects['Calzado']['40']->id, $tallasObjects['Calzado']['41']->id, $tallasObjects['Calzado']['42']->id, $tallasObjects['Calzado']['43']->id, $tallasObjects['Calzado']['44']->id]
            ],
            [
                'nombre' => 'Gorra Trucker Unisex Adidas Originals',
                'descripcion' => 'Remata magistralmente tu conjunto de ropa de estilo puramente callejero y protege cómodamente frente al sol tus propios ojos introduciendo este espléndido accesorio. Es ni más ni menos que una sensacional gorra tipo trucker perteneciente íntegramente a Adidas Originals. Presenta majestuosamente un diseño superior en el que predomina un panel delantero muy robusto luciendo el característico e icónico logotipo sobredimensionado del trébol. Cuenta con toda la zona de la mitad posterior confeccionada en fina malla.',
                'publico' => 'hombre',
                'url_imagen_principal' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=800&q=80',
                'precio' => 22.50,
                'stock' => 150,
                'marca' => $marcas['Adidas']->id,
                'categoria' => $categorias['Accesorios']->id,
                'colores' => [$colores['Blanco']->id, $colores['Azul Marino']->id],
                'tallas' => [$tallasObjects['Adulto']['M']->id] // Talla única
            ]
        ];

// 7. Insertar los productos y SUS VARIANTES
        foreach ($productosReales as $pData) {
            $producto = Producto::updateOrCreate(
                ['slug' => $makeSlug($pData['nombre'])], // Busca por slug
                [
                    'nombre' => $pData['nombre'],
                    'descripcion' => $pData['descripcion'],
                    'publico' => $pData['publico'],
                    'url_imagen_principal' => $pData['url_imagen_principal'],
                    'precio' => $pData['precio'],
                    'stock' => $pData['stock'],
                    'marca_id' => $pData['marca'],
                    'categoria_id' => $pData['categoria'],
                ]
            );

            // Sincronizar relaciones pivote
            $producto->tallas()->sync($pData['tallas']);
            $producto->colores()->sync($pData['colores']);

            // 🌟 CREAR LAS VARIANTES (Evitando duplicados con updateOrCreate)
            foreach ($pData['tallas'] as $tallaId) {
                foreach ($pData['colores'] as $colorId) {

                    // Truco matemático: 1 de cada 4 variantes tendrá stock 0 (Agotado), 
                    // el resto tendrá un número aleatorio muy dispar entre 1 y 150.
                    $stockDemo = (rand(1, 4) === 1) ? 0 : rand(1, 150);

                    ProductoVariante::updateOrCreate(
                        [
                            'producto_id' => $producto->id,
                            'talla_id' => $tallaId,
                            'color_id' => $colorId,
                        ],
                        [
                            'stock' => $stockDemo
                        ]
                    );
                }
            }

            // Generar 3 imágenes secundarias (SOLO si no tiene ya imágenes creadas)
            if ($producto->imagenes()->count() === 0) {
                for ($i = 1; $i <= 3; $i++) {
                    $producto->imagenes()->create([
                        'url_imagen' => 'https://picsum.photos/seed/' . $producto->id . $i . '/800/800'
                    ]);
                }
            }
        }

        // AQUÍ ESTÁ TU LISTA DE CLONES RECUPERADA
        $clonesData = [
            [
                'base' => $productosReales[0],
                'nombre' => 'Zapatillas Nike Air Force 1 Clásicas Negras',
                'descripcion' => 'Desata absolutamente todo tu enorme potencial callejero vistiendo las legendarias e icónicas zapatillas Nike Air Force 1. Un diseño poderoso, agresivo y sumamente limpio enteramente tintado en un muy elegante y sobrio negro mate capaz de resistirse ferozmente a la abrumadora suciedad urbana. Confeccionadas partiendo de un resistente e inmaculado cuero premium liso, mantienen perfectamente oculta la sensacional tecnología especial Nike Air debajo del enorme grosor protector que nos aporta la gran suela de goma.',
                'colores' => [$colores['Negro']->id]
            ],
            [
                'base' => $productosReales[1],
                'nombre' => 'Sudadera con Capucha Adidas Infantil',
                'descripcion' => 'Mantén enormemente abrigados y repletos de comodidades modernas a tus pequeñines preferidos durante el gélido invierno confiando plenamente en esta pequeña gran iteración de nuestra fantástica sudadera más famosa y aplaudida. Una pieza innegociable nacida desde Adidas Originals luciendo el llamativo e inmenso trébol mítico frontal. Construida priorizando siempre emplear los algodones naturales completamente amigables con el medio. Su gigantesco bolsillo de estilo canguro mantendrá escondidos esos juguetes.',
                'publico' => 'infantil',
                'tallas' => [$tallasObjects['Infantil']['8Y']->id, $tallasObjects['Infantil']['10Y']->id, $tallasObjects['Infantil']['12Y']->id]
            ],
            [
                'base' => $productosReales[3],
                'nombre' => 'Camiseta Zara Kids Estampado Espacial',
                'descripcion' => 'Prepara las maletas e invita urgentemente a tu maravilloso hijo o fantástica hija a surcar de forma emocionante toda y cada una de las grandes estrellas del firmamento cósmico gracias a la deslumbrante magia inherente a esta interesantísima camiseta manga corta de Zara Kids. Su colorido y llamativo estampado espacial reluce repletito de hermosísimos planetas volanderos y rápidos cohetes. Fue minuciosamente elaborada escogiendo únicamente el mejor y más dócil poliéster fresco diseñado para correr y sudar.',
                'colores' => [$colores['Azul Marino']->id, $colores['Gris']->id]
            ],
            [
                'base' => $productosReales[4],
                'nombre' => 'Zapatillas Puma Running Softride',
                'descripcion' => 'Eleva enormemente la mismísima calidad de todas y cada una de tus maratones recurrentes utilizando la excelsa tecnología insertada cuidadosamente dentro de estas espectaculares zapatillas neutras provistas por la firma internacional Puma y bautizadas como Softride. Integran asombrosamente una capa inferior inmensa rellena enteramente de una espuma plástica súper reactiva patentada de altísimo confort y rendimiento superior para no tener ninguna fatiga plantar. Destacan notablemente por su peso.',
                'colores' => [$colores['Blanco']->id, $colores['Rosa']->id]
            ],
            [
                'base' => $productosReales[8],
                'nombre' => 'Zapatillas Deportivas New Balance 990',
                'descripcion' => 'Asombra drásticamente al gigantesco mundo exterior presumiendo con soberbia de lucir libremente el estandarte que representa con abrumadora contundencia el linaje americano, esto es sin duda alguna calzar alegremente las New Balance modelo número 990 clásico indiscutible. Zapatillas míticas provistas orgullosamente de las líneas estéticas más atemporales disponibles hoy por hoy en el competitivo mercado actual del running. Destapan grandes y múltiples inserciones generosas compuestas con rica gamuza de cerdo.',
                'publico' => 'mujer',
                'colores' => [$colores['Azul Marino']->id, $colores['Beige']->id]
            ]
        ];

        // 8. Insertar los clones y SUS VARIANTES
        foreach ($clonesData as $clone) {
            $base = $clone['base'];
            
            $producto = Producto::updateOrCreate(
                ['slug' => $makeSlug($clone['nombre'])],
                [
                    'nombre' => $clone['nombre'],
                    'descripcion' => $clone['descripcion'],
                    'publico' => isset($clone['publico']) ? $clone['publico'] : $base['publico'],
                    'url_imagen_principal' => $base['url_imagen_principal'],
                    'precio' => $base['precio'],
                    'stock' => $base['stock'],
                    'marca_id' => $base['marca'],
                    'categoria_id' => $base['categoria'],
                ]
            );

            $tallasAUsar = isset($clone['tallas']) ? $clone['tallas'] : $base['tallas'];
            $coloresAUsar = isset($clone['colores']) ? $clone['colores'] : $base['colores'];

            // Sincronizar relaciones pivote
            $producto->tallas()->sync($tallasAUsar);
            $producto->colores()->sync($coloresAUsar);

            // 🌟 CREAR LAS VARIANTES DE STOCK PARA LOS CLONES
            foreach ($tallasAUsar as $tallaId) {
                foreach ($coloresAUsar as $colorId) {
                    ProductoVariante::updateOrCreate(
                        [
                            'producto_id' => $producto->id,
                            'talla_id' => $tallaId,
                            'color_id' => $colorId,
                        ],
                        [
                            'stock' => rand(5, 25)
                        ]
                    );
                }
            }

            // Generar imágenes clones (SOLO si no tiene ya)
            if ($producto->imagenes()->count() === 0) {
                for ($i = 1; $i <= 3; $i++) {
                    $producto->imagenes()->create([
                        'url_imagen' => 'https://picsum.photos/seed/' . $producto->id . $i . 'clone/800/800'
                    ]);
                }
            }

            // 9. CREAR CUPONES DE DESCUENTO PARA PRUEBAS
            $cupones = [
                [
                    'codigo' => 'BIENVENIDA10',
                    'tipo' => 'porcentaje',
                    'valor' => 10.00, // 10% de descuento
                    'is_active' => true
                ],
                [
                    'codigo' => 'MENOS5EUROS',
                    'tipo' => 'fijo',
                    'valor' => 5.00, // 5€ de descuento directo
                    'is_active' => true
                ],
                [
                    'codigo' => 'CRAZY20',
                    'tipo' => 'porcentaje',
                    'valor' => 20.00, // 20% de descuento
                    'is_active' => true
                ],
                [
                    'codigo' => 'CADUCADO50',
                    'tipo' => 'porcentaje',
                    'valor' => 50.00, // Cupón inactivo para probar que el backend da error
                    'is_active' => false
                ]
            ];

            foreach ($cupones as $cuponData) {
                Cupon::firstOrCreate(
                    ['codigo' => $cuponData['codigo']],
                    $cuponData
                );
            }

            // 9. CREAR CUPONES DE DESCUENTO PARA PRUEBAS
            $cupones = [
                [
                    'codigo' => 'BIENVENIDA10',
                    'tipo' => 'porcentaje',
                    'valor' => 10.00, // 10% de descuento
                    'is_active' => true
                ],
                [
                    'codigo' => 'MENOS5EUROS',
                    'tipo' => 'fijo',
                    'valor' => 5.00, // 5€ de descuento directo
                    'is_active' => true
                ],
                [
                    'codigo' => 'CRAZY20',
                    'tipo' => 'porcentaje',
                    'valor' => 20.00, // 20% de descuento
                    'is_active' => true
                ],
                [
                    'codigo' => 'CADUCADO50',
                    'tipo' => 'porcentaje',
                    'valor' => 50.00, // Cupón inactivo para probar que el backend da error
                    'is_active' => false
                ]
            ];

            foreach ($cupones as $cuponData) {
                Cupon::firstOrCreate(
                    ['codigo' => $cuponData['codigo']],
                    $cuponData
                );
            }
        }

        echo "✅ Base de datos poblada con éxito con catálogos hiper-realistas y SKUs de Variantes generados.\n";
        $this->call([
            OutfitTestSeeder::class
        ]);
    }
}

