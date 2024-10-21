<!-- Esta versión del Index de momento solo es para
poder controlar el led del ESP32 con intervención
de Mysql y despues controlar los dos servomotores -->

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
    $sql = "SELECT Estado FROM servomotores WHERE NobreServoMotor = ServoZoom;";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['Estado'] == 0) {
        echo "Zoom_Espera";
    } else if ($row['Estado'] == 1){
        echo "Zoom_Mas";
    }else if ($row['Estado'] == 2){
        echo "Zoom_Menos";
    }
}

if (isset($_POST['Mover_ServoZoom']) && isset($_POST['nuevo_estado'])) {
    $nuevoEstado = intval($_POST['nuevo_estado']);
    $update = $conn->query("UPDATE servomotores SET Estado = $nuevoEstado WHERE NobreServoMotor = 'ServoZoom';");

    if ($update) {
        echo "Estado del ServoZoom actualizado a: " . $nuevoEstado;
    } else {
        echo "Error al actualizar el estado del ServoZoom: " . $conn->error;
    }
}


// Obtener el estado actual del LED
$sql = "SELECT estado FROM controlcamaras WHERE idcamara = 1;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

// Obtener el estado del Servo Zoom
$sql = "SELECT Estado FROM `servomotores`where `NobreServoMotor`='servozoom';"; 
$result = $conn->query($sql);
$rowServo = $result->fetch_assoc();
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
            width: 100%;
            padding-top: 50px;
        }
        .col_3 {
            width: 33.3333333%;
            float: left;
            min-height: 1px;
        }
        #submit_button {
            background-color: #2bbaff; 
            color: #FFF; 
            font-weight: bold; 
            font-size: 40px; 
            border-radius: 15px;
            text-align: center;
        }
        #ServoZoom_button {
            background-color: red; 
            color: #FFF; 
            font-weight: bold; 
            font-size: 40px; 
            border-radius: 15px;
            text-align: center;
        }
        .led_img {
            height: 50%;		
            width: 100%;
            object-fit: cover;
            object-position: center;
            background-color: #2bbaff;
        }
        .ServoZoom_img {
            height: 100%;		
            width: 100%;
            object-fit: cover;
            object-position: center;
            background-color: #000000; 
        }
        @media only screen and (max-width: 600px) {
            .col_3 {
                height: 100%;	
                width: 100%;
                color: #FFF; 
                
            }
            .wrapper {
                padding-top: 5px;
            }
            .led_img {
                height: 50%;		
                width: 50%;
                object-fit: cover;
                object-position: center;
                background-color: #2bbaff;
            }
            .ServoZoom_img {
                height: 50%;		
                width: 50%;
                object-fit: cover;
                object-position: center;
                background-color: #000000; 
            }
        }
    </style>
</head>
<body>
    <div class="wrapper" id="refresh">
        <div class="col_3"></div> 
        <div class="col_3">
            <h1 style="text-align: center;">El estado del led es: <?php echo $row['estado']; ?></h1>
            <div class="col_3"></div> 
            <div class="col_3" style="text-align: center;" >
                <form action="index.php" method="post" id="LED" enctype="multipart/form-data">			
                    <input id="submit_button" type="submit" name="toggle_LED" value="Cambiar" />
                </form>
              
                <br><br>
                <div class="led_img">
                    <img id="contest_img" src="<?php echo $row['estado'] == 0 ? 'led_off.png' : 'led_on.png'; ?>" width="50%" height="10%">
                </div>
                <br><br>
                <div class="ServoZoom_img">
                    <img id="contest_img" src="<?php echo $row['estado'] == 0 ? 'jpg/0.webp' : 'jpg/90.webp'; ?>" width="100%" height="70%">
                </div>
                <form action="index.php" method="post" id="SERVOZOOMMAS" enctype="multipart/form-data">
                    <input type="hidden" name="nuevo_estado" value="2" /> 
                    <input id="ServoZoom_button" type="submit" name="Mover_ServoZoom" value="Mover ServoZoom Menos" />
                </form>
                <br><br>
                <form action="index.php" method="post" id="SERVOZOOMMENOS" enctype="multipart/form-data">
                    <input type="hidden" name="nuevo_estado" value="1" /> 
                    <input id="ServoZoom_button" type="submit" name="Mover_ServoZoom" value="Mover ServoZoom Mas" />
                </form>
                <!-- este estado es para saber sobre el servozoom  -->
                <h1 style="text-align: center;">El estado del Servo Zoom: <?php echo $rowServo['Estado']; ?></h1>

                <script type="text/javascript">
                    $(document).ready(function () {
                    setInterval(function () {
                    $('#refresh').load('index.php', 'update=true');
                    }, 1500); 
                     });
                </script>
            </div>
            <div class="col_3"></div> 
        </div>
         <div class="col_3"></div> 
    </div>

    <?php $conn->close(); ?>
</body>
</html>
