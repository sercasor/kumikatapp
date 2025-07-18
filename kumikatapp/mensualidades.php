<!DOCTYPE html>
<html>
    <head>
        <title>Kumikatapp - Perfil</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!--realizado por Sergio Castillo Ortiz, amante del deporte-->
        <meta name="description" content="App de gestión para escuelas de artes marciales con la planificación de entrenamientos más detallada. Ahorra tiempo y dedícalo a lo que amas: enseñar">
        <link rel="stylesheet" href="stylesheet.css">
    </head>
    <body>
        
        <?php
        require_once 'controller.php';
        session_start();
        showHeader();
        echo '<main>';
        showLoginSiNoLogueado();
        $usuario = Persona::getNombreUsuarioCookie();
        //comprobamos que el usuario es profesor
        if (!Profesor::obtenerProfesor($usuario)) {
            die('<br>Contenido disponible sólo para profesores. Volver a <a href="index.php">inicio</a>');
        }
        $persona = Persona::renovarPersonaSesion();
        $cifEscuela = $persona->getCif();
        $mensualidades = Mensualidad::obtenerTodasMensualidades($cifEscuela);

// Mostrar formularios según tipo de usuario
        showTodasMensualidades($mensualidades);

//            showFormularioCrearMensualidad($mensualidades);    // Puede registrar alumnos
//            showFormularioBorrarMensualidad();  // También puede registrarse como profesor

        function showTodasMensualidades($mensualidades) {

            echo "<p>Estas son las mensualidades existentes:</p>\n
    <table border='1'>\n
        <tr>\n
            <th>Id</th>\n
            <th>Nombre</th>\n
            <th>Tipo</th>\n
            <th>Monto</th>\n
            <th>Estado</th>\n
        </tr>\n";
            foreach ($mensualidades as $mensualidad) {
                echo "<tr>\n
            <td>" . $mensualidad->getIdMensualidad() . "</td>\n
            <td>" . $mensualidad->getNombre() . "</td>\n
            <td>" . $mensualidad->getTipo() . "</td>\n
            <td>" . $mensualidad->getMonto() . "</td>\n
            <td>" . ($mensualidad->getActiva()?"Activa":"Desactivada") . "</td>\n
        </tr>\n";
            }

            echo "</table>\n";
        }
        ?>


        <h1>Registra o borra una mensualidad</h1>

        <h2>Registra una nueva mensualidad</h2>

        <form action="mensualidades.php" method="POST">
            <label for="nombreMensualidad">Nombre:</label>
            <input type="text" id="nombreMensualidad" name="nombreMensualidad">
            <label for="tipoMensualidad">Tipo:</label>
            <input type="text" id="tipoMensualidad" name="tipoMensualidad" placeholder="Básica, intermedia, etc.">
            <label for="estadoMensualidad">Tipo:</label>
            <select id="estadoMensualidad" name="estadoMensualidad">
                <option value='activa'>Activa</option>
                <option value='desactivada'>Desactivada</option>
            </select>
            <label for="montoMensualidad">Monto:</label>
            <input type="number" id="montoMensualidad" name="montoMensualidad" placeholder="100"><br>
            <button type="submit" name="formRegistroMensualidad">Registrar mensualidad</button>
        </form>

        <h2>Borrar una mensualidad</h2>
        <form action="mensualidades.php" method="POST"> 
            <label for="idMensualidad">Id de la mensualidad:</label>
            <input type="number" id="idMensualidad" name="idMensualidad" ><br>
            <button type="submit" name="formBorrarMensualidad">Borrar</button>
        </form>


        <?php
//si se ha mandado el formulario con éxito hacemos el INSERT en la BD del pago


        if (isset($_POST['formRegistroMensualidad'])) {
            try {
                $nombre = $_POST['nombreMensualidad'];
                $tipo = $_POST['tipoMensualidad'];
                $monto = $_POST['montoMensualidad'];
//                $persona = Persona::renovarPersonaSesion(); //no debería ser necesario porque esto se aplica con cada reinicio??
//                $cifEscuela = $persona->getCif(); //no deberia ser necesario
                if (Mensualidad::crearMensualidadBD($nombre, $tipo, $monto, $cifEscuela)) {                    
                    echo 'Mensualidad creada con éxito';
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
                echo $exc->getMessage();
            }
        }
        //si se ha mandado el formulario con éxito hacemos el DELETE en la BD del pago
        if (isset($_POST['formBorrarMensualidad'])) {
            try {
                $idMensualidad = $_POST['idMensualidad'];
                if (Mensualidad::borrarMensualidadBD($idMensualidad)) {
                    echo'Mensualidad borrada con éxito';
                }
                
            } catch (Exception $exc) {
                echo $exc->getMessage();
            }
        }
        echo'</main>';
        showFooter();
        ?>














    </body>
</html>
