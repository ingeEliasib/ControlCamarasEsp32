
<?php
$servername = "localhost";
$dBUsername = "UsarioBD";
$dBPassword = "4(M(&g6!RjzK2c6{";
$dBName = "bd_esp32";

// Crear conexión
$conn = new mysqli($servername, $dBUsername, $dBPassword, $dBName);

// Comprobar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Manejar el toggling del LED
if (isset($_POST['toggle_LED'])) {
    $sql = "SELECT estado FROM controlcamaras;";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    if ($row['estado'] == 0) {
        $update = $conn->query("UPDATE controlcamaras SET estado = 1 WHERE idcamara = 1;");        
    } else {
        $update = $conn->query("UPDATE controlcamaras SET estado = 0 WHERE idcamara = 1;");        
    }
}

// Consultar el estado actual del Servo Zoom
if (isset($_POST['check_SERVOZOOM_status'])) {
    $sql = "SELECT Estado FROM servomotores WHERE NobreServoMotor = 'ServoZoom';";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['Estado'] == 0) {
        echo "Zoom_Espera";
    } else if ($row['Estado'] == 1){
        echo "Zoom_Mas";
    } else if ($row['Estado'] == 2){
        echo "Zoom_Menos";
    }
}

if (isset($_POST['Mover_ServoZoom']) && isset($_POST['nuevo_estado'])) {
    $nuevoEstado = intval($_POST['nuevo_estado']);
    $update = $conn->query("UPDATE servomotores SET Estado = $nuevoEstado WHERE NobreServoMotor = 'ServoZoom';");
}


if (isset($_POST['Poner_Espera_ServoZoom']) ) {
    $update = $conn->query("UPDATE servomotores SET Estado = 0 WHERE  NobreServoMotor = 'ServoZoom';");
}

#******** Gestion de servo horizontal *************
if (isset($_POST['check_SerMovHorizontal_status'])) {
    # SELECT * FROM `servomotores` WHERE NobreServoMotor='ServoMovHorizontal';
    $sql = "SELECT * FROM `servomotores` WHERE NobreServoMotor='ServoMovHorizontal';";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    $estado=$row['Estado'];
   
}
if(isset($_POST['Mover_ServoHorizontal']) && isset($_POST['Nuevo_Movimiento'])){
    $nuevoEstado = intval($_POST['Nuevo_Movimiento']);
    $update = $conn->query("UPDATE servomotores SET Estado = $nuevoEstado WHERE  NobreServoMotor = 'ServoMovHorizontal';");
    if ($update) {
       echo "" . $nuevoEstado;
    }
}
#***************************************

// Obtener el estado actual del LED
$sql = "SELECT estado FROM controlcamaras WHERE idcamara = 1;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

// Obtener el estado del Servo Zoom
$sql = "SELECT Estado FROM `servomotores` WHERE `NobreServoMotor`='ServoZoom';"; 
$result = $conn->query($sql);
$rowServo = $result->fetch_assoc();

$sql = "SELECT Estado FROM `servomotores` WHERE `NobreServoMotor`='ServoMovHorizontal';"; 
$result = $conn->query($sql);
$rowServoHorizontal = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Control Cámaras Servo Led</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
    <style>
        /* Estilos básicos para el menú, cuerpo y pie */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
        }
        /* Esconde el checkbox */
        .menu-toggle {
            display: none;
        }

        /* Icono del menú */
        .menu-icon {
            font-size: 24px;
            color: white;
            cursor: pointer;
            display: none; /* Oculto en pantallas grandes */
            text-align: center;
        }
        nav {
            margin: 10px 0;
        }
        nav a {
            margin: 0 15px;
            color: white;
            text-decoration: none;
        }
        .control-container {
            display: flex; /* Utiliza Flexbox */
            justify-content: center; /* Espacio alrededor de los elementos */
            align-items: center; /* Centra los elementos verticalmente */
            margin: 20px; /* Margen para el contenedor */
            background-color:brown;
        }
        /* .led-container, .servo-zoom-container {
            margin: 0 20px; 
        } */
        .led-container {
            text-align: center; /* Centra el contenedor */
            margin: 20px 0; /* Margen para separación */
            background-color: #45a049;
        }

        .led-button {
            width: 50px; /* Ajusta el ancho del botón */
            height: 50px; /* Ajusta la altura del botón */
            border-radius: 50%; /* Hace que el botón sea circular */
            border: none; /* Sin borde */
            color: white; /* Color del texto */
            font-size: 12px; /* Tamaño de la fuente */
            cursor: pointer; /* Cambiar el cursor al pasar sobre el botón */
            transition: background-color 0.3s; /* Transición suave para el color de fondo */
        }
        .led-status {
             margin-top: 5px; /* Espacio entre el botón y el texto */
             font-size: 14px; /* Tamaño de fuente */
             color: black; /* Color del texto */
        }
        .contenedor_Espacio {
             margin: 10px; /* Espacio entre botones */
             padding: 10px; /* Espaciado interno, opcional */
             border: 2px solid transparent; /* Borde transparente para mantener el espacio sin color */
             border-radius: 5px; /* Esquinas redondeadas, opcional */
             box-shadow: 0 0 0 2px azure; /* Crea el efecto de borde utilizando sombra */
         }

        .servo-zoom-container {
            background-color: aqua;
            text-align: center; /* Centra el texto en el contenedor */
            margin: 20px 0; /* Margen para separación */
            display: flex; /* Cambia a flexbox para alinear los elementos en fila */
            justify-content: center; /* Centra los botones horizontalmente */
            gap: 10px; /* Espacio entre los botones, ajusta el valor según prefieras */
        }

        .zoom-button {
            margin: 10px; /* Espacio entre botones */
            padding: 10px 20px; /* Espaciado interno */
            font-size: 14px; /* Tamaño de la fuente */
            cursor: pointer; /* Cambiar el cursor al pasar sobre el botón */
        }
        main {
            padding: 20px;
            text-align: center; /* Alinear contenido en el centro */
        }
        .button-container {
        display: flex;
        flex-wrap: wrap; /* Permite que los botones se envuelvan en varias filas */
        justify-content: center; /* Centra los botones en el contenedor */
        max-width: 600px; /* Ancho máximo del contenedor */
        margin: auto; /* Centra el contenedor horizontalmente */
            background-color: blue; /* Color de fondo */
        }

        .servo-button {
            margin: 10px; /* Espacio entre botones */
            padding: 10px 15px; /* Tamaño del botón */
            font-size: 16px; /* Tamaño de la fuente */
            cursor: pointer; /* Cambiar cursor al pasar sobre el botón */
            border: none; /* Sin borde */
            border-radius: 5px; /* Bordes redondeados */
            background-color: #4CAF50; /* Color de fondo */
            color: white; /* Color del texto */
            transition: background-color 0.3s; /* Transición suave para el color de fondo */
            flex: 1 1 calc(25% - 20px); /* Permite un máximo de 4 botones por fila en pantallas grandes */
        }
                .servo-button:hover {
            background-color: #45a049; /* Color de fondo al pasar el mouse */
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px;
            position: relative;
            bottom: 0;
            width: 100%;
        }
        @media only screen and (min-width: 601px) {
            .menu {
                display: flex;
                flex-direction: row; /* Alinea el menú horizontalmente en escritorio */
                gap: 15px;
            }
            .menu-icon {
                display: none;
            }
            /* Estilos para la versión de escritorio */
            .control-container {
                flex-direction: row; /* Alinea los elementos en fila en escritorio */
            }
        
            .led-container, .servo-zoom-container {
                flex: 1; /* Ajusta el espacio para cada contenedor */
                margin: 0 20px; /* Añade margen lateral */
            }
        
            .button-container {
                max-width: 600px; /* Ajusta el ancho para mostrar más botones en fila */
            }
        
            .led_img, .ServoZoom_img {
                display: block; /* Muestra las imágenes solo en la versión de escritorio */
            }
            .servo-button {
                 width: 80px; /* Ajusta el ancho en pantallas pequeñas */
                 height: auto; /* Permite altura flexible en móviles */
                 font-size: 14px; /* Reduce aún más el tamaño de la fuente */
                 padding: 8px 12px; /* Reduce el padding para pantallas móviles */
             }
         
             .button-container {
                 max-width: 100%; /* Ocupa todo el ancho en pantallas pequeñas */
                 gap: 5px; /* Reduce el espacio entre botones */
             }
        }
        /* Estilos para pantallas pequeñas */
        @media (max-width: 600px) {
            .menu {
                display: none; /* Oculta el menú por defecto */
            }
            .menu-icon {
                display: block; /* Muestra el icono del menú en móviles */
            }
            /* Muestra el menú cuando el checkbox está marcado */
            .menu-toggle:checked + .menu-icon + .menu {
                display: flex;
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
  
    <!-- Menú (Header) -->
    <header>
        <h1>Control Cámaras Servo Led</h1>
        <input type="checkbox" id="menu-toggle" class="menu-toggle">
            <label for="menu-toggle" class="menu-icon">&#9776;</label>
                 <nav class="menu">
                     <a href="#inicio">Inicio</a>
                     <a href="#camaras">Cám Principal</a>
                     <a href="#camaras">Cám Músicos</a>
                     <a href="#camaras">Cám Oficina</a>
                     <a href="#contacto">Contacto</a>
                 </nav>
    </header>

    <!-- Cuerpo (Main) -->
    <main>
        <h1>El estado del led es: <?php echo $row['estado']; ?></h1>
        <h3>El estado del Servo Zoom: <?php echo $rowServo['Estado']; ?></h3>
        <h3>El estado del Servo Horizontal: <?php echo $rowServoHorizontal['Estado']; ?></h3>
        <div class="control-container"> <!-- Nuevo contenedor -->
            <div class="led-container">
                <form action="index.php" method="post" id="LED" enctype="multipart/form-data">
                    <!-- <input id="submit_button" type="submit" name="toggle_LED" value="Led" class="led-button"  -->
                    <input id="submit_button" type="submit" name="toggle_LED" value=" " class="led-button" style="background-color: <?php echo $row['estado'] == 0 ? 'red' : 'green'; ?>;" />
                    <p class="led-status">Estado</p> <!-- Texto agregado -->
                </form>
            </div>  
            <div class="contenedor_Espacio">
                <!-- Nuevo contenedor -->
            </div>       
             
            <!-- Botones Zoom In y Zoom Out -->
            <div class="servo-zoom-container">
                <form action="index.php" method="post" id="SERVOZOOMMAS" enctype="multipart/form-data">
                <input type="hidden" name="nuevo_estado" value="2" /> 
                <input class="zoom-button" id="ServoZoom_button" type="submit" name="Mover_ServoZoom" value="ServoZoom +" />
                <!-- <input type="hidden" name="nuevo_estado" value="2" /> 
                    <input id="ServoZoom_button" type="submit" name="Mover_ServoZoom" value="ServoZoom +" class="zoom-button"  /> -->
                </form>
                <form action="index.php" method="post" id="SERVOZOOMMENOS" enctype="multipart/form-data">
                    <input type="hidden" name="nuevo_estado" value="1" /> 
                     <input class="zoom-button" id="ServoZoom_button" type="submit" name="Mover_ServoZoom" value="ServoZoom -" />
                <!-- <input type="hidden" name="nuevo_estado" value="1" /> 
                    <input id="ServoZoom_button" type="submit" name="Mover_ServoZoom" value="ServoZoom -" class="zoom-button"  /> -->
                </form>
              
            </div>
            
        </div>      
        

        <div class="button-container">  
            <!-- Botón 5 -->
            <form action="index.php" method="post" id="servoHorizontalForm5">
                <input type="hidden" name="Nuevo_Movimiento" value="5" />
                <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="5" />
            </form>
    
            <!-- Botón 10 -->
            <form action="index.php" method="post" id="servoHorizontalForm10">
                <input type="hidden" name="Nuevo_Movimiento" value="10" />
                <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="10" />
            </form>
    
            <!-- Botón 15 -->
            <form action="index.php" method="post" id="servoHorizontalForm15">
                <input type="hidden" name="Nuevo_Movimiento" value="15" />
                <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="15" />
            </form>
    
            <!-- Botón 20 -->
            <form action="index.php" method="post" id="servoHorizontalForm20">
                <input type="hidden" name="Nuevo_Movimiento" value="20" />
                <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="20" />
            </form>
    
            <!-- Botón 25 -->
            <form action="index.php" method="post" id="servoHorizontalForm25">
                <input type="hidden" name="Nuevo_Movimiento" value="25" />
                <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="25" />
            </form>
    
            <!-- Botón 30 -->
            <form action="index.php" method="post" id="servoHorizontalForm30">
                <input type="hidden" name="Nuevo_Movimiento" value="30" />
                <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="30" />
            </form>

            <!-- Botón 35 -->
<form action="index.php" method="post" id="servoHorizontalForm35">
    <input type="hidden" name="Nuevo_Movimiento" value="35" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="35" />
</form>

<!-- Botón 40 -->
<form action="index.php" method="post" id="servoHorizontalForm40">
    <input type="hidden" name="Nuevo_Movimiento" value="40" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="40" />
</form>

<!-- Botón 45 -->
<form action="index.php" method="post" id="servoHorizontalForm45">
    <input type="hidden" name="Nuevo_Movimiento" value="45" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="45" />
</form>

<!-- Botón 50 -->
<form action="index.php" method="post" id="servoHorizontalForm50">
    <input type="hidden" name="Nuevo_Movimiento" value="50" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="50" />
</form>

<!-- Botón 55 -->
<form action="index.php" method="post" id="servoHorizontalForm55">
    <input type="hidden" name="Nuevo_Movimiento" value="55" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="55" />
</form>

<!-- Botón 60 -->
<form action="index.php" method="post" id="servoHorizontalForm60">
    <input type="hidden" name="Nuevo_Movimiento" value="60" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="60" />
</form>

<!-- Botón 65 -->
<form action="index.php" method="post" id="servoHorizontalForm65">
    <input type="hidden" name="Nuevo_Movimiento" value="65" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="65" />
</form>

<!-- Botón 70 -->
<form action="index.php" method="post" id="servoHorizontalForm70">
    <input type="hidden" name="Nuevo_Movimiento" value="70" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="70" />
</form>

<!-- Botón 75 -->
<form action="index.php" method="post" id="servoHorizontalForm75">
    <input type="hidden" name="Nuevo_Movimiento" value="75" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="75" />
</form>

<!-- Botón 80 -->
<form action="index.php" method="post" id="servoHorizontalForm80">
    <input type="hidden" name="Nuevo_Movimiento" value="80" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="80" />
</form>

<!-- Botón 85 -->
<form action="index.php" method="post" id="servoHorizontalForm85">
    <input type="hidden" name="Nuevo_Movimiento" value="85" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="85" />
</form>

<!-- Botón 90 -->
<form action="index.php" method="post" id="servoHorizontalForm90">
    <input type="hidden" name="Nuevo_Movimiento" value="90" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="90" />
</form>

<!-- Botón 95 -->
<form action="index.php" method="post" id="servoHorizontalForm95">
    <input type="hidden" name="Nuevo_Movimiento" value="95" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="95" />
</form>

<!-- Botón 100 -->
<form action="index.php" method="post" id="servoHorizontalForm100">
    <input type="hidden" name="Nuevo_Movimiento" value="100" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="100" />
</form>

<!-- Botón 105 -->
<form action="index.php" method="post" id="servoHorizontalForm105">
    <input type="hidden" name="Nuevo_Movimiento" value="105" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="105" />
</form>

<!-- Botón 110 -->
<form action="index.php" method="post" id="servoHorizontalForm110">
    <input type="hidden" name="Nuevo_Movimiento" value="110" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="110" />
</form>

<!-- Botón 115 -->
<form action="index.php" method="post" id="servoHorizontalForm115">
    <input type="hidden" name="Nuevo_Movimiento" value="115" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="115" />
</form>

<!-- Botón 120 -->
<form action="index.php" method="post" id="servoHorizontalForm120">
    <input type="hidden" name="Nuevo_Movimiento" value="120" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="120" />
</form>

<!-- Botón 125 -->
<form action="index.php" method="post" id="servoHorizontalForm125">
    <input type="hidden" name="Nuevo_Movimiento" value="125" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="125" />
</form>

<!-- Botón 130 -->
<form action="index.php" method="post" id="servoHorizontalForm130">
    <input type="hidden" name="Nuevo_Movimiento" value="130" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="130" />
</form>

<!-- Botón 135 -->
<form action="index.php" method="post" id="servoHorizontalForm135">
    <input type="hidden" name="Nuevo_Movimiento" value="135" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="135" />
</form>

<!-- Botón 140 -->
<form action="index.php" method="post" id="servoHorizontalForm140">
    <input type="hidden" name="Nuevo_Movimiento" value="140" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="140" />
</form>

<!-- Botón 145 -->
<form action="index.php" method="post" id="servoHorizontalForm145">
    <input type="hidden" name="Nuevo_Movimiento" value="145" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="145" />
</form>

<!-- Botón 150 -->
<form action="index.php" method="post" id="servoHorizontalForm150">
    <input type="hidden" name="Nuevo_Movimiento" value="150" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="150" />
</form>

<!-- Botón 155 -->
<form action="index.php" method="post" id="servoHorizontalForm155">
    <input type="hidden" name="Nuevo_Movimiento" value="155" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="155" />
</form>

<!-- Botón 160 -->
<form action="index.php" method="post" id="servoHorizontalForm160">
    <input type="hidden" name="Nuevo_Movimiento" value="160" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="160" />
</form>

<!-- Botón 165 -->
<form action="index.php" method="post" id="servoHorizontalForm165">
    <input type="hidden" name="Nuevo_Movimiento" value="165" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="165" />
</form>

<!-- Botón 170 -->
<form action="index.php" method="post" id="servoHorizontalForm170">
    <input type="hidden" name="Nuevo_Movimiento" value="170" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="170" />
</form>

<!-- Botón 175 -->
<form action="index.php" method="post" id="servoHorizontalForm175">
    <input type="hidden" name="Nuevo_Movimiento" value="175" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="175" />
</form>

<!-- Botón 180 -->
<form action="index.php" method="post" id="servoHorizontalForm180">
    <input type="hidden" name="Nuevo_Movimiento" value="180" />
    <input class="servo-button" id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="180" />
</form>

    
        </div>
    </main>

    <!-- Pie (Footer) -->
    <footer>
        <p>&copy; 2024 Control Cámaras Servo Led. Todos los derechos reservados.</p>
        <p><a href="#politica" style="color: white;">Política de Privacidad</a> | <a href="#terminos" style="color: white;">Términos y Condiciones</a></p>
    </footer>
  
</body>
</html>