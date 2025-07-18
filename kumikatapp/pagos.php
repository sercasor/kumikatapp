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
        

        try {
            showAlumnosEscuela();
            $persona = Persona::renovarPersonaSesion();
            $cif_escuela = $persona->getCif();
            $mensualidades = Mensualidad::obtenerTodasMensualidades($cif_escuela);
        } catch (Exception $exc) {
            echo $exc->getMessage();
            echo'<br>Contenido disponible sólo para profesores. Volver a <a href="index.php">inicio</a>';
            die();
        }
        ?>
        <h1>Pagos</h1>

        <h2>Registra el pago de un alumno</h2>

        <form action="pagos.php" method="POST">
            <label for="nombreUsuario">Usuario:</label>
            <input type="text" id="nombreUsuario" name="nombreUsuario">
            <label for="mensualidadElegida">Selecciona una mensualidad:</label>
            <select name="mensualidadElegida" id="mensualidadElegida" required>
                <?php
                foreach ($mensualidades as $mensualidad) {
                    $mensualidadNombre = $mensualidad->getNombre(); 
                    $mensualidadId = $mensualidad->getIdMensualidad(); 
                    echo("<option value='$mensualidadId'>$mensualidadNombre</option>");
                }
                ?>

            </select>

            <button type="submit" name="formPago">Registrar pago</button>
        </form>

        <h2>Borrar un pago</h2>
        <form action="pagos.php" method="POST"> 
            <label for="idPago">Id del pago:</label>
            <input type="number" id="idPago" name="idPago" ><br>
            <label for="nombreUsuario">Usuario:</label>
            <input type="text" id="nombreUsuario" name="nombreUsuario">
            <button type="submit" name="borrarPagoForm">Borrar</button>
        </form>
        <h2>Historial de pagos del usuario</h2>
        <form action="pagos.php" method="POST"> 
            <label for="nombreUsuario">Usuario:</label>
            <input type="text" id="nombreUsuario" name="nombreUsuario">

            <button type="submit" name="historialPagosForm">Consultar historial de pagos</button>
        </form>


        <?php
//si se ha mandado el formulario con éxito hacemos el INSERT en la BD del pago

        if (isset($_POST['formPago'])) {
            $idMensualidadElegida = $_POST['mensualidadElegida'];
            $usuario = $_POST['nombreUsuario'];
            $metodoPago = "efectivo";
            $fechaPago = date("Y-m-d");

            Pago::crearPagoBD($usuario, $idMensualidadElegida, $fechaPago, $metodoPago);
        }
        if (isset($_POST['borrarPagoForm'])) {
            try {
                $idPago = $_POST['idPago'];
                $usuario = $_POST['nombreUsuario'];
                Pago::borrarPagoBD($idPago, $usuario);
            } catch (Exception $exc) {
                echo $exc->getMessage();
            }
        }
        if (isset($_POST['historialPagosForm'])) {
            $usuario = $_POST['nombreUsuario'];
            showPagosUsuario($usuario);
        }
        echo '</main>';
        showFooter();
        ?>














    </body>
</html>
