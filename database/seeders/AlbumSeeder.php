<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Album;

class AlbumSeeder extends Seeder
{
    public function run(): void
    {
        $albums = [
            [
                "artista" => "Tan Biónica",
                "fecha" => "2013-09-20",
                "imagenPrincipal" => "https://tusitio.com/imagenes/destinologia_album.jpg",
                "nombre" => "Destinología",
                "redesSociales" => [
                    "https://www.youtube.com/playlist?list=PLtanbionica-destinologia",
                    "https://open.spotify.com/album/6EJwZ9v5Rw4XfL4d2YZZmL"
                ],
                "canciones" => [
                    ["titulo" => "Ciudad Mágica", "letra" => "Intento seguirte, pero no doy más
Sospecho que el tiempo se nos va a acabar
Estás algo loca y sos tan clásica
Dejá que la noche nos proponga más

Decime que sí
Hace como yo
A veces sos tan genial

Persigo tus ojos por la capital
Me gusta que seas tan dramática
Tus ojos dibujan una eternidad

Y está muy bien así
Por hoy no pienses más
Yo sé que lo necesitás

Me quedo con vos, yo sigo de largo, voy a buscarte
Que noche mágica, ciudad de Buenos Aires
Se queman las horas, de esta manera, nadie me espera
Como me gusta verte caminar así

Me quedo con vos, yo sigo de largo, voy a buscarte
Me mata como te moves por todas partes
Se queman las horas, de esta manera, nadie me espera
Como me gusta verte caminar así

Algunos momentos de esta eternidad
Me son suficientes para recordar
Tus piernas bailando son tan mágicas
La noche se presta para mucho más

Y está muy bien así
Por hoy no pienses más
Yo sé que lo necesitás

Me quedo con vos, yo sigo de largo, voy a buscarte
Que noche mágica, ciudad de Buenos Aires
Se queman las horas, de esta manera, nadie me espera
Como me gusta verte caminar así

Me quedo con vos, yo sigo de largo, voy a buscarte
Me mata como te moves por todas partes
Se queman las horas, de esta manera, nadie me espera
Como me gusta verte caminar así

Que noche mágica, ciudad de Buenos Aires
Como me gusta verte caminar así

Me quedo con vos, yo sigo de largo, voy a buscarte
Me mata como te moves por todas partes
Se queman las horas, de esta manera, nadie me espera
Como me gusta verte caminar así", "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/ciudadmagica.jpg"],
                    ["titulo" => "La Melodía de Dios", "letra" => "Todas las mañanas del mundo
Y esta angustia barata
El reloj amenaza y retrasa
Y la falta que haces en la casa

Cada cosa que no decís
Porque te está haciendo daño
En el nombre de mi desengaño
A la noche, te extraño, te extraño

Vivo, como siempre, desarmado sobre mí

Con vos es 4 de noviembre cada media hora
Atrasaré las horas, horas, horas
Que algo te libre de las penas acompañadoras
Cuando te sientas sola, sola, sola

Toda tu mesita de luz
Lleva el color de tu esencia
Las mañanas exigen clemencia
La catástrofe que hizo tu ausencia

Cuando se libere mi alma
De tus ojos de encanto
Cuando el frío no enfríe tanto
Los domingos y jueves de espanto

Vivo, como siempre, desarmado sobre mí
Yo buscaré algún Sol ahí

Con vos es 4 de noviembre cada media hora
Atrasaré las horas, horas, horas
Que algo te libre de las penas acompañadoras
Cuando te sientas sola, sola, sola

Cuando me faltes este otoño y se despinten solas
Tus acuarelas todas, todas, todas
No quiero nada más sin vos, no quiero estar a solas
No quiero, Barcelona dijo: Hola

Con vos es 4 de noviembre cada media hora
Atrasaré las horas, horas, horas
Que algo te libre de las penas acompañadoras
Cuando te sientas sola, sola, sola

Cuando me faltes este otoño y se despinten solas
Tus acuarelas todas, todas, todas
No quiero nada más sin vos, no quiero estar a solas
No quiero Barcelona, dijo: Hola

Atrasaré las horas, horas, horas
(Atrasaré las horas, atrasaré las horas)
Atrasaré las horas, horas, horas
(Atrasaré las horas, atrasaré las horas)
Atrasaré las horas, horas, horas
(Atrasaré las horas, atrasaré las horas)
Atrasaré las horas, horas, horas", "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/lamelodíadedios.jpg"],
                    ["titulo" => "Mis Noches de Enero", "letra" => "Se detuvo el tiempo y la lluvia no llovió
Cuando, por el cielo de Palermo, apareció
Vengo atravesado por un lunes de terror
Y no lamento otra tardecita sin Sol

Tengo algún recuerdo del lugar donde nací
Tengo la sospecha de que también fui feliz
Tengo tantas ganas de parar y de seguir
O de fugarme, por algunos siglos, de mí

Y a cada noche que anocheció
Su cancioncita triste me llevó, me llevó
Y a coquetear con demonios, no
Su bailecito torpe me llevó, me llevó

Yo buscaré en mis recuerdos otra vez
Tus ojos primero, mis noches de enero
Yo viajaré, aprendiendo a seguir
Abocado al arte de necesitarte
Quiero recordarte así

Y a cada noche que anocheció
Su cancioncita triste me llevó, me llevó
Y a coquetear con demonios, no
Su bailecito torpe me llevó, me llevó

Yo buscaré en mis recuerdos otra vez
Tus ojos primero, mis noches de enero
Yo viajaré aprendiendo a seguir
Abocado al arte de necesitarte
Quiero recordarte así

Yo viajaré, aprendiendo a seguir
Tus ojos primero, mis noches de enero
Yo viajaré, aprendiendo a seguir
Tus ojos primero, mis noches de enero

(Yo buscaré en mis recuerdos otra vez)
Tus ojos primero, mis noches de enero
(Yo buscaré)
Tus ojos primero, mis noches de enero

(Yo buscaré, qué poco nos queda)
Tus ojos primero, mis noches de enero
(Yo viajaré, qué poco nos queda)
Tus ojos primero, mis noches de enero

Yo viajaré, aprendiendo a seguir (qué poco nos queda)
Tus ojos primero, mis noches de enero
Yo viajaré, aprendiendo a seguir (qué poco nos queda)
Tus ojos primero, mis noches de enero

(Yo buscaré, qué poco nos queda)
Tus ojos primero, mis noches de enero
Yo viajaré, aprendiendo a seguir (qué poco nos queda)
Tus ojos primero, mis noches de enero", "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/misnochesdeenero.jpg"],
                    ["titulo" => "Tus Ojos Mil", "letra" => "Retrocedo, como el tiempo
¿Cuánto queda para mi?
Cuando vuelva del infierno
Tus ojos mil

En el ultimo tormento
Del veranito de San Juan
Mariposas con lamento
Volaban La Paternal

Y ando mejor a veces
Cuando no sé dónde ir
Cuando no ataca el sueño
Y me da por escribir

Se niega el sol a salir
Hoy no te pongas así
Yo ya no hablo de mi destino
Tus ojos mil

En el fondo, en el alma, en el fin
En lo que queda de mi
En el efecto melancolía
Tus ojos mil

La nostalgia del verano
Llega con puntualidad
Y mis sueños demorados
Patearon la capital

Los fantasmas del pasado
Ruegan un volver atrás
No me olvides que me apago
Que no lo puedo evitar

Algunas pocas veces
Reconozco padecer
Los cielos con tormentas
Que se olvidan de llover

Si estás tan lejos de mi
Hoy no te pongas así
No tengo nada que ver conmigo
Tus ojos mil

En el fondo, en el alma, en el fin
En lo que queda de mi
En el efecto melancolía
Tus ojos mil

En el fondo, en el alma, en el fin
En lo que queda de mi
Yo no te quise matar febrero
Tus ojos mil, tus ojos mil

Yo no te quise matar febrero
Yo no te quise matar febrero
Tus ojos mil", "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/tusojosmil.jpg"],
                    ["titulo" => "Música", "letra" => "Todos mis ataques pasajeros
Me sorprenden a la hora de cenar
Porque flotan en el vaso de la lluvia de febrero
Que no moja ni entristece la ciudad

Cada momento de duelo
Cada tormento primero
Cada pesar consuelo
Cada dolor pasajero

Cada manía o apego
Cada lamento sincero
Cada domingo fulero
Cada lunes de miedo

Todos los días del arquero
Yo y mi look de pordiosero
Todas las capas del cielo
Todas las cosas que quiero

Todos los días del mundo
Existe una forma de resucitar

Cada noche, en cada lugar
Los momentos que nos quedan
Una absurda oportunidad de vivir
Revivir mi vida

Música
Mientras caemos, hay música
Aunque ahí afuera esté todo mal
Es el parlante de mi ciudad
Drama-má-má-má-má-má-má-má-má-mática
La hora de la pena y nadie tiene paz
Yo sigo sin escuchar

Lentos, infinitos, los minutos del invierno
Se diluyen en la boca de un diablo charlatán

Porque solo me llevo la gloria
De tener en la memoria
Una mágica historia
En tus horas de euforia

Todos los días del mundo
Existe una forma de resucitar

Cada noche, en cada lugar
Los momentos que nos quedan
Una absurda oportunidad de vivir
Revivir mi vida

Música
Mientras caemos, hay música
Aunque ahí afuera esté todo mal
Es el parlante de mi ciudad
Dramá-má-má-má-má-má-má-má-má-mática
La hora de la pena y nadie tiene paz
Yo sigo sin escuchar

Las voces de la angustia y la soledad
La ausencia indeclinable de la libertad
Yo vivo las rutinas más
Faná-ná-ná-ná-ná-ná-ná-ná-náticas
Que nacen de mañanas problemáticas
Que sigo sin despertar", "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/musica.jpg"],
                    ["titulo" => "Hola Noviembre", "letra" => "Se anochecieron las tardes
De los días que pasamos
De algunos pocos veranos
Que tuvimos de prestado

Y cada vez que hablas así
En la boca, se te nota
Que me escondes las sombras
Justo atrás de mis derrotas

No me hagas más los ojitos
De los viernes, los feriados
Ni me invites a la boda
De la hija del diablo

Si estás pensando en mí
Y abril no funcionó
Pero te extraño, aunque
No nos estemos entendiendo

¿Tener o no tener?
¿Volver o no volver?
Ayer y antes de ayer
Tuvimos un hermoso tiempo

Llenaste nuestra habitación de ausencia
Hola, noviembre
¿Quién va a sacar del comedor la angustia?
Hola, noviembre

Si andas por todos los cielos
Menos el de nuestro barrio
Se te perdieron los años
Anteanoche, como a diario

Yo voy a perder esta guerra
Tan temprano
Yo busco en todas las flores
Tu perfume de verano

Si estás pensando en mí
Y abril no funcionó
Pero te extraño, aunque
No nos estemos entendiendo

¿Tener o no tener?
¿Volver o no volver?
Ayer y antes de ayer
Tuvimos un hermoso tiempo

Llenaste nuestra habitación de ausencia
Hola, noviembre
¿Quién va a sacar del comedor la angustia?
Hola, noviembre
Hola, noviembre
Hola, noviembre

Si estás pensando en mí
Y abril no funcionó
Ayer y antes de ayer
Hola, noviembre

Llenaste nuestra habitación de ausencia
Hola, noviembre
¿Quién va a sacar del comedor la angustia?
Hola, noviembre

Si estás pensando en mí
Y abril no funciono
Hola, noviembre

¿Tener o no tener?
¿Volver o no volver?
Hola, noviembre", "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/holanoviembre.jpg"],
                    ["titulo" => "Vámonos", "letra" => "Media mañana en la ciudad y un tren que se va siempre
Ayer llovieron amuletos de la mala suerte
Hoy se prendieron lucecitas que se apagan siempre
Desaparece y aparece como vos la suerte

Todas las mañanas de mi vida me pregunto que será de tus ojitos indecentes
Todas las tormentas y los soles que no salen, los olores y las flores de septiembre
Un hechizo o brujería o venganza de la vida llega tarde y casi no se siente
Yo tengo recuerdo de tus dientes de diabla patoteando de mañana a la muerte

Cada sutileza de ayer se duplica y hace llenar
De mareos los otoños y nostalgias la ciudad
Yo no busco ayeres en hoy, ni mañanas en nunca más
Los minutos se disfrazan de segundos

A mi nadie me dijo de vos
Pintaron los diablitos y yo
Y nuestros angelitos de Dios
Al borde bailaban, bailaban, bailaban con vos

Millones de ausencias sin voces se escuchan lamentos antiguos de vidas pasadas
Ayer pensaba en que lugar de mi memoria guardo tu espalda frente al arroyo de alta córdoba de la cañada
Yo siento que vuelvo y encubro mis silencios
Yo no respondo por espantos ni por viejos tiempos

A mi nadie me dijo de vos
Tiramos una combinación
Comimos maravillas al Sol
Rompimos el secreto con vos

A mi nadie me dijo de vos
Pintaron los diablitos y yo
Y nuestros angelitos de Dios
Al borde bailaban, bailaban, bailaban

A mi nadie me dijo de vos
Tiramos una combinación
Nos vamos y decimos jalou
Comimos maravillas al Sol

A mi nadie me dijo de vos", "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/vamonos.jpg"],
                    ["titulo" => "Mi Vida Secreta", "letra" => "Otra madrugada inquieta, para mi vida secreta
Y mi tristeza de saturday night se lleva bien con esta soledad
Ella sueña con fantasmas, cura el horror cataplasma
Y va tratando de resucitar y renunciar a llorar y llorar

Me desconecto más
Te miento y te digo la verdad
Es viernes y te extraño, es una nueva desilusión
Tus labios de encanto provocan espanto
Y recuerdo el dolor de noches y noches de calor

Oh

Un paisaje de ilusiones
Miércoles de apagones
Se nota siempre la soledad
Algunas cosas son para postergar

Hoy siguió llorando a mares
Penas internacionales
Llueve cien mil tormentas por acá
Perdió la fe en la estación que quedo atrás

Me desconecto más
Ni miento ni digo la verdad
Es viernes y te extraño, es una nueva desilusión
Tus labios de encanto provocan espanto
Y recuerdo el dolor de noches y noches de calor

Nosotros los que estamos hoy, perdidos en la desilusión
Mi vida secreta es el alfabeta de tu corazón
Mis ojos se queman con el sol

Oh", "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/mividasecreta.jpg"],
                    ["titulo" => "Momentos de Mi Vida", "letra" => "Dale, no me podes tratar así en este martes medio gris
La luna es una cosa que se pierde en la penosa
madrugada silenciosa del cielo de San Martin
Todo amanecer que estés acá, con esas ganas de llorar
La pena es un recuerdo moribundo que se guarda
bien profundo en el submundo, y muro de mi ser

Es la hora de tomar nota, de pagarte todas las cuotas
De reconocer las derrotas y la habilidad de perderte
Todo lo que fui que ya no soy, creo que perdí la conexión
De momentos de mi vida

Si ya no me espera nadie hoy, me gusta perder la dirección
Mañana es otro día

Loca, si estas tan sola como yo, yo te comparto este dolor
Las cosas del destino no se miden con la esencia
Ni el idioma de la ausencia que deja tú aparecer

Es la hora de tomar nota, de pagarte todas las cuotas
De reconocer las derrotas y la habilidad de perderte

Todo lo que fui que ya no soy, creo que perdí la conexión
De momentos de mi vida
Si ya no me espera nadie hoy, me gusta perder la dirección
Mañana es otro día
De momentos de mi vida
Mañana es otro día", "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/momentosdemivida.jpg"],
                    ["titulo" => "El Asunto", "letra" => "Ya bajé la guardia hace algún tiempo
No me enojo, ni me río porque sí
Canto mi bolero desangrado
Pinto el cielo en acuarelas azul sobre gris
Y eran calesitas desoladas
No me dejen solo por ahí
Ya le rendí cuentas al destino
No me sigas, don't follow me

Todas las mañanas llora porque si
No pretendo en sueños, yo sin vos, sin mi
Fue la ultima vez que la vi
Yo vivo la vida de la ausencia
Veo con los ojos del olvido la verdad

Cada lunes es un día muerto
Y el espejo, fatalidad
Ponganme anestesia sin apuro
Que hoy me está costando sonreír
Tengo más pasado que futuro
Y unos años sin dormir
Se curo de espanto, se acercó hasta mi
Y escondió consejos para ser feliz
Fue la ultima vez que la vi

Todas las mañanas
Llora porque sí
No pretendo en sueños; yo sin vos, sin mi
Fue la ultima vez que la vi
Fue la ultima vez que la vi", "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/elasunto.jpg"],
                    ["titulo" => "Poema de lo Cielo", "letra" => "Es una forma de huir o un acto de libertad
Es imposible seguir, me hago viejo y no sé en que confiar
En algo de azar luces caen en tu sombra
Y es un hecho triste mi naturaleza
Mi destino insiste con tenerte cerca
Como la noche se lleva el sol
Y nos deja un par de estrellas
Cómo se quema en tus ojos, toda mi tristeza

Y es imperdonable toda tu belleza
Salvale los males de mañana muertas
Como se lleva el olvido todas mis promesas
Como se quema en tus ojos toda mi tristeza
Como la noche se lleva el sol y nos deja un par de estrellas
Como se quema en tus ojos toda mi tristeza
Es una forma de huir o un acto de libertad", "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/poemadelocielo.jpg"],
                    ["titulo" => "Sinfonía de los Mares", "letra" => "Accede al link: https://youtu.be/Ig7b8PLTD0s?si=Ref3JA0NRstMgPeQ", "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/sinfoniadelosmares.jpg"],
                    ["titulo" => "Ciudad Mágica - Live From Argentina", "letra" => "Intento seguirte, pero no doy más
Sospecho que el tiempo se nos va a acabar
Estás algo loca y sos tan clásica
Dejá que la noche nos proponga más

Decime que sí
Hace como yo
A veces sos tan genial

Persigo tus ojos por la capital
Me gusta que seas tan dramática
Tus ojos dibujan una eternidad

Y está muy bien así
Por hoy no pienses más
Yo sé que lo necesitás

Me quedo con vos, yo sigo de largo, voy a buscarte
Que noche mágica, ciudad de Buenos Aires
Se queman las horas, de esta manera, nadie me espera
Como me gusta verte caminar así

Me quedo con vos, yo sigo de largo, voy a buscarte
Me mata como te moves por todas partes
Se queman las horas, de esta manera, nadie me espera
Como me gusta verte caminar así

Algunos momentos de esta eternidad
Me son suficientes para recordar
Tus piernas bailando son tan mágicas
La noche se presta para mucho más

Y está muy bien así
Por hoy no pienses más
Yo sé que lo necesitás

Me quedo con vos, yo sigo de largo, voy a buscarte
Que noche mágica, ciudad de Buenos Aires
Se queman las horas, de esta manera, nadie me espera
Como me gusta verte caminar así

Me quedo con vos, yo sigo de largo, voy a buscarte
Me mata como te moves por todas partes
Se queman las horas, de esta manera, nadie me espera
Como me gusta verte caminar así

Que noche mágica, ciudad de Buenos Aires
Como me gusta verte caminar así

Me quedo con vos, yo sigo de largo, voy a buscarte
Me mata como te moves por todas partes
Se queman las horas, de esta manera, nadie me espera
Como me gusta verte caminar así", "feat" => "Tan Biónica (En Vivo)", "imagenPrincipal" => "https://tusitio.com/imagenes/ciudadmagica_live.jpg"]
                ],
                "updated_at" => now()
            ],
            [
                "artista" => "Tan Biónica",
                "fecha" => "2010-11-01",
                "imagenPrincipal" => "https://tusitio.com/imagenes/obsesionario_album.jpg",
                "nombre" => "Obsesionario",
                "redesSociales" => [
                    "https://www.youtube.com/playlist?list=PLtanbionica-obsesionario",
                    "https://open.spotify.com/album/5xS0T3pJjHq2FoA3Hg4Wms"
                ],
                "canciones" => [
                    ["titulo" => "Ella", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/ella.jpg"],
                    ["titulo" => "Beautiful", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/beautiful.jpg"],
                    ["titulo" => "Obsesionario En La Mayor", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/obsesionarioenlamayor.jpg"],
                    ["titulo" => "Loca", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/loca.jpg"],
                    ["titulo" => "El Duelo", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/elduelo.jpg"],
                    ["titulo" => "Dominguicidio", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/dominguicidio.jpg"],
                    ["titulo" => "Pastillitas del Olvido", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/pastillitasdelolvido.jpg"],
                    ["titulo" => "La Suerte Está Echada", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/lasuerteestaechada.jpg"],
                    ["titulo" => "La Comunidad", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/lacomunidad.jpg"],
                    ["titulo" => "Perdida", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/perdida.jpg"],
                    ["titulo" => "El Color del Ayer", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/elcolordelayer.jpg"],
                    ["titulo" => "Pétalos", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/petalos.jpg"]
                ],
                "updated_at" => now()
            ],
            [
                "artista" => "Tan Biónica",
                "fecha" => "2007-03-15",
                "imagenPrincipal" => "https://tusitio.com/imagenes/cancionesdelhuracan_album.jpg",
                "nombre" => "Canciones del Huracán",
                "redesSociales" => [
                    "https://www.youtube.com/playlist?list=PLtanbionica-huracan",
                    "https://open.spotify.com/album/3fI4T3QOegv0iYX5sRkZ9W"
                ],
                "canciones" => [
                    ["titulo" => "Chica Biónica", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/chicabionica.jpg"],
                    ["titulo" => "Arruinarse", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/arruinarse.jpg"],
                    ["titulo" => "Mis Madrugaditas", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/mismadrugaditas.jpg"],
                    ["titulo" => "La Ensalada", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/laensalada.jpg"],
                    ["titulo" => "Yo Te Espero", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/yoteespero.jpg"],
                    ["titulo" => "Vidas Perfectas", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/vidasperfectas.jpg"],
                    ["titulo" => "El Huracán", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/elhuracan.jpg"],
                    ["titulo" => "Tapa de Moda", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/tapademoda.jpg"],
                    ["titulo" => "La Depresión", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/ladepresion.jpg"],
                    ["titulo" => "Queso Ruso", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/quesoruso.jpg"],
                    ["titulo" => "Vinidy Swing", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/vinidyswing.jpg"],
                    ["titulo" => "Nací en Primavera", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/nacienprimavera.jpg"],
                    ["titulo" => "Bye Bye", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/byebye.jpg"],
                    ["titulo" => "Lunita de Tucumán", "letra" => null, "feat" => null, "imagenPrincipal" => "https://tusitio.com/imagenes/lunitadetucuman.jpg"]
                ],
                "updated_at" => now()
            ]
        ];

        foreach ($albums as $data) {
            $nombreNormalizado = strtolower(trim($data['nombre']));
            $existente = Album::whereRaw([
                'nombre' => ['$regex' => "^{$nombreNormalizado}$", '$options' => 'i']
            ])->first();

            if ($existente) {
                $existente->update($data);
            } else {
                Album::create($data);
            }
        }
    }
}