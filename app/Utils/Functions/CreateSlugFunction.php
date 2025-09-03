<?php

namespace App\Utils\Functions;

class CreateSlugFunction
{
    public static function createSlug(string $name): string
    {
        // Eliminar espacios al inicio y al final
        $name = trim($name);

        // Convertir a minúsculas
        $name = strtolower($name);

        // Caracteres especiales
        $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);

        // Cualquier carácter no alfanumérico o espacio con un guion
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $name);

        // Múltiples espacios o guiones con un único guion
        $slug = preg_replace('/[\s-]+/', '-', $slug);

        // Guiones al inicio o al final
        return trim($slug, '-');
    }
}
