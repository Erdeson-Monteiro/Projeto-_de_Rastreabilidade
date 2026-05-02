# Sistema de Rastreabilidade Bovina com RFID

Protótipo de sistema de rastreamento automatizado de bovinos desenvolvido com tecnologia RFID, ESP32 e servidor web PHP/MySQL. Projeto de iniciação científica financiado pela **FAPEMA** (Fundação de Amparo à Pesquisa e ao Desenvolvimento Científico e Tecnológico do Maranhão), desenvolvido no ensino médio técnico do IFMA.

## Sobre o Projeto

O Brasil é um dos maiores produtores de carne bovina do mundo. A rastreabilidade individual de animais — do nascimento ao abate — é essencial para a gestão eficiente de rebanhos, a conformidade com regulamentos sanitários (como o SISBOV) e a segurança alimentar dos consumidores.

Este projeto propõe e implementa um protótipo de rastreamento bovino usando **RFID (Radio Frequency Identification)**, permitindo identificar, cadastrar e consultar o histórico de cada animal de forma rápida e segura — sem a necessidade de identificação manual.

### Problema resolvido

Em grandes rebanhos, a identificação manual é lenta e imprecisa. Este sistema automatiza o processo: ao aproximar-se da antena RFID, o animal é identificado instantaneamente, e seus dados (vacinações, pesagens, movimentações, histórico veterinário) ficam acessíveis em tempo real via interface web.

## Arquitetura do Sistema

```
[Tag RFID no animal]
        ↓ (radiofrequência)
[Leitor RC522 + ESP32]
        ↓ (HTTP/WiFi)
[Servidor PHP + MySQL]
        ↓
[Interface Web (browser)]
```

### Componentes de Hardware

| Componente | Função |
|---|---|
| ESP32 | Microcontrolador principal (WiFi integrado) |
| Módulo RFID RC522 | Leitura das tags RFID dos animais |
| Tags RFID | Identificação única de cada animal |
| Display OLED 128x64 | Feedback visual (opcional) |
| Painel solar + bateria | Autonomia energética em campo |

### Componentes de Software

| Parte | Tecnologia |
|---|---|
| Firmware ESP32 | C++ (Arduino IDE) |
| Backend / API | PHP |
| Banco de dados | MySQL |
| Frontend | HTML, CSS, JavaScript |

## Funcionalidades

- Identificação automática de bovinos por RFID
- Cadastro de animais com dados completos
- Registro de vacinações e histórico veterinário
- Controle de pesagens com log de evolução
- Consulta de histórico por animal
- Interface web responsiva
- API REST para integração com o firmware ESP32
- Autenticação de usuários
- Suporte a operação com energia solar (sem rede elétrica)

## Configuração e Instalação

### 1. Firmware ESP32

Veja o guia completo em [README_ESP32.md](README_ESP32.md).

Resumidamente:
1. Instale o [Arduino IDE](https://www.arduino.cc/en/software) com suporte ao ESP32
2. Instale as bibliotecas: `MFRC522`, `ArduinoJson`, `Adafruit GFX`, `Adafruit SSD1306`
3. Edite `config_esp32.h` com seu SSID, senha WiFi e IP do servidor
4. Faça upload do sketch `esp32_rfid_simples.ino` para o ESP32

### 2. Servidor Web (XAMPP / Apache + PHP + MySQL)

1. Instale o [XAMPP](https://www.apachefriends.org/) (ou qualquer stack LAMP/WAMP)
2. Copie a pasta do projeto para `htdocs/projeto_rastreabilidade/`
3. Copie `includes/config.example.php` para `includes/config.php`
4. Ajuste as credenciais do banco em `includes/config.php`
5. Importe o banco de dados:
   ```sql
   mysql -u root -p < db/database.sql
   ```
6. Acesse `http://localhost/projeto_rastreabilidade/pages/index.php`

### Esquema de conexão RC522 ↔ ESP32

| RC522 | ESP32 |
|---|---|
| SDA | GPIO 21 |
| SCK | GPIO 18 |
| MOSI | GPIO 23 |
| MISO | GPIO 19 |
| RST | GPIO 22 |
| 3.3V | 3.3V |
| GND | GND |

## Estrutura do Repositório

```
.
├── api/
│   └── rfid/           # Endpoints da API REST (ESP32 → servidor)
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
├── db/
│   └── database.sql    # Script de criação do banco de dados
├── includes/
│   ├── config.php          # Configuração do banco (não versionado)
│   ├── config.example.php  # Template de configuração
│   ├── header.php
│   └── footer.php
├── pages/              # Páginas da interface web
├── config_esp32.h      # Configurações do firmware (WiFi, pinos)
├── esp32_rfid_simples.ino      # Firmware principal
├── esp32_rfid_rastreamento.ino # Firmware com mais funcionalidades
├── esp32_rfid_sem_display.ino  # Firmware sem display OLED
├── login.php
└── atualizar_ip.sh     # Script auxiliar para atualizar IP do servidor
```

## Contexto Acadêmico

- **Instituição:** IFMA (Instituto Federal do Maranhão)
- **Financiamento:** FAPEMA
- **Nível:** Ensino Médio Técnico (Iniciação Científica)
- **Área:** Agropecuária + Tecnologia da Informação

### Referências

- MINISTÉRIO DA AGRICULTURA E PECUÁRIA. SISBOV, 2024.
- FACINA, A. R. Desenvolvimento de Protótipo de Rastreamento Bovino Usando LoRa. Unicamp, 2021.
- VASCONCELOS, A. S. Plataforma IoT para Rastreamento e Monitoramento para Bovinos a Pasto. UFCG, 2020.
- FERNANDES, L. M. A. P. Análise da Adoção da Tecnologia Blockchain na Rastreabilidade de Bovinos. ISCTE, 2023.
- SYAHPUTRI, B. E.; SUCIPTO, S. Monitoring of beef cold chain using RFID. ICGAB, 2021.

## Licença

Distribuído sob a licença MIT. Veja [LICENSE](LICENSE) para mais detalhes.
