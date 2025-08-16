#!/bin/bash

# Crear el hook pre-commit
echo "⏳ Creando el hook pre-commit..."
mkdir -p ./.git/hooks
cp hooks/pre-commit.sh ./.git/hooks/pre-commit.sh

if [ -f ./.git/hooks/pre-commit.sh ]; then
    chmod +x ./.git/hooks/pre-commit.sh
    echo "✅ Hook pre-commit creado exitosamente."
else
    echo "❌ Hubo un error al crear el hook pre-commit."
    exit 1
fi
