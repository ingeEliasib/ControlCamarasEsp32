// Include libraries
#include <HTTPClient.h>
#include <WiFi.h>
#include <ESP32Servo.h>

// Variables utilizadas en el código
String idcamara = "1";                   // ID de la cámara
bool toggle_pressed = false;              // Indica si se presiona el botón
String data_to_send = "";                // Datos a enviar al servidor
unsigned int Actual_Millis, Previous_Millis;
int refresh_time = 1000;                  // Frecuencia de actualización (más de 1s recomendado)

// Entradas/salidas
const int button1 = 18;//0                   // Pin del botón (G0)
const int LED = 27;//17;verde                      // Pin del LED
const int LEDROJO = 25;//17;verde                      // Pin del LED

// Variables para servo
const int pinServoZoom = 32;// 18;             // Pin del servo de zoom
const int pinServoHorizontal = 33;// 18; 
Servo ServoZoom;     
Servo ServoHorizontal;                  

//const String URLHPLINK = "http://192.168.118.178/dashboard/camaras/Web%20files/esp32_update.php";
//const String URLHPLINK = "http://192.168.43.39/dashboard/camaras/Web%20files/esp32_update.php";
//const String URLHPLINK = "http://DESKTOP-QM1EUKM/dashboard/camaras/Web%20files/esp32_update.php";
//String URLHPLINK = "http://192.168.43.39/dashboard/camaras/Web%20files/esp32_update.php";

String URLHPLINK;
String ipAsignada;

void IRAM_ATTR isr() {
  toggle_pressed = true; 
  Serial.println("Botón presionado");
}

/***************************************************
 * Configuración de redes WiFi
 ***************************************************/
const char* ssid1 = "EquipoRadioTvPiso1";
const char* password1 = "CasadeOracion2023";

const char* ssid2 = "wifiprueba";
const char* password2 = "123456789";

const char* ssid3 = "Radiotelevision";
const char* password3 = "CasadeOracion2023";

const char* ssid4 = "Computer Master2";
const char* password4 = "Lobococinawafles";

const char* ssid5 = "Galaxy A03 5555";
const char* password5 = "123456789";

WiFiServer server(80);

void connectWiFi() {
  Serial.println("\nIniciando conexión a redes WiFi...");

  // Intento de conexión a cada red
  Serial.print("Conectando a "); Serial.println(ssid1);
  WiFi.begin(ssid1, password1);
  delay(5000); // Espera para intentar la conexión
  ipAsignada = "192.168.0.28";
  digitalWrite(LEDROJO, LOW);

  while(WiFi.status() != WL_CONNECTED) {

     if (WiFi.status() != WL_CONNECTED) {
        Serial.print("Conectando a "); Serial.println(ssid1);
        WiFi.begin(ssid1, password1);
        delay(5000); // Espera para intentar la conexión
        ipAsignada = "192.168.0.28";
      }

     if (WiFi.status() != WL_CONNECTED) {
        Serial.print("Conectando a "); Serial.println(ssid2);
        WiFi.begin(ssid2, password2);
        ipAsignada = "192.168.118.178";
        delay(5000);
      }

     if (WiFi.status() != WL_CONNECTED) {
       Serial.print("Conectando a "); Serial.println(ssid3);
       WiFi.begin(ssid3, password3);
       delay(5000);
       ipAsignada = "192.168.0.28";
     }
   
     if (WiFi.status() != WL_CONNECTED) {
       Serial.print("Conectando a "); Serial.println(ssid4);
       WiFi.begin(ssid4, password4);
       delay(5000);
     }
   
     if (WiFi.status() != WL_CONNECTED) {
       Serial.print("Conectando a "); Serial.println(ssid5);
       WiFi.begin(ssid5, password5);
       delay(5000);
       ipAsignada = "192.168.187.178";
     }else {
    Serial.println("No se pudo conectar a ninguna red WiFi.");
  }

  }
   Serial.println("WiFi conectado.");
   Serial.print("Dirección IP: ");
   Serial.println(WiFi.localIP());
   // Obtiene la dirección IP local
   digitalWrite(LEDROJO, HIGH);
   
    // Imprime la dirección IP en el Monitor Serial
    Serial.println("http://" + ipAsignada + "/dashboard/camaras/Web%20files/esp32_update.php");
    URLHPLINK="http://" + ipAsignada + "/dashboard/camaras/Web%20files/esp32_update.php";
    server.begin();  // Iniciar el servidor

}
/******************
ERRORES
********/
void MostrarErrorPorLed(){
  int conteo =0;
  while(conteo = 3){
        digitalWrite(LEDROJO, LOW);
        delay(500);
        digitalWrite(LEDROJO, HIGH);
        conteo += 1;
  }

}

/***************************************************
* Funciones Manejo de Zoom
***************************************************/
void MovimientoServoZoom(String valor) {
  if (valor == "1") {
    Serial.println("Moviendo servo a 115 grados");
    //digitalWrite(LED, HIGH);
    ServoZoom.write(115);
    delay(300);
    ServoZoom.write(90);
   // digitalWrite(LED, LOW);
    Serial.println("Fin movimiento");
  } else if (valor == "2") {
    Serial.println("Moviendo servo a 55 grados");
   // digitalWrite(LED, HIGH);
    ServoZoom.write(55);
    delay(300);
    ServoZoom.write(90);
  //  digitalWrite(LED, LOW);
    Serial.println("Fin movimiento");
  } else if (valor == "0") {
    Serial.println("Zoom en reposo");
  }else {
    Serial.println("Valor no válido");
  }
}

void PruebaServoHorizontal(String valor) {
    Serial.println("Moviendo Servo " + valor);
    int posicion = valor.toInt();
    ServoHorizontal.write(posicion);

}

/***************************************************
* Setup
***************************************************/
void setup() {
  delay(10);
  Serial.begin(115200);                             // Iniciar monitor
  pinMode(LED, OUTPUT);                             // Configurar pin del LED como salida
  pinMode(LEDROJO, OUTPUT);
  pinMode(button1, INPUT_PULLDOWN);                 // Configurar pin del botón como entrada con pulldown
  attachInterrupt(button1, isr, RISING);            // Crear interrupción en el pin del botón

  connectWiFi();                                    // Conectar a la red WiFi
  Actual_Millis = millis();                          // Guardar tiempo para el bucle de actualización
  Previous_Millis = Actual_Millis; 

  ServoZoom.attach(pinServoZoom, 500, 2500);       // Inicializar servo de zoom
  ServoHorizontal.attach(pinServoHorizontal, 500, 2500);       // Inicializar servo de zoom
}

/***************************************************
* Loop
***************************************************/
void loop() {  
  WiFiClient client = server.available();

  // Bucle de actualización usando millis()
  Actual_Millis = millis();
  if (Actual_Millis - Previous_Millis > refresh_time) {
    Previous_Millis = Actual_Millis;  
    if (WiFi.status() == WL_CONNECTED) {             // Comprobar estado de conexión WiFi  
      HTTPClient http;                                // Crear nuevo cliente HTTP
      
      // Preparar datos a enviar
      if (toggle_pressed) {                           // Si se presionó el botón
        data_to_send = "toggle_LED"; 
        toggle_pressed = false;                       // Reiniciar la variable
      } else {
        data_to_send = "check_LED_status";           // Consultar el estado
      }
      

      http.begin(URLHPLINK);
      http.addHeader("Content-Type", "application/x-www-form-urlencoded"); // Preparar encabezado
      
      int response_code = http.POST(data_to_send);  // Enviar POST
      if (response_code > 0) {
        if (response_code == 200) { 
          Serial.println("Código HTTP " + String(response_code) + " todo ok"); 
          String response_body = http.getString(); // Guardar respuesta del servidor
          Serial.print("Respuesta del servidor: "); // Imprimir respuesta para depuración
          Serial.println(response_body);

          // Controlar el estado del LED
          if (response_body == "LED_is_off") {
            digitalWrite(LED, LOW);
          } else if (response_body == "LED_is_on") {
            digitalWrite(LED, HIGH);
          } 

          // Consultar estado del Servo Zoom
          http.begin(URLHPLINK);
          http.addHeader("Content-Type", "application/x-www-form-urlencoded");
          response_code = http.POST("check_SERVOZOOM_status"); // Consultar estado del servo

          if (response_code > 0 && response_code == 200) {
            String servo_response = http.getString();
            Serial.print("Estado del Servo Zoom: ");
            Serial.println(servo_response);
            MovimientoServoZoom(servo_response);//mover servo y poner en 0
            int response_code = http.POST("Poner_Espera_ServoZoom");  // poner servo en 0 en bd
          }

          // ********* horizontal **************
          http.begin(URLHPLINK);
          http.addHeader("Content-Type", "application/x-www-form-urlencoded");
          response_code = http.POST("check_SerMovHorizontal_status"); 
          
          if (response_code > 0 && response_code == 200) {
              String servo_response = http.getString();
              Serial.print("Estado del Servo Mov Horizontal: ");
              Serial.println(servo_response);
          
              // Validar la respuesta antes de usarla
              if (servo_response.length() > 0) { // Asegurarse de que la respuesta no esté vacía
                  PruebaServoHorizontal(servo_response);
              } else {
                  Serial.println("Error: la respuesta del servidor está vacía." + String(servo_response));
              }
          }
          Serial.println( URLHPLINK);

        } else {
          Serial.println("Código HTTP " + String(response_code));  
          MostrarErrorPorLed(); 

        }
      } else {
        Serial.print("Error al enviar POST, código: ");
        Serial.println(response_code);
        MostrarErrorPorLed(); 
      }
      http.end(); // Finalizar conexión
    } else {
      Serial.println("Error de conexión WIFI");
    }
  }
}
