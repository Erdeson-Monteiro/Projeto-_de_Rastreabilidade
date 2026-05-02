/*
 * Sistema de Rastreamento Bovino - ESP32
 * 
 * Este código implementa um sistema de leitura de tags RFID para rastreamento de bovinos.
 * Funcionalidades:
 * - Leitura de tags RFID usando módulo RC522
 * - Conexão WiFi automática
 * - Envio de dados para servidor PHP
 * - Display de informações (OLED 128x64)
 * - Tratamento de erros e reconexão
 * 
 * Hardware necessário:
 * - ESP32
 * - Módulo RFID RC522
 * - Display OLED 128x64 (opcional)
 * - LED indicador (opcional)
 * 
 * Conexões RC522:
 * - SDA -> D21 (GPIO 21)
 * - SCK -> D18 (GPIO 18)
 * - MOSI -> D23 (GPIO 23)
 * - MISO -> D19 (GPIO 19)
 * - RST -> D22 (GPIO 22)
 * - 3.3V -> 3.3V
 * - GND -> GND
 * 
 * Conexões Display OLED (se usado):
 * - SDA -> GPIO 4
 * - SCL -> GPIO 15
 * - VCC -> 3.3V
 * - GND -> GND
 * 
 * Conexões LED (se usado):
 * - LED -> GPIO 2
 */

 #include <SPI.h>
#include <MFRC522.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
 
 
 // Configurações WiFi — altere para sua rede
 const char* ssid = "SUA_REDE_WIFI";
 const char* password = "SUA_SENHA_WIFI";

 // Configurações do servidor — altere para o IP do seu computador com XAMPP
 const char* serverUrl = "http://192.168.1.100/projeto_rastreabilidade/api/rfid/esp32.php";
 
 // Pinos do RC522
 #define RST_PIN         22    // D22 - RST
 #define SS_PIN          21    // D21 - SDA
 
 // Pinos do Display OLED (opcional)
 #define SCREEN_WIDTH 128
 #define SCREEN_HEIGHT 64
 #define OLED_RESET     -1
 #define SCREEN_ADDRESS 0x3C
 
 // Pino do LED indicador (opcional)
 #define LED_PIN 2
 
 // Objetos
 MFRC522 mfrc522(SS_PIN, RST_PIN);
 Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);
 
 // Variáveis globais
 String lastTagId = "";
 unsigned long lastReadTime = 0;
 const unsigned long READ_INTERVAL = 2000; // Intervalo mínimo entre leituras (2 segundos)
 bool wifiConnected = false;
 int reconnectAttempts = 0;
 const int MAX_RECONNECT_ATTEMPTS = 10;
 
 // Função para conectar ao WiFi
 bool connectToWiFi() {
   Serial.println("Conectando ao WiFi...");
   WiFi.begin(ssid, password);
   
   int attempts = 0;
   while (WiFi.status() != WL_CONNECTED && attempts < 20) {
     delay(500);
     Serial.print(".");
     attempts++;
   }
   
   if (WiFi.status() == WL_CONNECTED) {
     Serial.println();
     Serial.println("WiFi conectado!");
     Serial.print("IP: ");
     Serial.println(WiFi.localIP());
     wifiConnected = true;
     reconnectAttempts = 0;
     return true;
   } else {
     Serial.println();
     Serial.println("Falha na conexão WiFi!");
     wifiConnected = false;
     return false;
   }
 }
 
 // Função para inicializar o display
 void initDisplay() {
   if(!display.begin(SSD1306_SWITCHCAPVCC, SCREEN_ADDRESS)) {
     Serial.println("Falha na inicialização do display OLED");
     return;
   }
   
   display.clearDisplay();
   display.setTextSize(1);
   display.setTextColor(SSD1306_WHITE);
   display.setCursor(0,0);
   display.println("Sistema Bovino");
   display.println("Inicializando...");
   display.display();
   delay(2000);
 }
 
 // Função para exibir mensagem no display
 void displayMessage(String message, String subMessage = "") {
   display.clearDisplay();
   display.setTextSize(1);
   display.setTextColor(SSD1306_WHITE);
   display.setCursor(0,0);
   display.println(message);
   if (subMessage != "") {
     display.setCursor(0,20);
     display.println(subMessage);
   }
   display.display();
 }
 
 // Função para enviar dados para o servidor
 bool sendToServer(String tagId) {
   if (WiFi.status() != WL_CONNECTED) {
     Serial.println("WiFi não conectado!");
     return false;
   }
   
   HTTPClient http;
   http.begin(serverUrl);
   http.addHeader("Content-Type", "application/x-www-form-urlencoded");
   
   String postData = "tag_id=" + tagId;
   
   Serial.println("Enviando dados para o servidor...");
   Serial.println("URL: " + String(serverUrl));
   Serial.println("Dados: " + postData);
   
   int httpResponseCode = http.POST(postData);
   
   if (httpResponseCode > 0) {
     String response = http.getString();
     Serial.println("Código de resposta HTTP: " + String(httpResponseCode));
     Serial.println("Resposta do servidor: " + response);
     
     // Tenta parsear a resposta JSON
     DynamicJsonDocument doc(1024);
     DeserializationError error = deserializeJson(doc, response);
     
     if (!error) {
       if (doc["success"] == true) {
         if (doc["animal"] != nullptr) {
           String animalInfo = "Animal: " + String(doc["animal"]["identificador"].as<const char*>());
           displayMessage("Tag Lida!", animalInfo);
         } else {
           displayMessage("Tag Nao", "Cadastrada!");
         }
         return true;
       } else {
         Serial.println("Erro no servidor: " + String(doc["message"].as<const char*>()));
         displayMessage("Erro no", "Servidor!");
       }
     } else {
       Serial.println("Erro ao parsear JSON: " + String(error.c_str()));
       displayMessage("Erro JSON", "Parse!");
     }
   } else {
     Serial.println("Erro na requisição HTTP: " + String(httpResponseCode));
     displayMessage("Erro HTTP", String(httpResponseCode));
   }
   
   http.end();
   return false;
 }
 
 // Função para ler tag RFID
 String readRFID() {
   if (!mfrc522.PICC_IsNewCardPresent()) {
     return "";
   }
   
   if (!mfrc522.PICC_ReadCardSerial()) {
     return "";
   }
   
   String tagId = "";
   for (byte i = 0; i < mfrc522.uid.size; i++) {
     tagId += String(mfrc522.uid.uidByte[i] < 0x10 ? "0" : "");
     tagId += String(mfrc522.uid.uidByte[i], HEX);
   }
   tagId.toUpperCase();
   
   // Para a criptografia
   mfrc522.PICC_HaltA();
   mfrc522.PCD_StopCrypto1();
   
   return tagId;
 }
 
 void setup() {
   Serial.begin(115200);
   Serial.println("=== Sistema de Rastreamento Bovino ===");
   
   // Inicializa SPI
   SPI.begin();
   
   // Inicializa RC522
   mfrc522.PCD_Init();
   delay(4);
   mfrc522.PCD_DumpVersionToSerial();
   Serial.println("Módulo RC522 inicializado");
   
   // Inicializa LED
   pinMode(LED_PIN, OUTPUT);
   digitalWrite(LED_PIN, LOW);
   
   // Inicializa display
   Wire.begin(4, 15); // SDA=4, SCL=15
   initDisplay();
   
   // Conecta ao WiFi
   displayMessage("Conectando", "WiFi...");
   if (connectToWiFi()) {
     displayMessage("WiFi OK!", "Sistema Pronto");
     digitalWrite(LED_PIN, HIGH);
   } else {
     displayMessage("WiFi Falhou!", "Verifique Config");
     digitalWrite(LED_PIN, LOW);
   }
   
   delay(2000);
   displayMessage("Aguardando", "Tag RFID...");
 }
 
 void loop() {
   // Verifica conexão WiFi
   if (WiFi.status() != WL_CONNECTED) {
     if (wifiConnected) {
       Serial.println("Conexão WiFi perdida!");
       wifiConnected = false;
       digitalWrite(LED_PIN, LOW);
       displayMessage("WiFi Perdido!", "Reconectando...");
     }
     
     if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
       if (connectToWiFi()) {
         displayMessage("Reconectado!", "Sistema Pronto");
         digitalWrite(LED_PIN, HIGH);
       } else {
         reconnectAttempts++;
         displayMessage("Tentativa " + String(reconnectAttempts), "de " + String(MAX_RECONNECT_ATTEMPTS));
         delay(5000);
       }
     } else {
       displayMessage("WiFi Falhou!", "Reinicie Sistema");
       delay(10000);
       return;
     }
   }
   
   // Lê tag RFID
   String tagId = readRFID();
   
   if (tagId != "" && tagId != lastTagId && (millis() - lastReadTime) > READ_INTERVAL) {
     Serial.println("Tag lida: " + tagId);
     lastTagId = tagId;
     lastReadTime = millis();
     
     // Pisca LED
     digitalWrite(LED_PIN, LOW);
     delay(100);
     digitalWrite(LED_PIN, HIGH);
     
     // Exibe no display
     displayMessage("Tag Lida!", tagId);
     
     // Envia para o servidor
     if (sendToServer(tagId)) {
       Serial.println("Dados enviados com sucesso!");
     } else {
       Serial.println("Falha ao enviar dados!");
     }
     
     delay(3000);
     displayMessage("Aguardando", "Tag RFID...");
   }
   
   delay(100); // Pequena pausa para não sobrecarregar
 } 