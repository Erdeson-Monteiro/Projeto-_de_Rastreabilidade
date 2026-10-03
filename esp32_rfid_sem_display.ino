/*
 * Sistema de Rastreamento Bovino - ESP32 (Sem Display OLED)
 * 
 * Este código implementa um sistema de leitura de tags RFID para rastreamento de bovinos.
 * Versão simplificada sem display OLED para evitar problemas de compilação.
 * 
 * Hardware necessário:
 * - ESP32
 * - Módulo RFID RC522
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
 * Conexões LED (opcional):
 * - LED -> GPIO 2
 */
 
#include <SPI.h>
#include <MFRC522.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// Configurações WiFi
const char* ssid = "SUA_REDE_WIFI";          // Nome da sua rede WiFi
const char* password = "SUA_SENHA_WIFI";     // Senha da sua rede WiFi

// Configurações do servidor
const char* serverUrl = "http://SEU_IP/projeto_rastreabilidade/api/rfid/esp32.php";

// Pinos do RC522
#define RST_PIN         22    // D22 - RST
#define SS_PIN          21    // D21 - SDA

// Pino do LED indicador (opcional)
#define LED_PIN 2

// Objetos
MFRC522 mfrc522(SS_PIN, RST_PIN);

// Variáveis globais
String lastTagId = "";
unsigned long lastReadTime = 0;
const unsigned long READ_INTERVAL = 2000; // Intervalo mínimo entre leituras (2 segundos)
const unsigned long SAME_TAG_INTERVAL = 10000; // Mesma tag só é registrada de novo após 10 segundos
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
          Serial.println("Tag lida com sucesso: " + animalInfo);
        } else {
          Serial.println("Tag não cadastrada no sistema!");
        }
        http.end();
        return true;
      } else {
        Serial.println("Erro no servidor: " + String(doc["message"].as<const char*>()));
      }
    } else {
      Serial.println("Erro ao parsear JSON: " + String(error.c_str()));
    }
  } else {
    Serial.println("Erro na requisição HTTP: " + String(httpResponseCode));
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
  Serial.println("Versão sem display OLED");
  
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
  
  // Conecta ao WiFi
  Serial.println("Conectando ao WiFi...");
  if (connectToWiFi()) {
    Serial.println("WiFi conectado com sucesso!");
    digitalWrite(LED_PIN, HIGH);
  } else {
    Serial.println("Falha na conexão WiFi!");
    digitalWrite(LED_PIN, LOW);
  }
  
  delay(2000);
  Serial.println("Sistema pronto! Aguardando tags RFID...");
  Serial.println("========================================");
}

void loop() {
  // Verifica conexão WiFi
  if (WiFi.status() != WL_CONNECTED) {
    if (wifiConnected) {
      Serial.println("Conexão WiFi perdida!");
      wifiConnected = false;
      digitalWrite(LED_PIN, LOW);
    }
    
    if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
      Serial.println("Tentando reconectar WiFi...");
      if (connectToWiFi()) {
        Serial.println("WiFi reconectado!");
        digitalWrite(LED_PIN, HIGH);
      } else {
        reconnectAttempts++;
        Serial.println("Tentativa " + String(reconnectAttempts) + " de " + String(MAX_RECONNECT_ATTEMPTS));
        delay(5000);
      }
    } else {
      // Esgotou as tentativas: espera 30 s e recomeça, em vez de desistir para sempre
      Serial.println("Falha na reconexão WiFi. Nova tentativa em 30 segundos.");
      delay(30000);
      reconnectAttempts = 0;
      return;
    }
  }
  
  // Lê tag RFID
  String tagId = readRFID();
  
  // Tag diferente: aceita após READ_INTERVAL. Mesma tag (o animal passou de novo):
  // aceita após SAME_TAG_INTERVAL, para não registrar a mesma leitura várias vezes
  unsigned long elapsed = millis() - lastReadTime;
  bool novaTag = (tagId != lastTagId && elapsed > READ_INTERVAL);
  bool mesmaTagDeNovo = (tagId == lastTagId && elapsed > SAME_TAG_INTERVAL);
  if (tagId != "" && (novaTag || mesmaTagDeNovo)) {
    Serial.println("========================================");
    Serial.println("Tag lida: " + tagId);
    lastTagId = tagId;
    lastReadTime = millis();
    
    // Pisca LED
    digitalWrite(LED_PIN, LOW);
    delay(100);
    digitalWrite(LED_PIN, HIGH);
    
    // Envia para o servidor
    if (sendToServer(tagId)) {
      Serial.println("Dados enviados com sucesso!");
    } else {
      Serial.println("Falha ao enviar dados!");
    }
    
    Serial.println("========================================");
    Serial.println("Aguardando próxima tag...");
  }
  
  delay(100); // Pequena pausa para não sobrecarregar
} 