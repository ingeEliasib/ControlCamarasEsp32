#include <ESP32Servo.h>

const int pinServoZoom = 18;  // Pin del servo
Servo myServo;

void setup() {
  Serial.println("iniciando ");
  Serial.begin(115200);           // Inicializar monitor serie
  myServo.attach(pinServoZoom);   // Adjuntar el servo al pin
}

void loop() {
  // Mover de 0 a 180 grados
  Serial.println(" inicio dos");
  for (int pos = 0; pos <= 180; pos += 1) {
    myServo.write(pos);            // Mover el servo a la posición 'pos'
    delay(15);                     // Esperar para que el servo llegue a la posición
    
  }

  delay(1000);                     // Esperar 1 segundo en la posición 180
  Serial.println("MovimientoZoom ");

  // Mover de 180 a 0 grados
  for (int pos = 180; pos >= 0; pos -= 1) {
    myServo.write(pos);            // Mover el servo a la posición 'pos'
    delay(15);                     // Esperar para que el servo llegue a la posición

  }

  delay(1000);                     // Esperar 1 segundo en la posición 0
    Serial.println("fin movi ");
}
