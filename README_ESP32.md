# Sistema de Rastreamento Bovino - ESP32

Este projeto implementa um sistema completo de rastreamento bovino usando ESP32, módulo RFID RC522 e um servidor web PHP.

## 📋 Índice

1. [Componentes Necessários](#componentes-necessários)
2. [Conexões de Hardware](#conexões-de-hardware)
3. [Configuração do Software](#configuração-do-software)
4. [Instalação das Bibliotecas](#instalação-das-bibliotecas)
5. [Configuração do Código](#configuração-do-código)
6. [Upload para o ESP32](#upload-para-o-esp32)
7. [Teste e Funcionamento](#teste-e-funcionamento)
8. [Solução de Problemas](#solução-de-problemas)

## 🔧 Componentes Necessários

### Hardware Obrigatório:
- **ESP32** (qualquer modelo)
- **Módulo RFID RC522**
- **Cabo USB** para programação
- **Fonte de alimentação** (5V/2A recomendado)

### Hardware Opcional:
- **Display OLED 128x64** (I2C)
- **LED indicador**
- **Protoboard e jumpers**

## 🔌 Conexões de Hardware

### Conexões do Módulo RC522:
| RC522 | ESP32 |
|-------|-------|
| SDA   | GPIO 21 |
| SCK   | GPIO 18 |
| MOSI  | GPIO 23 |
| MISO  | GPIO 19 |
| RST   | GPIO 22 |
| 3.3V  | 3.3V |
| GND   | GND |

### Conexões do Display OLED (opcional):
| Display | ESP32 |
|---------|-------|
| SDA     | GPIO 4 |
| SCL     | GPIO 15 |
| VCC     | 3.3V |
| GND     | GND |

### Conexões do LED (opcional):
| LED | ESP32 |
|-----|-------|
| +   | GPIO 2 |
| -   | GND (com resistor 220Ω) |

## 💻 Configuração do Software

### 1. Arduino IDE
- Baixe e instale o [Arduino IDE](https://www.arduino.cc/en/software)
- Adicione o ESP32 ao Arduino IDE:
  - Abra Arduino IDE
  - Vá em `Arquivo > Preferências`
  - Em "URLs Adicionais para Gerenciador de Placas", adicione:
    ```
    https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
    ```
  - Vá em `Ferramentas > Placa > Gerenciador de Placas`
  - Procure por "ESP32" e instale

### 2. Servidor Web
- Instale o [XAMPP](https://www.apachefriends.org/)
- Copie o projeto para a pasta `htdocs`
- Configure o banco de dados MySQL

## 📚 Instalação das Bibliotecas

No Arduino IDE, vá em `Sketch > Incluir Biblioteca > Gerenciar Bibliotecas` e instale:

1. **MFRC522** (por GithubCommunity)
2. **ArduinoJson** (por Benoit Blanchon)
3. **Adafruit GFX Library**
4. **Adafruit SSD1306**

## ⚙️ Configuração do Código

### 1. Edite o arquivo `config_esp32.h`:

```cpp
// Configurações de rede WiFi
const char* WIFI_SSID = "SUA_REDE_WIFI";           // Nome da sua rede WiFi
const char* WIFI_PASSWORD = "SUA_SENHA_WIFI";      // Senha da sua rede WiFi

// Configurações do servidor
const char* SERVER_URL = "http://192.168.1.100/projeto_rastreabilidade/api/rfid/esp32.php";
// Altere o IP para o IP do seu computador onde está o XAMPP
```

### 2. Para descobrir o IP do seu computador:

**Windows:**
```cmd
ipconfig
```

**Linux/Mac:**
```bash
ifconfig
```

Procure pelo IP da sua rede local (geralmente começa com 192.168.x.x ou 10.0.x.x)

### 3. Configurações opcionais:

```cpp
// Se não usar display OLED, altere para false
#define USE_DISPLAY            true

// Se não usar LED indicador, altere para false
#define USE_LED_INDICATOR      true

// Para desabilitar mensagens de debug
#define ENABLE_DEBUG           false
```

## 📤 Upload para o ESP32

1. Conecte o ESP32 via USB
2. No Arduino IDE:
   - Selecione a placa: `Ferramentas > Placa > ESP32 Arduino > ESP32 Dev Module`
   - Selecione a porta: `Ferramentas > Porta > [Porta do ESP32]`
3. Abra o arquivo `esp32_rfid_simples.ino`
4. Clique em `Sketch > Upload`

## 🧪 Teste e Funcionamento

### 1. Monitor Serial
- Abra o Monitor Serial: `Ferramentas > Monitor Serial`
- Configure a velocidade para 115200 baud
- Você verá mensagens como:
  ```
  === Sistema de Rastreamento Bovino ===
  Conectando ao WiFi...
  WiFi conectado!
  IP: 192.168.1.50
  Módulo RC522 inicializado
  ```

### 2. Teste de Leitura
- Aproxime uma tag RFID do módulo RC522
- No monitor serial você verá:
  ```
  Tag lida: 12345678
  Enviando dados para o servidor...
  Dados enviados com sucesso!
  ```

### 3. Verificação no Servidor
- Acesse: `http://localhost/projeto_rastreabilidade/pages/index.php`
- Verifique se a leitura aparece na página

## 🔍 Solução de Problemas

### Problema: WiFi não conecta
**Solução:**
- Verifique se o SSID e senha estão corretos
- Certifique-se de que a rede WiFi está funcionando
- Tente reiniciar o ESP32

### Problema: Módulo RC522 não lê tags
**Solução:**
- Verifique todas as conexões
- Certifique-se de que o módulo está alimentado com 3.3V
- Teste com diferentes tags RFID

### Problema: Erro HTTP ao enviar dados
**Solução:**
- Verifique se o XAMPP está rodando
- Confirme se o IP do servidor está correto
- Verifique se o Apache está ativo

### Problema: Display OLED não funciona
**Solução:**
- Verifique as conexões I2C (SDA e SCL)
- Certifique-se de que o endereço I2C está correto (geralmente 0x3C)
- Teste com um scanner I2C

### Problema: Código não compila
**Solução:**
- Verifique se todas as bibliotecas estão instaladas
- Certifique-se de que a placa ESP32 está selecionada
- Verifique se não há erros de sintaxe

## 📱 Funcionalidades do Sistema

### Leitura de Tags RFID
- Leitura automática de tags RFID
- Evita leituras duplicadas
- Intervalo configurável entre leituras

### Comunicação WiFi
- Conexão automática ao WiFi
- Reconexão automática em caso de perda
- Tratamento de erros de rede

### Interface Visual (com display)
- Exibe status do sistema
- Mostra tags lidas
- Indica erros e status de conexão

### Integração com Servidor
- Envio automático de dados
- Tratamento de respostas JSON
- Log de todas as operações

## 🔄 Fluxo de Funcionamento

1. **Inicialização:**
   - ESP32 conecta ao WiFi
   - Inicializa módulo RC522
   - Configura display (se disponível)

2. **Leitura:**
   - Monitora presença de tags RFID
   - Lê ID da tag quando detectada
   - Evita leituras duplicadas

3. **Envio:**
   - Envia tag_id para o servidor PHP
   - Aguarda resposta do servidor
   - Exibe resultado no display

4. **Tratamento de Erros:**
   - Reconecta WiFi se necessário
   - Exibe erros no display
   - Continua funcionando mesmo com falhas

## 📞 Suporte

Se você encontrar problemas:

1. Verifique o monitor serial para mensagens de erro
2. Confirme todas as conexões de hardware
3. Teste cada componente individualmente
4. Verifique as configurações no arquivo `config_esp32.h`

## 📄 Licença

Este projeto é de código aberto e pode ser usado livremente para fins educacionais e comerciais. 