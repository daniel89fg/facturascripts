<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2019-2023 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Core;

use DateTime;

/**
 * Permite validar distintos tipos de datos.
 */
class Validator
{
    /**
     * Devuelve true si $text contiene solamente números, letras y los caracteres $extra.
     * También comprueba si la longitud de $text está entre $min y $max.
     */
    public static function alphaNumeric(string $text, string $extra = '', int $min = 1, int $max = 99): bool
    {
        $replace = [
            '[' => '\[',
            ']' => '\]',
            '^' => '\^',
            '$' => '\$',
            '.' => '\.',
            '|' => '\|',
            '?' => '\?',
            '*' => '\*',
            '+' => '\+',
            '(' => '\(',
            ')' => '\)',
            '/' => '\/',
            '\\' => '\\\\'
        ];
        $extra = strtr($extra, $replace);
        $pattern = '/^[a-zA-Z0-9' . $extra . ']{' . $min . ',' . $max . '}$/';
        return preg_match($pattern, $text) === 1;
    }

    /**
     * Devuelve true si el email es válido
     */
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Devuelve true si la cadena tiene entre $min y $max caracteres
     */
    public static function string(string $text, int $min = 1, int $max = 99): bool
    {
        return strlen($text) >= $min && strlen($text) <= $max;
    }

    /**
     * Devuelve true si la $url es válida.
     * Si $strict es true, entonces la url debe comenzar por http, https o cualquier otro protocolo válido.
     */
    public static function url(string $url, bool $strict = false): bool
    {
        // si la url está vacía o comienza por javascript: entonces no es una url válida
        if (empty($url) || stripos($url, 'javascript:') === 0) {
            return false;
        }

        // si la url comienza por www, entonces se añade https://
        if (false === $strict && stripos($url, 'www.') === 0) {
            $url = 'https://' . $url;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Valida una fecha en formato ESTRICTO 'd-m-Y' (día-mes-año con guiones)
     * Ejemplos válidos: "31-12-2023", "01-01-2024"
     *
     * @param string $date Fecha a validar (formato requerido: 'd-m-Y')
     * @return bool True si es válida y tiene el formato correcto
     */
    public static function date(string $date): bool
    {
        if (empty($date)) {
            return false;
        }

        // Validación ESTRICTA del formato 'd-m-Y'
        $dateObj = DateTime::createFromFormat('d-m-Y', $date);
        if (!$dateObj || $dateObj->format('d-m-Y') !== $date) {
            return false;
        }

        return checkdate(
            (int)$dateObj->format('m'),
            (int)$dateObj->format('d'),
            (int)$dateObj->format('Y')
        );
    }

    /**
     * Valida fecha y hora con:
     * - Fecha ESTRICTA en formato 'd-m-Y'
     * - Tiempo en formato 'H:i:s' O 'H:i'
     *
     * Ejemplos válidos:
     * "31-12-2023 23:59:59", "01-01-2024 00:00", "15-06-2023 14:30"
     *
     * @param string $datetime Fecha y hora a validar
     * @return bool True si es válido y tiene el formato correcto
     */
    public static function datetime(string $datetime): bool
    {
        if (empty($datetime)) {
            return false;
        }

        // Primero separamos fecha y tiempo
        $parts = explode(' ', $datetime);
        if (count($parts) !== 2) {
            return false;
        }

        // Validar la parte de la fecha (estricto)
        if (!static::date($parts[0])) {
            return false;
        }

        // Validar la parte del tiempo (flexible)
        return static::hour($parts[1]);
    }

    /**
     * Valida un tiempo en formato 'H:i:s' o 'H:i'
     *
     * @param string $time Tiempo a validar
     * @return bool True si es válido
     */
    public static function hour(string $time): bool
    {
        // Aceptar HH:MM:SS o HH:MM
        if (!preg_match('/^([01]?\d|2[0-3]):([0-5]?\d)(?::([0-5]?\d))?$/', $time)) {
            return false;
        }

        // Validación adicional para segundos si existen
        $parts = explode(':', $time);
        if (count($parts) === 3 && ($parts[2] < 0 || $parts[2] > 59)) {
            return false;
        }

        return true;
    }
}
