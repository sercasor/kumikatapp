<?php

require_once 'model.php';

//realizado por Sergio Castillo Ortiz



function showLoginSiNoLogueado() {
    if (!Persona::getNombreUsuarioCookie()) {
        echo'No te has logueado, puedes hacerlo en la página de <a href="login.php">login</a>';
        die();
    }
}
/**
 * Funcion que comprueba si tenemos un profesor logueado y para la ejecución del programa si no es así
 * @param bool $esProfesor
 */
function contenidoRestringidoProfe($esProfesor,$usuario) {
            if (Profesor::obtenerProfesor($usuario->getUsuario())) {
                $esProfesor = true;
            } else {
                die('<br>Contenido disponible sólo para profesores. Volver a <a href="index.php">inicio</a>');
            }
        }

/**
 * Función para mostrar el header de la web
 * @param type $param
 */
function showHeader() {
    echo '<div class="menuPrincipal">
            <div class="logo">
                <img src="recursos/logo-kumikatapp.png" alt="Logo KumikaTapp"/>
            </div>

            <div class="enlaces">
                <a href="index.php">Inicio</a>
                <a href="calendario.php">Calendario</a>
                <a href="planificar-clase.php">Planificar clase</a>
                <a href="inscripcion.php">Inscripciones</a>
                <a href="mensualidades.php">Mensualidades</a>
                <a href="pagos.php">Pagos</a>
                <a href="contacto.php">Contacto</a>
                <a href="perfil.php" >Perfil</a>
                <a href="login.php" class="active">Log in/Log out</a>
            </div>
        </div>';
}

/**
 * Función para mostrar el footer de la web
 * @param type $param
 */
function showFooter() {
    echo '<footer>
            <p>Solicita la inclusión de tu escuela a través de nuestro  <a href="mailto:info@kumikatapp.es">correo</a> o llamándonos al <a href="tel:655124578">655124578</a> indicando los datos de tu escuela y los profesores.</p>   

        </footer>';
}

function mostrarFormularioCambioCredenciales() {

    echo '
            <h2>Modificar mis datos</h2>
            <form action="perfil.php" method="POST">
            <label for="dni">DNI:</label>
            <input type="text" name="dni" ><br>

            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" ><br>

            <label for="apellido">Apellido:</label>
            <input type="text" name="apellido" ><br>

            <label for="email">Email:</label>
            <input type="email" name="email" ><br>

            <label for="edad">Edad:</label>
            <input type="number" name="edad" ><br>

            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono" ><br>

            <label for="cif_escuela">CIF de la escuela:</label>
            <input type="text" name="cif_escuela" ><br>
            

             <label for="contrasenya">Contraseña:</label>
            <input type="password" name="contrasenya" ><br>

            <button type="submit" name="modificarDatosPersonales">Enviar</button>
        </form>';
}

/**
 * Función para mostrar los datos del usuario
 */
function showUsuario() {
    //Comprobamos si el usuario se ha logueado y devolvemos un obejto Persona en forma de variable de sesión
    $datos = Persona::obtenerPersonaSiLogueado();
    echo "<p>Estos son los datos del usuario encontrado</p>\n
<table border='1'>\n
    <tr>\n
        <th>DNI</th>\n
        <th>Nombre</th>\n
        <th>Apellido</th>\n
        <th>Email</th>\n
        <th>Edad</th>\n
        <th>Nombre Usuario</th>\n
        <th>Contraseña</th>\n
        <th>Teléfono</th>\n
        <th>Cif de la escuela</th>\n
        
    </tr>\n
    <tr>\n
        <td>" . $datos->getDni() . "</td>\n
        <td>" . $datos->getNombre() . "</td>\n
        <td>" . $datos->getApellido() . "</td>\n
        <td>" . $datos->getEmail() . "</td>\n
        <td>" . $datos->getEdad() . "</td>\n
        <td>" . $datos->getUsuario() . "</td>\n
        <td>" . $datos->getContrasenya() . "</td>\n
        <td>" . $datos->getTelefono() . "</td>\n
        <td>" . $datos->getCif() . "</td>\n
    </tr>\n
</table>\n";
}

/**
 * Función para mostrar los pagos del usuario
 */
function showPagosUsuario($usuario) {
    // Comprobamos si el usuario se ha logueado
    $usuario = Persona::obtenerPersonaSiExiste($usuario);
    $pagos = Pago::obtenerTodosPagos($usuario->getUsuario());

    echo "<p>Estos son los pagos del usuario encontrado</p>\n
    <table border='1'>\n
        <tr>\n
            <th>ID del pago</th>\n
            <th>Fecha de pago</th>\n
            <th>Método de pago</th>\n
            <th>Usuario</th>\n
            <th>ID de mensualidad</th>\n
        </tr>\n";

    foreach ($pagos as $pago) {
        echo "<tr>\n
            <td>" . $pago->getIdPago() . "</td>\n
            <td>" . $pago->getFechaPago() . "</td>\n
            <td>" . $pago->getMetodoPago() . "</td>\n
            <td>" . $pago->getUsuario() . "</td>\n
            <td>" . $pago->getIdMensualidad() . "</td>\n
        </tr>\n";
    }

    echo "</table>\n";
}

/**
 * Función para mostrar los alumnos de la escuela a la que pertenece el usuario logueado
 */
function showAlumnosEscuela() {
    $usuario = Persona::getNombreUsuarioCookie();
    //comprobamos que el usuario es profesor
    if (!Profesor::obtenerProfesor($usuario)) {
        throw new Exception('Acceso no permitido');
    }
    // Comprobamos si el usuario se ha logueado
    $alumnos = Alumno::obtenerTodosAlumnos();

    echo "<p>Estos son los usuarios que pertenecen a tu escuela:</p>\n
    <table border='1'>\n
        <tr>\n
            <th>DNI</th>\n
            <th>Nombre</th>\n
            <th>Apellido</th>\n
            <th>Email</th>\n
            <th>Edad</th>\n
            <th>Usuario</th>\n
            <th>Teléfono</th>\n
            <th>Cif de la escuela</th>\n
            <th>Activo</th>\n
        </tr>\n";

    foreach ($alumnos as $alumno) {
        echo "<tr>\n
            <td>" . $alumno->getDni() . "</td>\n
            <td>" . $alumno->getNombre() . "</td>\n
            <td>" . $alumno->getApellido() . "</td>\n
            <td>" . $alumno->getEmail() . "</td>\n
            <td>" . $alumno->getEdad() . "</td>\n
            <td>" . $alumno->getUsuario() . "</td>\n
            <td>" . $alumno->getTelefono() . "</td>\n
            <td>" . $alumno->getCif() . "</td>\n
            <td>" . ($alumno->getActivo() ? 'Si' : 'No') . "</td>\n
        </tr>\n";
    }

    echo "</table>\n";
}

function showFormularioInscripcionAlu($rangos) {
    echo '<form action="inscripcion.php" method="POST">';
    echo '<label for="nombreUsuario">Usuario:</label>';
    echo '<input type="text" id="nombreUsuario" name="nombreUsuario"><br>';

    echo '<label for="fechaMatriculacion">Fecha de matriculación:</label>';
    echo '<input type="date" id="fechaMatriculacion" name="fechaMatriculacion"><br>';

    echo '<label for="rangoElegido">Rango del alumno:</label>';
    echo'<select name="rangoElegido" id="rangoElegido" required><br>';
    foreach ($rangos as $rango) {
        $rangoNombre = $rango['cinturon'];
        $rangoId = $rango['id_rango'];
        echo("<option value='$rangoId'>$rangoNombre</option>");
    }
    echo'</select><br>';
    echo '<button type="submit" name="alumnoRegistrado">Registrar alumno</button><br>';
    echo '</form><br>';
}

function showFormularioInscripcionProfe() {
    echo '<form action="inscripcion.php" method="POST">';
    echo '<label for="nombreUsuario">Usuario:</label>';
    echo '<input type="text" id="nombreUsuario" name="nombreUsuario"><br>';

    echo '<label for="salario">Salario:</label>';
    echo '<input type="number" id="salario" name="salario" min="0" placeholder="0" ><br>';

    echo '<label for="fechaContratacion">Fecha de contratación:</label>';
    echo '<input type="date" id="fechaContratacion" name="fechaContratacion"><br>';

    echo '<button type="submit" name="profeRegistrado">Registrar profesor</button><br>';
    echo '</form><br>';
}
function showFormularioborrarPersona() {
    echo '<form action="inscripcion.php" method="POST">';
    echo '<label for="nombreUsuario">Usuario:</label>';
    echo '<input type="text" id="nombreUsuario" name="nombreUsuario"><br>';

    echo '<button type="submit" name="borrarPersonaform">Borrar usuario</button><br>';
    echo '</form><br>';
}
/**
         * Función exclusiva para profesores. Sirve para mostrar un formulario que permite crear o borrar nuevas clases a las que los alumnos posteriormente se apuntarán mediante el formulario correspondiente.
         */
        function mostrarFormulariosCrearBorrarClase($clases) {


            echo '<p>Futuras clases:</p>';
            foreach ($clases as $clase) {
                $claseFecha = new DateTime($clase->getFechaHora());
                $claseFechaFormateada = $claseFecha->format("d/m/Y - H:i:s");
                $claseId = $clase->getIdClase();
                echo("<p>id clase: $claseId Fecha y hora: $claseFechaFormateada</p>");
            }

            echo '
            <h2>Crear nueva clase</h2>
            <form action="calendario.php" method="POST">
            
            <label for="fechaHora">Fecha y hora:</label>
            <input type="datetime-local" name="fechaHora" required><br>
            
            <label for="duracion">Duración en minutos:</label>
            <input type="number" name="duracion" ><br>

            <label for="usuarioProfesor">Usuario del profesor que imparte clase:</label>
            <input type="text" name="usuarioProfesor" ><br>
            
            <button type="submit" name="formCrearClase">Enviar</button>
        </form>
            <h2>Borrar clase</h2>
            <form action="calendario.php" method="POST">
            
            <label for="idClase">Id de clase:</label>
            <input type="number" name="idClase" ><br>
            
            <button type="submit" name="formBorrarClase">Borrar clase</button>
        </form>';
        }
?>