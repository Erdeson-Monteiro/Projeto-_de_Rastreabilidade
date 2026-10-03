#!/bin/bash

# Script para atualizar IPs em todos os arquivos do projeto
# Uso: ./atualizar_ip.sh NOVO_IP

if [ $# -eq 0 ]; then
    echo "Uso: $0 NOVO_IP"
    echo "Exemplo: $0 192.168.1.100"
    exit 1
fi

NOVO_IP=$1
echo "Atualizando IP para: $NOVO_IP"

# Arquivos para atualizar
ARQUIVOS=(
    "esp32_rfid_sem_display.ino"
    "esp32_rfid_rastreamento.ino"
    "esp32_rfid_simples.ino"
    "config_esp32.h"
)

# Padrões para substituir
PADROES=(
    "http://192.168.1.100"
    "http://192.168.0.100"
    "http://192.168.1.1"
)

for arquivo in "${ARQUIVOS[@]}"; do
    if [ -f "$arquivo" ]; then
        echo "Atualizando $arquivo..."
        
        for padrao in "${PADROES[@]}"; do
            sed -i "s|$padrao|http://$NOVO_IP|g" "$arquivo"
        done
        
        echo "✅ $arquivo atualizado"
    else
        echo "❌ $arquivo não encontrado"
    fi
done

echo ""
echo "🎉 Atualização concluída!"
echo "📝 Lembre-se de fazer upload do código para o ESP32 após a mudança de IP"