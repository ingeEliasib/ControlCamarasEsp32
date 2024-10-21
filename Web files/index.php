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

// Obtener el estado actual del LED
$sql = "SELECT estado FROM controlcamaras;";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Control LED</title>
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
        .led_img {
            height: 400px;		
            width: 100%;
            object-fit: cover;
            object-position: center;
        }
        @media only screen and (max-width: 600px) {
            .col_3 {
                width: 100%;
            }
            .wrapper {
                padding-top: 5px;
            }
            .led_img {
                height: 300px;		
                width: 80%;
                margin: 0 10%;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper" id="refresh">
        <div class="col_3"></div>
        <div class="col_3">
            <h1 style="text-align: center;">El estado del led es: <?php echo $row['status']; ?></h1>
            <div class="col_3"></div>
            <div class="col_3" style="text-align: center;">
                <form action="index.php" method="post" id="LED" enctype="multipart/form-data">			
                    <input id="submit_button" type="submit" name="toggle_LED" value="Toggle LED" />
                </form>
                <script type="text/javascript">
                    $(document).ready(function () {
                        setTimeout(function () {
                            $('#refresh').load('index.php', 'update=true');
                        }, 1000);
                    });
                </script>
                <br><br>
                <div class="led_img">
                    <img id="contest_img" src="<?php echo $row['status'] == 0 ? 'led_off.png' : 'led_on.png'; ?>" width="100%" height="100%">
                </div>
            </div>
            <div class="col_3"></div>
        </div>
        <div class="col_3"></div>
    </div>

    <?php $conn->close(); ?>
</body>
</html>
