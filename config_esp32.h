/*
 * Configurações do Sistema de Rastreamento Bovino - ESP32
 * 
 * Este arquivo contém todas as configurações que você precisa alterar
 * antes de compilar e fazer upload do código para o ESP32.
 */

#ifndef CONFIG_ESP32_H
#define CONFIG_ESP32_H

// ========================================
// CONFIGURAÇÕES DE REDE WiFi
// ========================================
const char* WIFI_SSID = "SUA_REDE_WIFI";           // Nome da sua rede WiFi
const char* WIFI_PASSWORD = "SUA_SENHA_WIFI";      // Senha da sua rede WiFi

// ========================================
// CONFIGURAÇÕES DO SERVIDOR
// ========================================
// Altere o IP abaixo para o IP do seu computador onde está rodando o XAMPP
// Para descobrir o IP, abra o terminal e digite: ipconfig (Windows) ou ifconfig (Linux/Mac)
const char* SERVER_URL = "http://192.168.1.100/projeto_rastreabilidade/api/rfid/esp32.php";

// ========================================
// CONFIGURAÇÕES DE PINOS
// ========================================
// Pinos do módulo RC522
#define RC522_RST_PIN    22    // Pino RST do RC522 (D22)
#define RC522_SS_PIN     21    // Pino SDA/SS do RC522 (D21)
// Conexões SPI automáticas:
// - SCK -> D18 (GPIO 18)
// - MOSI -> D23 (GPIO 23) 
// - MISO -> D19 (GPIO 19)

// Pinos do Display OLED (opcional - se não usar, comente as linhas abaixo)
#define OLED_SDA_PIN     4     // Pino SDA do display OLED
#define OLED_SCL_PIN     15    // Pino SCL do display OLED
#define OLED_RESET_PIN   -1    // Pino RST do display OLED (geralmente -1)
#define OLED_ADDRESS     0x3C  // Endereço I2C do display OLED

// Pino do LED indicador (opcional)
#define LED_PIN          2     // Pino do LED indicador

// ========================================
// CONFIGURAÇÕES DE COMPORTAMENTO
// ========================================
#define READ_INTERVAL_MS       2000    // Intervalo mínimo entre leituras (2 segundos)
#define SAME_TAG_INTERVAL      10000   // Mesma tag só é registrada de novo após 10 segundos
#define MAX_RECONNECT_ATTEMPTS 10      // Máximo de tentativas de reconexão WiFi
#define WIFI_TIMEOUT_MS        10000   // Timeout para conexão WiFi (10 segundos)
#define DISPLAY_TIMEOUT_MS     3000    // Tempo que a mensagem fica no display (3 segundos)

// ========================================
// CONFIGURAÇÕES DE DEBUG
// ========================================
#define SERIAL_BAUD_RATE       115200  // Velocidade do monitor serial
#define ENABLE_DEBUG           true    // Habilita mensagens de debug no serial

// ========================================
// CONFIGURAÇÕES DE HARDWARE
// ========================================
#define USE_DISPLAY            true    // true se usar display OLED, false se não usar
#define USE_LED_INDICATOR      true    // true se usar LED indicador, false se não usar

#endif // CONFIG_ESP32_H 