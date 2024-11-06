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
    <title>Control Camaras Servo Led</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
    <style>
        .wrapper {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding-top: 20px;
        }
        .col_3 {
            flex: 1 1 100%;
            max-width: 300px;
            margin: 10px;
            text-align: center;
        }
        /* #submit_button, #ServoZoom_button {
            background-color: #2bbaff; 
            color: #FFF; 
            font-weight: bold; 
            font-size: 20px; 
            border-radius: 15px;
            text-align: center;
            padding: 10px 20px;
            margin-top: 10px;
        } */
        .led_img, .ServoZoom_img {
            display: flex;
            justify-content: center;
            align-items: center;
            height: auto; /* Mantiene la proporción de las imágenes */
            width: 100%; /* Asegura que ocupen el ancho del contenedor */
        }
        .ServoZoom_img img {
            max-width: 50%; /* Mantiene la imagen dentro de los límites del contenedor */
            height: auto; /* Mantiene la proporción de las imágenes */
            object-fit: cover; /* Asegura que las imágenes se recorten adecuadamente */
        }
        .led_img img {
            max-width: 30%; /* Mantiene la imagen dentro de los límites del contenedor */
            height: auto; /* Mantiene la proporción de las imágenes */
            object-fit: cover; /* Asegura que las imágenes se recorten adecuadamente */
        }
        .image-container {
            display: flex;
            justify-content: center;
            gap: 10px; /* Espacio entre las imágenes */
        }
        @media only screen and (min-width: 601px) {
            /* Estilos para la versión de escritorio */
            .led_img, .ServoZoom_img {
                display: block; /* Muestra las imágenes solo en escritorio */
            }
        }
    </style>
</head>
<body>
    <div class="wrapper" id="refresh">
        <div class="col_3">
            <h1>El estado del led es: <?php echo $row['estado']; ?></h1>
            <h3>El estado del Servo Zoom: <?php echo $rowServo['Estado']; ?></h3>
            <h3>El estado del Servo Horizontal: <?php echo $rowServoHorizontal['Estado']; ?></h3>
            <div class="image-container">
                <div class="led_img">
                    <img id="contest_img" src="<?php echo $row['estado'] == 0 ? 'led_off.png' : 'led_on.png'; ?>" loading="lazy">
                </div>
                <div class="ServoZoom_img">
                    <img id="contest_imgServo" src="<?php echo $rowServo['Estado'] == 2 ? 'jpg/90.webp' : 'jpg/0.webp'; ?>" loading="lazy">
                </div>
            </div>
            <form action="index.php" method="post" id="LED" enctype="multipart/form-data">			
                <input id="submit_button" type="submit" name="toggle_LED" value="Led" />
            </form>
            <form action="index.php" method="post" id="SERVOZOOMMAS" enctype="multipart/form-data">
                <input type="hidden" name="nuevo_estado" value="2" /> 
                <input id="ServoZoom_button" type="submit" name="Mover_ServoZoom" value="ServoZoom +" />
            </form>
            <form action="index.php" method="post" id="SERVOZOOMMENOS" enctype="multipart/form-data">
                <input type="hidden" name="nuevo_estado" value="1" /> 
                <input id="ServoZoom_button" type="submit" name="Mover_ServoZoom" value="ServoZoom -" />
            </form>

            <!-- <form action="index.php" method="post" id="servoHorizontalForm">
                <div>
                    <?php
                    for ($i = 5; $i <= 30; $i += 5) {
                        echo'<input type="hidden" name="Nuevo_Movimiento" value="' . $i . '" />';
                        echo'<input id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="' . $i . '" />';

                        // echo '<input type="submit" name="Mover_ServoHorizontal" value="' . $i . '" />';
                        // echo '<input type="hidden" name="nuevo_estado" value="' . $i . '" />';
                    }
                    ?>
                </div>
            </form> -->
            <!-- Botón 5 -->
        <form action="index.php" method="post" id="servoHorizontalForm5">
            <input type="hidden" name="Nuevo_Movimiento" value="5" />
            <input id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="5" />
        </form>
                        
        <!-- Botón 10 -->
        <form action="index.php" method="post" id="servoHorizontalForm10">
            <input type="hidden" name="Nuevo_Movimiento" value="10" />
            <input id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="10" />
        </form>
                        
        <!-- Botón 15 -->
        <form action="index.php" method="post" id="servoHorizontalForm15">
            <input type="hidden" name="Nuevo_Movimiento" value="15" />
            <input id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="15" />
        </form>
                        
        <!-- Botón 20 -->
        <form action="index.php" method="post" id="servoHorizontalForm20">
            <input type="hidden" name="Nuevo_Movimiento" value="20" />
            <input id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="20" />
        </form>
                        
        <!-- Botón 25 -->
        <form action="index.php" method="post" id="servoHorizontalForm25">
            <input type="hidden" name="Nuevo_Movimiento" value="25" />
            <input id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="25" />
        </form>
                        
        <!-- Botón 30 -->
        <form action="index.php" method="post" id="servoHorizontalForm30">
            <input type="hidden" name="Nuevo_Movimiento" value="30" />
            <input id="ServoZoom_button" type="submit" name="Mover_ServoHorizontal" value="30" />
        </form>
          
        </div>
    </div>

    <?php $conn->close(); ?>
</body>
</html>
