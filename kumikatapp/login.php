<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kumikatapp - App de gestión eficiente de escuelas de artes marciales</title>
        <meta name="description" content="App de gestión para escuelas de artes marciales con la planificación de entrenamientos más detallada. Ahorra tiempo y dedícalo a lo que amas: enseñar">
        <!--realizado por Sergio Castillo Ortiz, amante del deporte-->
        <link rel="stylesheet" href="stylesheet.css">
    </head>
    <body>
        <?php
        require_once 'controller.php';
        /* DECLARACIÓN DE VARIABLES */
        $cookie_nombre = "cookieBrownie";
        $contrasenyaAceptada = false;
        showHeader();
        echo '<main>';
        session_start();
        //eliminamos cookie de login si el usuario le ha dado al botón de logout estando logueado
        if (isset($_POST['logoutRegistroBotones'])) {
            try {
                if (Persona::hayUsuarioLogueado()) {
                    echo Persona::borrarCookieYSesionLogin($cookie_nombre);
                }
            } catch (Exception $exc) {
                echo $exc->getMessage();
                die();
            }
        }


        /* Función para mostrar las opciones de Login, pasar al controller al finalizar */

        function mostrarFormularioLoginRegistro() {

            $_SESSION["tipoFormulario"] = $_POST['loginRegistroBotones'];

            echo '<form action="login.php" method="POST">';

            if ($_SESSION["tipoFormulario"] == "Registro") {
                echo '
            <label for="dni">DNI:</label>
            <input type="text" name="dni" required><br>

            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" required><br>

            <label for="apellido">Apellido:</label>
            <input type="text" name="apellido" required><br>

            <label for="email">Email:</label>
            <input type="email" name="email" required><br>

            <label for="edad">Edad:</label>
            <input type="number" name="edad" required><br>

            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono" required><br>

            <label for="cif_escuela">CIF de la escuela:</label>
            <input type="text" name="cif_escuela" required><br>
        ';
            }

            echo '
        <label for="usuario">Usuario:</label>
        <input type="text" name="usuario" required><br>

        <label for="contrasenya">Contraseña:</label>
        <input type="password" name="contrasenya" required><br>

        <button type="submit">Enviar</button>
    </form>';
        }

        //comprobamos si no se ha rellenadoel login o registro para mostrar los botones previos
        if (!isset($_COOKIE[$cookie_nombre]) || !$contrasenyaAceptada) {
            echo'<h1>Identifícate o crea un nuevo usuario</h1>';
            //Comprueba si el usuario ha elegido loguearse o registrarse como nuevo usuario y muestra los botones para elegir formulario 
            echo'<form  action="login.php" method="POST">
        <button name="loginRegistroBotones" type="submit" value="Login">Tengo una cuenta</button>
        <button name="loginRegistroBotones" type="submit" value="Registro">Crear nuevo usuario</button>
        <button name="logoutRegistroBotones" type="submit" value="logout">Cerrar sesión</button>
    </form>';
            if (isset($_POST['loginRegistroBotones'])) {
                mostrarFormularioLoginRegistro();
            }

            /* si se ha mandado el nombre de usuario a través del formulario creamos cookie, creamos usuario y le mandamos al perfil */
            if (isset($_POST['usuario'])) {
                
                $nombreUsuario = $_POST['usuario'];
                $contrasenya = $_POST['contrasenya'];

                // Guardamos la cookie
                setcookie($cookie_nombre, $nombreUsuario, time() + (86400 * 30), "/"); //30 días
                // Si es formulario de registro, creamos nueva Persona y la insertamos
                if ($_SESSION["tipoFormulario"] == "Registro") {
                    $dni = $_POST["dni"];
                    $nombre = $_POST["nombre"];
                    $apellido = $_POST["apellido"];
                    $email = $_POST["email"];
                    $edad = $_POST["edad"];
                    $telefono = $_POST["telefono"];
                    $cif_escuela = $_POST["cif_escuela"];

                    // Creamos la nueva Persona
                    // Insertamos en la BD
                    if (Persona::crearPersonaBD($nombreUsuario, $contrasenya, $cif_escuela, $dni, $nombre, $apellido, $email, $edad, $telefono)) {
                        echo "El usuario {$nombreUsuario} ha sido creado con éxito.<br>";
                    } else {
                        die("Error al crear el usuario. Puede que el DNI o el usuario ya existan.");
                    }
                }


                if (Persona::comprobarContrasenyaExistente($nombreUsuario, $contrasenya)) {
                    $contrasenyaAceptada = true;
                } else {
                    die('Error: contraseña incorrecta, por favor, <a href="login.php">inténtalo de nuevo</a>');
                }


                echo 'Login realizado con éxito. Visitar tu <a href="perfil.php">perfil</a>';
            }
        }
        //si la cookie se ha creado, mandamos al usuario a su perfil
        if (isset($_COOKIE[$cookie_nombre]) && $contrasenyaAceptada) {
            header('Location: perfil.php');
            die;
        }
        echo '</main>';
        showFooter();
        ?>












    </body>
</html>
