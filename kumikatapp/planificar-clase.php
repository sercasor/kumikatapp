<!DOCTYPE html>
<html>
    <head>
        <title>Planificador de clases</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Planifica tu clase y consigue una mayor optimización del tiempo, mejorando la experiecia de aprendizaje de tus alumnos.">
        <link rel="stylesheet" href="stylesheet.css">
        <!--realizado por Sergio Castillo Ortiz, amante del deporte-->
    </head>
    <body>
        <?php
        /* FUNCIONES DE SESION - POR DEFECTO */
        require_once 'controller.php';
        session_start();
        // Refrescar sesión si la variable no está creada
        $usuario = Persona::renovarPersonaSesion();

        /* FUNCIONES POR DEFECTO */
        showHeader();
        showLoginSiNoLogueado();

        /* CODIGO GLOBAL MODIFICABLE */
        echo'<main>';

        $esProfesor = false;
        contenidoRestringidoProfe($esProfesor, $usuario);

        $tecnicas = Tecnica::obtenerTodasTecnicas();
        ?>

        <h1>Planificación de clases individuales</h1>
        <h2>Elige la clase</h2>
        <p>Si no sabes el id de la clase, puedes averiguarlo en el <a href="calendario.php">calendario</a></p>
        <form action="planificar-clase.php" method="GET">
            <label for="idClase">Id clase:</label>
            <input type="number" id="idClase" name="idClase">

            <label for="calentamiento">Calentamiento:</label>
            <input type="text" id="calentamiento" name="calentamiento" placeholder="Descripción del calentamiento">
            <label for="minutosCalentamiento">Minutos de calentamiento:</label>
            <input type="number" id="minutosCalentamiento" name="minutosCalentamiento"><br>



            <label for="tecnica">Técnica del día:</label>
            <select id="tecnica" name="tecnica">
                <?php
                foreach ($tecnicas as $tecnica) {
                    $tecnicaNombre = $tecnica->getNombre();
                    $tecnicaId = $tecnica->getIdTecnica();
                    echo("<option value='$tecnicaId'>$tecnicaNombre</option>");
                }
                ?>
            </select>
            <label for="minutosTecnica">Minutos de técnica:</label>
            <input type="number" id="minutosTecnica" name="minutosTecnica"><br>

            <label for="rondasLuchas">Rondas de luchas:</label>
            <input type="number" id="rondasLuchas" name="rondasLuchas">
            <label for="minutosRondas">Minutos por lucha:</label>
            <input type="number" id="minutosRondas" name="minutosRondas"><br>

            <button type="submit" name="formPlanificarClase">Generar planificación de clase</button>
        </form>






        <?php
//si se ha mandado el formulario con éxito hacemos el INSERT en la tabla de la BD correspondiente

        if (isset($_GET['formPlanificarClase'])) {
            //variables y objetos
            $idClase = $_GET['idClase'];
            $clase = Clase::obtenerClase($idClase);
//            $clase = NEW Clase($datos); //BORRAR


            $calentamiento = $_GET['calentamiento'];
            $minutosCalentamiento = $_GET['minutosCalentamiento'];

            $tecnicaElegida = $_GET['tecnica'];
            $tecnica = Tecnica::obtenerTecnicaSiExiste($tecnicaElegida);
//            $tecnica = new Tecnica($datos); //BORRAR
            $dificultadTecnica = $tecnica->getDificultad();
            $minutosTecnica = $_GET['minutosTecnica'];

            $rondas = $_GET['rondasLuchas'];
            $minutosLuchas = $_GET['minutosRondas'];
            try {
                echo $clase->crearCalentamiento($calentamiento, $minutosCalentamiento);
                echo $clase->asignarTecnicaBD($tecnica, $minutosTecnica);
                echo $clase->asignarLuchas($rondas, $minutosLuchas);
            } catch (Exception $exc) {
                echo "<p style='color:red'>ERROR: {$exc->getMessage()}</p>";
            }



            echo'<h2>Empieza la clase: </h2>';
            echo'<h3>Calentamiento </h3>';
            echo"<p>Calentamiento:  $calentamiento durante $minutosCalentamiento minutos</p>";

            // asignación de tecnica por parejas
            echo '<h3>Realización de técnica</h3>';
            echo '<p>Parejas creadas para la realización de la técnica ' . $tecnica->getNombre() . ' durante ' . $minutosTecnica . ' minutos y cuya dificultad es ' . $dificultadTecnica . ' :</p>';
            $alumnos = $clase->getAlumnosClase($idClase);
            //debugging
//            echo "Debugging: contenidos de variable \$alumnos <pre>";
//            var_dump($alumnos);
//            echo "</pre>";
            $parejas = $clase->crearParejas($alumnos);
            foreach ($parejas as $pareja) {
                $a = $pareja[0];
                $b = $pareja[1];
                // depuración, debería mostrar objetos con los valores sacados de la BD BORRAR
//                var_dump($a); 
//                var_dump($b);
                echo "<p>Pareja creada: {$a->getNombre()}  y {$b->getNombre()}</p>";
            }

            echo '<h3>Luchas:</h3>';
            echo '<p>Parejas creadas para las luchas:</p>';

            //en cada ronda se aleatorizará el array con el método shuffle
            for ($i = 0; $i < $rondas - 1; $i++) {
                $contadorRonda = $i + 1;
                echo "<h3>Ronda nº$contadorRonda:</h3>";
                $parejas = $clase->crearParejas($alumnos);
                foreach ($parejas as $pareja) {
                    $a = $pareja[0];
                    $b = $pareja[1];
                    // depuración, debería mostrar objetos con los valores sacados de la BD BORRAR
//                var_dump($a); 
//                var_dump($b);
                    echo "<p>Pareja creada: {$a->getNombre()}  y {$b->getNombre()}</p>";
                }
            }
        }
        echo'</main>';
        showFooter();
        ?>














    </body>
</html>
