#!/bin/bash

echo "📊 Validando código..."
echo "⏳ Validando código con Laravel Pint..."
composer pre-commit
RESULT=$?
if [ $RESULT -ne 0 ]; then
  echo "❌ Pre-commit hook failed. Commit aborted."
  exit 1
fi
exit 0
echo "✅ Validación completada exitosamente."
