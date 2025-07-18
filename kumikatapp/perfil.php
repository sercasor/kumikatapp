<!DOCTYPE html>
<html>
    <head>
        <title>Kumikatapp - Perfil</title>
        <!--realizado por Sergio Castillo Ortiz, amante del deporte-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="App de gestión para escuelas de artes marciales con la planificación de entrenamientos más detallada. Ahorra tiempo y dedícalo a lo que amas: enseñar">
        <link rel="stylesheet" href="stylesheet.css">
    </head>
    <body>
        <?php
        require_once 'controller.php';
        session_start();
        showHeader();
        echo '<main>';
        showLoginSiNoLogueado()
        ?>
        <h1>Mi perfil</h1>





        <?php
        if (isset($_SESSION["persona"])) {
            echo "Bienvenido/a, " . Persona::getNombreUsuarioCookie() . "\n";
        }


        showUsuario();
        mostrarFormularioCambioCredenciales();

        if (isset($_POST['modificarDatosPersonales'])) {
            // Actualizar el objeto con los nuevos datos
            if (isset($_SESSION["persona"])) {
                $persona = $_SESSION["persona"];
                $persona->setDni($_POST["dni"]);
                $persona->setNombre($_POST["nombre"]);
                $persona->setApellido($_POST["apellido"]);
                $persona->setEmail($_POST["email"]);
                $persona->setEdad($_POST["edad"]);
                $persona->setTelefono($_POST["telefono"]);
                $persona->setCif($_POST["cif_escuela"]);
                $persona->setContrasenya($_POST["contrasenya"]);

                // Guardar en la base de datos
                $mensaje = $persona->modificarDatosPersonalesBD($persona);

                // Refrescar sesión
                $_SESSION["persona"] = Persona::obtenerPersonaSiExiste($persona->getUsuario());

                echo "<p>$mensaje</p>";
                header('Location: perfil.php');
            }
        }
        echo '</main>';
        showFooter();
        ?>












    </body>
</html>
