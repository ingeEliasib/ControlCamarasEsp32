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
const int button1 = 0;                   // Pin del botón (G0)
const int LED = 17;                      // Pin del LED

// Variables para servo
const int pinServoZoom = 18;             // Pin del servo de zoom
Servo ServoZoom;                         // Objeto del servo

const String URLHPLINK = "http://192.168.118.178/dashboard/camaras/Web%20files/esp32_update.php";

const char* ssid = "wifiprueba";              // Tu nombre de red WIFI
const char* password = "123456789";                 // Tu contraseña WIFI


WiFiServer server(80);

void IRAM_ATTR isr() {
  toggle_pressed = true; 
  Serial.println("Botón presionado");
}

/***************************************************
* Connecting to a WiFi network
****************************************************/
void connectWiFi() {
  Serial.println();
  Serial.print("Conectando a ");
  Serial.println(ssid);
  
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print("Buscando Wifi ...");
    Serial.print("Conectando a ");
    Serial.println(ssid);
    WiFi.begin(ssid, password);
    delay(500);
  }
  
  Serial.println("");
  Serial.println("WiFi conectado.");
  Serial.print("Dirección IP: ");
  Serial.println(WiFi.localIP());
  
  server.begin();  // Iniciar el servidor
}

/***************************************************
* Funciones Manejo de Zoom
***************************************************/
void MovimientoZoom() {
  Serial.println("Encender led");
  digitalWrite(LED, HIGH);
  Serial.println("Mover Servo a 90 grados");
  ServoZoom.write(90); 
  Serial.println("Fin movimiento y encendido");
}

void PararZoom() {
  Serial.println("Apagar led y parar servo");
  digitalWrite(LED, LOW);
  ServoZoom.write(0); 
}

/***************************************************
* Setup
***************************************************/
void setup() {
  delay(10);
  Serial.begin(115200);                             // Iniciar monitor
  pinMode(LED, OUTPUT);                             // Configurar pin del LED como salida
  pinMode(button1, INPUT_PULLDOWN);                 // Configurar pin del botón como entrada con pulldown
  attachInterrupt(button1, isr, RISING);            // Crear interrupción en el pin del botón

  connectWiFi();                                    // Conectar a la red WiFi
  Actual_Millis = millis();                          // Guardar tiempo para el bucle de actualización
  Previous_Millis = Actual_Millis; 

  ServoZoom.attach(pinServoZoom, 500, 2500);       // Inicializar servo de zoom
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

            // Mover el servo según el estado
            if (servo_response == "1" or servo_response == "0") {
              Serial.println("Moviendo Servo a 0 grados");
              ServoZoom.write(0);
            } else if (servo_response == "2") {
              Serial.println("Moviendo Servo a 90 grados");
              ServoZoom.write(90);
               delay(500);
            }
  
          }
        } else {
          Serial.println("Código HTTP " + String(response_code));   
        }
      } else {
        Serial.print("Error al enviar POST, código: ");
        Serial.println(response_code);
      }
      http.end(); // Finalizar conexión
    } else {
      Serial.println("Error de conexión WIFI");
    }
  }
}
