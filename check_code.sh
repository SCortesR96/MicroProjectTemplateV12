#!/bin/bash

echo "📊 Validando código..."
echo "⏳ Validando código con Laravel Pint..."
docker exec MicroService_Template ./vendor/bin/pint

echo "⏳ Validando código con Larastan Larastan..."
docker exec MicroService_Template ./vendor/bin/phpstan analyse --memory-limit=2G

echo "✅ Validación completada exitosamente."
