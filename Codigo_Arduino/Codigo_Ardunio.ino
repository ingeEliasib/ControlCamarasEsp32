// Include libraries
#include <HTTPClient.h>
#include <WiFi.h>

// Add WIFI data
const char* ssid = "wifiprueba";              // Tu nombre de red WIFI
const char* password = "123456789";                 // Tu contraseña WIFI
WiFiServer server(80);

// Variables utilizadas en el código
String LED_id = "1";                                // Para controlar el LED
bool toggle_pressed = false;                         // Indica si se presiona el botón
String data_to_send = "";                           // Datos a enviar al servidor
unsigned int Actual_Millis, Previous_Millis;
int refresh_time = 200;                             // Frecuencia de actualización (más de 1s recomendado)

// Entradas/salidas
const int button1 = 0;                              // Pin del botón (G0)
const int LED = 17;                                 // Pin del LED (G16)

// Interrupción por presión del botón
void IRAM_ATTR isr() {
  toggle_pressed = true; 
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
  }
  
  Serial.println("");
  Serial.println("WiFi conectado.");
  Serial.print("Dirección IP: ");
  Serial.println(WiFi.localIP());
  
  server.begin();  // Iniciar el servidor
}

void setup() {
  delay(10);
  Serial.begin(115200);                             // Iniciar monitor
  pinMode(LED, OUTPUT);                             // Configurar pin del LED como salida
  pinMode(button1, INPUT_PULLDOWN);                 // Configurar pin del botón como entrada con pulldown
  attachInterrupt(button1, isr, RISING);            // Crear interrupción en el pin del botón

  connectWiFi();                                    // Conectar a la red WiFi
  Actual_Millis = millis();                          // Guardar tiempo para el bucle de actualización
  Previous_Millis = Actual_Millis; 
}

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
        data_to_send = "toggle_LED=" + LED_id;  
        toggle_pressed = false;                       // Reiniciar la variable
      } else {
        data_to_send = "check_LED_status=" + LED_id; // Si no, consultar el estado
      }
      
      // Comenzar nueva conexión al servidor       
      http.begin("http://192.168.161.129/dashboard/Proyecto_camara/esp32_update.php"); // URL correcta
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");         // Preparar encabezado
      
      int response_code = http.POST(data_to_send);                                // Enviar POST
      // Si el código es mayor que 0, significa que recibimos respuesta
      if (response_code > 0) {
        Serial.println("Código HTTP " + String(response_code));                   // Imprimir código de retorno
  
        if (response_code == 200) {                                               // Si el código es 200, buena respuesta
          String response_body = http.getString();                                // Guardar respuesta del servidor
          Serial.print("Respuesta del servidor: ");                               // Imprimir respuesta para depuración
          Serial.println(response_body);

          // Si los datos recibidos son LED_is_off, apagar el LED
          if (response_body == "LED_is_off") {
            digitalWrite(LED, LOW);
          } 
          // Si los datos recibidos son LED_is_on, encender el LED
          else if (response_body == "LED_is_on") {
            digitalWrite(LED, HIGH);
          }  
        } // Fin de response_code = 200
      } // FIN de response_code > 0
      else {
        Serial.print("Error al enviar POST, código: ");
        Serial.println(response_code);
      }
      http.end();                                                                 // Finalizar conexión
    } // FIN de WIFI conectado
    else {
      Serial.println("Error de conexión WIFI");
    }
  }
}
