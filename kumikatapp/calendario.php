<!DOCTYPE html>
<html>
    <head>
        <title>Calendario</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Visualiza las clases futuras de tu escuela, apúntate o cancela con facilidad.">
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

        $clases = Clase::obtenerTodasClasesFuturas();
        ?>

        <h1>Calendario de clases futuras</h1>

        <h2>Confirmar asistencia a una clase</h2>
        <form action="calendario.php" method="POST">
            <label for="idClaseElegida">Selecciona una clase para apuntarte:</label>
            <select name="idClaseElegida" id="idClaseElegida" required>
                <?php
//                $clase=new Clase($clase);//borrar


                foreach ($clases as $clase) {
                    $claseFecha = new DateTime($clase->getFechaHora());
                    $claseFechaFormateada = $claseFecha->format("d/m/Y - H:i:s");
                    $claseId = $clase->getIdClase();
                    echo("<option value='$claseId'>$claseFechaFormateada</option>");
                }
                ?>

            </select>

            <button type="submit" name="confirmarAsistencia">Confirmar asistencia</button>
            <button type="submit" name="cancelarAsistencia">Cancelar asistencia</button>
        </form>


        <?php
//si se ha mandado el formulario con éxito hacemos el INSERT en la tabla de la BD correspondiente

        if (isset($_POST['idClaseElegida'])) {

            //variables y objetos
            $idClase = $_POST['idClaseElegida'];
//            $usuario= new Persona($datos); //borrar
            $nombreUsuario = Persona::getNombreUsuarioCookie();
            $alumno = Alumno::obtenerAlumno($nombreUsuario);
            if (isset($_POST['confirmarAsistencia'])) {
                try {

//            $alumno= new Alumno($datos, $persona); //borrar
                    echo $alumno->reservarClase($idClase);
                } catch (Exception $exc) {
                    echo $exc->getTraceAsString();
                    echo $exc->getMessage();
                }
            } elseif (isset($_POST['cancelarAsistencia'])) {
                try {
                    echo $alumno->cancelarAsistencia($idClase);
                } catch (Exception $exc) {
                    echo $exc->getTraceAsString();
                    echo $exc->getMessage();
                }
            }
        }

//código para crear o borrar una clase según los datos introducidos por el profesor logueado
        if (isset($_POST['formCrearClase'])) {

            //variables y objetos
//            $usuario= new Persona($datos); //borrar
            $fechaHora = date('Y-m-d H:i:s', strtotime($_POST['fechaHora']));
            $duracion = $_POST['duracion'];
            $usuarioProfesor = $_POST['usuarioProfesor'];

            echo Clase::crearClaseBD($fechaHora, $duracion, $usuarioProfesor);

        }
//código para crear o borrar una clase según los datos introducidos por el profesor logueado
        if (isset($_POST['formBorrarClase'])) {

            //variables y objetos
            $idClase = $_POST['idClase'];
            echo Clase::cancelarClaseBD($idClase);
        }






        /* Comprobamos si el usuario logueado es un profesor para mostrar el formulario para crear y borrar nuevas clases */
        $esProfesor = false;
        if (Profesor::obtenerProfesor($usuario->getUsuario())) {
            $esProfesor = true;
        }

        if ($esProfesor) {
            mostrarFormulariosCrearBorrarClase($clases);
        }

        

        echo'</main>';
        showFooter();
        ?>














    </body>
</html>
