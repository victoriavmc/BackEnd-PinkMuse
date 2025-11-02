<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Noticia;

class NoticiaSeeder extends Seeder
{
    public function run(): void
    {
        $noticias = [
            [
                "tipoActividad" => "noticia",
                "titulo" => "Anuncio Oficial del Nuevo Álbum 'Luz Infinita'",
                "descripcion" => "La banda anunció oficialmente su nuevo álbum titulado 'Luz Infinita', que verá la luz el próximo mes. Contará con 12 canciones originales y colaboraciones sorpresa. El disco explorará sonidos más electrónicos, sin perder la esencia alternativa que los caracteriza.",
                "resumen" => "La banda presenta 'Luz Infinita', su nuevo trabajo discográfico con 12 temas inéditos.",
                "imagenPrincipal" => "https://tusitio.com/imagenes/luzinfinita_principal.jpg",
                "imagenes" => [
                    "https://tusitio.com/imagenes/luzinfinita_1.jpg",
                    "https://tusitio.com/imagenes/luzinfinita_2.jpg"
                ],
                "fecha" => "2025-01-15",
                "habilitacionAcciones" => "si",
                "habilitacionComentarios" => true,
                "autor" => "PinkMuse",
                "categoria" => "Lanzamientos",
                "fuente" => "PinkMuse Press",
                "etiquetas" => ["álbum", "música", "novedades"]
            ],
            [
                "tipoActividad" => "noticia",
                "titulo" => "Nueva Gira Internacional Confirmada",
                "descripcion" => "La banda confirmó una gira internacional que recorrerá más de 10 países. Comenzará en Buenos Aires y continuará por México, España, Brasil y Chile, entre otros. Las entradas estarán disponibles a partir del 1 de marzo.",
                "resumen" => "Gira internacional confirmada con más de 10 fechas en América y Europa.",
                "imagenPrincipal" => "https://tusitio.com/imagenes/gira2025_principal.jpg",
                "imagenes" => [
                    "https://tusitio.com/imagenes/gira2025_1.jpg",
                    "https://tusitio.com/imagenes/gira2025_2.jpg"
                ],
                "fecha" => "2025-02-20",
                "habilitacionAcciones" => "si",
                "habilitacionComentarios" => true,
                "autor" => "PinkMuse",
                "categoria" => "Eventos",
                "fuente" => "PinkMuse Oficial",
                "etiquetas" => ["gira", "tour", "anuncio"]
            ],
            [
                "tipoActividad" => "noticia",
                "titulo" => "PinkMuse Inicia su Tour en el Luna Park",
                "descripcion" => "El esperado inicio del tour 'Luz Infinita' se vivió en el Luna Park con una asistencia récord. El show incluyó visuales impactantes y un cierre con la canción 'Ecos del Tiempo'. Los fans destacaron la energía y la puesta en escena.",
                "resumen" => "Arrancó el tour 'Luz Infinita' en el Luna Park con entradas agotadas.",
                "imagenPrincipal" => "https://tusitio.com/imagenes/lunaparkshow_principal.jpg",
                "imagenes" => [
                    "https://tusitio.com/imagenes/lunaparkshow_1.jpg",
                    "https://tusitio.com/imagenes/lunaparkshow_2.jpg"
                ],
                "fecha" => "2025-03-15",
                "habilitacionAcciones" => "si",
                "habilitacionComentarios" => true,
                "autor" => "PinkMuse",
                "categoria" => "Conciertos",
                "fuente" => "Rolling Music",
                "etiquetas" => ["luna park", "tour", "shows"]
            ],
            [
                "tipoActividad" => "noticia",
                "titulo" => "PinkMuse Lanza Videoclip de 'Fuego Azul'",
                "descripcion" => "El nuevo videoclip del single 'Fuego Azul' fue lanzado oficialmente en YouTube y ya supera el millón de visualizaciones. El video combina elementos visuales surrealistas con una narrativa introspectiva, dirigida por Ana Suárez.",
                "resumen" => "Nuevo videoclip 'Fuego Azul' supera el millón de reproducciones en su primer día.",
                "imagenPrincipal" => "https://tusitio.com/imagenes/fuegoazul_principal.jpg",
                "imagenes" => [
                    "https://tusitio.com/imagenes/fuegoazul_1.jpg",
                    "https://tusitio.com/imagenes/fuegoazul_2.jpg"
                ],
                "fecha" => "2025-04-22",
                "habilitacionAcciones" => "si",
                "habilitacionComentarios" => true,
                "autor" => "PinkMuse",
                "categoria" => "Videoclips",
                "fuente" => "YouTube Oficial",
                "etiquetas" => ["fuego azul", "video", "estreno"]
            ],
            [
                "tipoActividad" => "noticia",
                "titulo" => "Concierto Benéfico por la Música Independiente",
                "descripcion" => "PinkMuse se sumó al evento benéfico 'Sonidos Solidarios', junto a más de 20 artistas independientes. El objetivo fue recaudar fondos para apoyar a nuevos músicos emergentes. La jornada concluyó con una versión acústica inédita.",
                "resumen" => "PinkMuse participó en el festival 'Sonidos Solidarios' para apoyar la música emergente.",
                "imagenPrincipal" => "https://tusitio.com/imagenes/benefico_principal.jpg",
                "imagenes" => [
                    "https://tusitio.com/imagenes/benefico_1.jpg",
                    "https://tusitio.com/imagenes/benefico_2.jpg"
                ],
                "fecha" => "2025-05-10",
                "habilitacionAcciones" => "si",
                "habilitacionComentarios" => true,
                "autor" => "PinkMuse",
                "categoria" => "Eventos",
                "fuente" => "Sonidos Solidarios",
                "etiquetas" => ["beneficio", "festival", "solidario"]
            ],
            [
                "tipoActividad" => "noticia",
                "titulo" => "PinkMuse Alcanza el Top 10 Global de Spotify",
                "descripcion" => "Con más de 50 millones de reproducciones, el nuevo álbum 'Luz Infinita' se posicionó entre los 10 discos más escuchados del mes en Spotify Global. El tema 'Eterna' lidera las listas en Latinoamérica.",
                "resumen" => "'Luz Infinita' se ubica entre los 10 álbumes más escuchados del mundo.",
                "imagenPrincipal" => "https://tusitio.com/imagenes/spotify_principal.jpg",
                "imagenes" => [
                    "https://tusitio.com/imagenes/spotify_1.jpg",
                    "https://tusitio.com/imagenes/spotify_2.jpg"
                ],
                "fecha" => "2025-07-01",
                "habilitacionAcciones" => "si",
                "habilitacionComentarios" => true,
                "autor" => "PinkMuse",
                "categoria" => "Streaming",
                "fuente" => "Spotify Charts",
                "etiquetas" => ["spotify", "ranking", "éxito"]
            ],
            [
                "tipoActividad" => "noticia",
                "titulo" => "Fin de Gira y Nuevo Documental en Camino",
                "descripcion" => "La banda cerró su gira mundial en Río de Janeiro ante 60 mil personas. Durante el evento se anunció la producción de un documental sobre la creación de 'Luz Infinita' y las vivencias del tour, con estreno previsto para fin de año.",
                "resumen" => "Culmina la gira mundial y se confirma un documental sobre 'Luz Infinita'.",
                "imagenPrincipal" => "https://tusitio.com/imagenes/riocierre_principal.jpg",
                "imagenes" => [
                    "https://tusitio.com/imagenes/riocierre_1.jpg",
                    "https://tusitio.com/imagenes/riocierre_2.jpg"
                ],
                "fecha" => "2025-10-25",
                "habilitacionAcciones" => "si",
                "habilitacionComentarios" => true,
                "autor" => "PinkMuse",
                "categoria" => "Tour",
                "fuente" => "PinkMuse Oficial",
                "etiquetas" => ["gira", "documental", "cierre"]
            ],
            [
                "tipoActividad" => "noticia",
                "titulo" => "La leyenda del desodorante: ¿mito urbano o expediente clasificado de la farándula argentina?",
                "descripcion" => "Según rumores que resisten décadas, Marcelo Tinelli habría protagonizado un insólito episodio tras una fiesta privada, ingresando de urgencia a una clínica con un desodorante... en una zona no autorizada. Aunque nunca se confirmó, el mito persiste en la cultura popular.",
                "resumen" => "El supuesto incidente de Tinelli y el desodorante vuelve a ser tendencia en redes.",
                "imagenPrincipal" => "https://i.redd.it/y7a0isqahqef1.jpeg",
                "imagenes" => ["https://tusitio.com/imagenes/desodorante-mito1.jpg"],
                "fecha" => "2010-04-10",
                "habilitacionAcciones" => "si",
                "habilitacionComentarios" => true,
                "autor" => "PinkMuse",
                "categoria" => "Leyendas Urbanas",
                "fuente" => "Revista Paparazzi",
                "etiquetas" => ["tinelli", "mito urbano", "humor"]
            ],
            [
                "tipoActividad" => "noticia",
                "titulo" => "¿Rubius es el sugardaddy de Gaspi? Fotos y memes alimentan el rumor",
                "descripcion" => "Usuarios de Twitter explotaron tras ver a Rubius regalarle una consola nueva a Gaspi y comentarle 'te extraño' en Twitch. Aunque ambos lo niegan, los fans ya los apodan 'Rugaspi' y exigen colaboración entre ambos.",
                "resumen" => "El internet arde con los rumores del supuesto romance entre Rubius y Gaspi.",
                "imagenPrincipal" => "https://tusitio.com/imagenes/rubius-gaspi.jpg",
                "imagenes" => ["https://tusitio.com/imagenes/rugaspi1.jpg"],
                "fecha" => "2025-08-02",
                "habilitacionAcciones" => "si",
                "habilitacionComentarios" => true,
                "autor" => "PinkMuse",
                "categoria" => "Fandom",
                "fuente" => "Twitter / X",
                "etiquetas" => ["rubius", "gaspi", "rumores"]
            ],
            [
                "tipoActividad" => "noticia",
                "titulo" => "Charly García cayó del piso 9... pero a una pileta",
                "descripcion" => "El legendario rockero protagonizó una historia que ya es parte del folclore argentino: una caída desde el noveno piso que terminó en una pileta, salvando milagrosamente su vida y consolidando su estatus de mito viviente del rock nacional.",
                "resumen" => "El salto inmortal de Charly García cumple 25 años y sigue siendo historia viva del rock.",
                "imagenPrincipal" => "https://tusitio.com/imagenes/charly-caida.jpg",
                "imagenes" => ["https://tusitio.com/imagenes/charly-pileta1.jpg"],
                "fecha" => "2000-03-03",
                "habilitacionAcciones" => "si",
                "habilitacionComentarios" => true,
                "autor" => "PinkMuse",
                "categoria" => "Historias",
                "fuente" => "Rock&Roll Times",
                "etiquetas" => ["charly garcía", "rock argentino", "anécdotas"]
            ]
        ];

        foreach ($noticias as $data) {
            $tituloNormalizado = strtolower(trim($data['titulo']));
            $existente = Noticia::whereRaw(['titulo' => ['$regex' => "^{$tituloNormalizado}$", '$options' => 'i']])->first();

            if ($existente) {
                $existente->update($data);
            } else {
                Noticia::create($data);
            }
        }
    }
}