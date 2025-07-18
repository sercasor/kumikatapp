<!DOCTYPE html>
<html>
    <head>
        <title>Kumikatapp - Inscripciones</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="App de gestión para escuelas de artes marciales con la planificación de entrenamientos más detallada. Ahorra tiempo y dedícalo a lo que amas: enseñar">
        <link rel="stylesheet" href="stylesheet.css">
        <!--realizado por Sergio Castillo Ortiz, amante del deporte-->
    </head>
    <body>
        <?php
        require_once 'controller.php';
        session_start();
        showHeader();
        echo'<main>';
        $rangos = Rango::obtenerTodosRangos();
        showLoginSiNoLogueado();
        // Refrescar sesión si la variable no está creada
        $usuario = Persona::renovarPersonaSesion();
        $esProfesor = false;
        if (Profesor::obtenerProfesor($usuario->getUsuario())) {
            $esProfesor = true;
        }
        ?>
        
        <h1>Inscribe tu usuario en la escuela</h1>



        <?php


// Mostrar formularios según tipo de usuario
        if ($esProfesor) {
            showFormularioInscripcionAlu($rangos);    // Puede registrar alumnos
            showFormularioInscripcionProfe();  // También puede registrarse como profesor
            showFormularioborrarPersona();  
            showAlumnosEscuela();
        } else {
            echo'<br>Contenido disponible sólo para profesores. Volver a <a href="index.php">inicio</a>';
            die();
        }
        ?>




        
        <?php
        //si se ha mandado el formulario con éxito hacemos el INSERT en la tabla de la BD correspondiente

        if (isset($_POST['profeRegistrado'])) {
            $salario = $_POST['salario'];
            $fechaContratacion = $_POST['fechaContratacion'];
            $nombreProfe = $_POST['nombreUsuario'];
            try {
                if (Profesor::crearProfesorBD($nombreProfe, $fechaContratacion, $salario)) {
                    echo 'Profesor creado con éxito';
                }
                
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
                echo $exc->getMessage();
            }
        } elseif (isset($_POST['alumnoRegistrado'])) {
            $rangoElegido = $_POST['rangoElegido'];
            $nombreUsuario = $_POST['nombreUsuario'];
            $usuarioAlu = Persona::obtenerPersonaSiExiste($nombreUsuario);
            try {
                $fechaMatriculacion = $_POST['fechaMatriculacion'];
                if (Alumno::crearAlumnoBD($nombreUsuario, $fechaMatriculacion, null, $rangoElegido)) {
                    echo'Alumno creado con éxito';
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
                echo $exc->getMessage();
            }
        } elseif (isset ($_POST['borrarPersonaform'])) {
            $usuario=$_POST['nombreUsuario'];
            try {
                if (Persona::borrarPersonaBD($usuario)) {
                    echo'Usuario borrado con éxito';
                }
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }

            
        }
        echo'</main>';
        showFooter();
        ?>














    </body>
</html>
