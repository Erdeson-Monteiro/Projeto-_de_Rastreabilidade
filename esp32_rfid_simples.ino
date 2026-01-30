/*
 * Sistema de Rastreamento Bovino - ESP32 (Versão Simplificada)
 * 
 * Este código implementa um sistema de leitura de tags RFID para rastreamento de bovinos.
 * 
 * INSTRUÇÕES DE USO:
 * 1. Altere as configurações no arquivo config_esp32.h
 * 2. Instale as bibliotecas necessárias no Arduino IDE
 * 3. Compile e faça upload para o ESP32
 * 
 * BIBLIOTECAS NECESSÁRIAS:
 * - MFRC522 (por GithubCommunity)
 * - ArduinoJson (por Benoit Blanchon)
 * - Adafruit GFX Library
 * - Adafruit SSD1306
 */

#include "config_esp32.h"
#include <SPI.h>
#include <MFRC522.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

#if USE_DISPLAY
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#endif

// Objetos
MFRC522 mfrc522(RC522_SS_PIN, RC522_RST_PIN); // SDA=D21, RST=D22

#if USE_DISPLAY
Adafruit_SSD1306 display(128, 64, &Wire, OLED_RESET_PIN);
#endif

// Variáveis globais
String lastTagId = "";
unsigned long lastReadTime = 0;
bool wifiConnected = false;
int reconnectAttempts = 0;

// Função para conectar ao WiFi
bool connectToWiFi() {
  if (ENABLE_DEBUG) {
    Serial.println("Conectando ao WiFi...");
  }
  
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    if (ENABLE_DEBUG) {
      Serial.print(".");
    }
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    if (ENABLE_DEBUG) {
      Serial.println();
      Serial.println("WiFi conectado!");
      Serial.print("IP: ");
      Serial.println(WiFi.localIP());
    }
    wifiConnected = true;
    reconnectAttempts = 0;
    return true;
  } else {
    if (ENABLE_DEBUG) {
      Serial.println();
      Serial.println("Falha na conexão WiFi!");
    }
    wifiConnected = false;
    return false;
  }
}

#if USE_DISPLAY
// Função para inicializar o display
void initDisplay() {
  Wire.begin(OLED_SDA_PIN, OLED_SCL_PIN);
  
  if(!display.begin(SSD1306_SWITCHCAPVCC, OLED_ADDRESS)) {
    if (ENABLE_DEBUG) {
      Serial.println("Falha na inicialização do display OLED");
    }
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
#endif

// Função para enviar dados para o servidor
bool sendToServer(String tagId) {
  if (WiFi.status() != WL_CONNECTED) {
    if (ENABLE_DEBUG) {
      Serial.println("WiFi não conectado!");
    }
    return false;
  }
  
  HTTPClient http;
  http.begin(SERVER_URL);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  
  String postData = "tag_id=" + tagId;
  
  if (ENABLE_DEBUG) {
    Serial.println("Enviando dados para o servidor...");
    Serial.println("URL: " + String(SERVER_URL));
    Serial.println("Dados: " + postData);
  }
  
  int httpResponseCode = http.POST(postData);
  
  if (httpResponseCode > 0) {
    String response = http.getString();
    
    if (ENABLE_DEBUG) {
      Serial.println("Código de resposta HTTP: " + String(httpResponseCode));
      Serial.println("Resposta do servidor: " + response);
    }
    
    // Tenta parsear a resposta JSON
    DynamicJsonDocument doc(1024);
    DeserializationError error = deserializeJson(doc, response);
    
    if (!error) {
      if (doc["success"] == true) {
        if (doc["animal"] != nullptr) {
          String animalInfo = "Animal: " + String(doc["animal"]["identificador"].as<const char*>());
          #if USE_DISPLAY
          displayMessage("Tag Lida!", animalInfo);
          #endif
        } else {
          #if USE_DISPLAY
          displayMessage("Tag Nao", "Cadastrada!");
          #endif
        }
        return true;
      } else {
        if (ENABLE_DEBUG) {
          Serial.println("Erro no servidor: " + String(doc["message"].as<const char*>()));
        }
        #if USE_DISPLAY
        displayMessage("Erro no", "Servidor!");
        #endif
      }
    } else {
      if (ENABLE_DEBUG) {
        Serial.println("Erro ao parsear JSON: " + String(error.c_str()));
      }
      #if USE_DISPLAY
      displayMessage("Erro JSON", "Parse!");
      #endif
    }
  } else {
    if (ENABLE_DEBUG) {
      Serial.println("Erro na requisição HTTP: " + String(httpResponseCode));
    }
    #if USE_DISPLAY
    displayMessage("Erro HTTP", String(httpResponseCode));
    #endif
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
  Serial.begin(SERIAL_BAUD_RATE);
  
  if (ENABLE_DEBUG) {
    Serial.println("=== Sistema de Rastreamento Bovino ===");
  }
  
  // Inicializa SPI
  SPI.begin();
  
  // Inicializa RC522
  mfrc522.PCD_Init();
  delay(4);
  
  if (ENABLE_DEBUG) {
    mfrc522.PCD_DumpVersionToSerial();
    Serial.println("Módulo RC522 inicializado");
  }
  
  #if USE_LED_INDICATOR
  // Inicializa LED
  pinMode(LED_PIN, OUTPUT);
  digitalWrite(LED_PIN, LOW);
  #endif
  
  #if USE_DISPLAY
  // Inicializa display
  initDisplay();
  #endif
  
  // Conecta ao WiFi
  #if USE_DISPLAY
  displayMessage("Conectando", "WiFi...");
  #endif
  
  if (connectToWiFi()) {
    #if USE_DISPLAY
    displayMessage("WiFi OK!", "Sistema Pronto");
    #endif
    #if USE_LED_INDICATOR
    digitalWrite(LED_PIN, HIGH);
    #endif
  } else {
    #if USE_DISPLAY
    displayMessage("WiFi Falhou!", "Verifique Config");
    #endif
    #if USE_LED_INDICATOR
    digitalWrite(LED_PIN, LOW);
    #endif
  }
  
  delay(2000);
  #if USE_DISPLAY
  displayMessage("Aguardando", "Tag RFID...");
  #endif
}

void loop() {
  // Verifica conexão WiFi
  if (WiFi.status() != WL_CONNECTED) {
    if (wifiConnected) {
      if (ENABLE_DEBUG) {
        Serial.println("Conexão WiFi perdida!");
      }
      wifiConnected = false;
      #if USE_LED_INDICATOR
      digitalWrite(LED_PIN, LOW);
      #endif
      #if USE_DISPLAY
      displayMessage("WiFi Perdido!", "Reconectando...");
      #endif
    }
    
    if (reconnectAttempts < MAX_RECONNECT_ATTEMPTS) {
      if (connectToWiFi()) {
        #if USE_DISPLAY
        displayMessage("Reconectado!", "Sistema Pronto");
        #endif
        #if USE_LED_INDICATOR
        digitalWrite(LED_PIN, HIGH);
        #endif
      } else {
        reconnectAttempts++;
        #if USE_DISPLAY
        displayMessage("Tentativa " + String(reconnectAttempts), "de " + String(MAX_RECONNECT_ATTEMPTS));
        #endif
        delay(5000);
      }
    } else {
      #if USE_DISPLAY
      displayMessage("WiFi Falhou!", "Reinicie Sistema");
      #endif
      delay(10000);
      return;
    }
  }
  
  // Lê tag RFID
  String tagId = readRFID();
  
  if (tagId != "" && tagId != lastTagId && (millis() - lastReadTime) > READ_INTERVAL_MS) {
    if (ENABLE_DEBUG) {
      Serial.println("Tag lida: " + tagId);
    }
    lastTagId = tagId;
    lastReadTime = millis();
    
    #if USE_LED_INDICATOR
    // Pisca LED
    digitalWrite(LED_PIN, LOW);
    delay(100);
    digitalWrite(LED_PIN, HIGH);
    #endif
    
    #if USE_DISPLAY
    // Exibe no display
    displayMessage("Tag Lida!", tagId);
    #endif
    
    // Envia para o servidor
    if (sendToServer(tagId)) {
      if (ENABLE_DEBUG) {
        Serial.println("Dados enviados com sucesso!");
      }
    } else {
      if (ENABLE_DEBUG) {
        Serial.println("Falha ao enviar dados!");
      }
    }
    
    delay(DISPLAY_TIMEOUT_MS);
    #if USE_DISPLAY
    displayMessage("Aguardando", "Tag RFID...");
    #endif
  }
  
  delay(100); // Pequena pausa para não sobrecarregar
} 