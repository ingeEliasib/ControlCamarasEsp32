<?php

$servername = "localhost"; // o "127.0.0.1"
$dBUsername = "id18842182_electronoobs"; // tu usuario
$dBPassword = "4(M(&g6!RjzK2c6{"; // tu contraseña
$dbname = "desarrollo"; // nombre de tu base de datos

// Crear conexión
$conn = mysqli_connect($servername, $dBUsername, $dBPassword, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Consultar el estado actual de la cámara
if (isset($_POST['check_LED_status'])) {
    $sql = "SELECT estado FROM controlcamaras WHERE idcamara = 1;"; // Cambia 1 si necesitas otra cámara
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['estado'] == 0) {
        echo "LED_is_off";
    } else {
        echo "LED_is_on";
    }
}

// Actualizar el estado de la cámara
if (isset($_POST['toggle_LED'])) {
    $sql = "SELECT estado FROM controlcamaras WHERE idcamara = 1;"; // Cambia 1 si necesitas otra cámara
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    // Cambiar el estado
    $newState = $row['estado'] == 0 ? 1 : 0;
    $update = mysqli_query($conn, "UPDATE controlcamaras SET estado = $newState WHERE idcamara = 1;");
    
    if ($newState == 1) {
        echo "LED_is_on";
    } else {
        echo "LED_is_off";
    }
}

mysqli_close($conn);
?>
