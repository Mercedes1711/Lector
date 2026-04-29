#!/bin/bash
# Iniciar el servicio SSH
service ssh start

# Ejecutar el comando principal
exec "$@"
