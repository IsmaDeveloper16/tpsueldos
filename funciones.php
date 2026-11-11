<?php
/**
 * funciones.php
 * -------------------------------------------------------------------------
 * Lógica de cálculo y utilidades del sistema de liquidación de sueldo.
 * No utiliza base de datos ni librerías externas.
 */

declare(strict_types=1);

// Escala de sueldos básicos por categoría (importes en pesos argentinos).
const SUELDOS_BASICOS = [
    'A' => 4050.55,
    'B' => 3209.20,
    'C' => 2575.73,
    'D' => 1812.28,
];

// Alícuotas que se aplican sobre el sueldo básico.
const ALICUOTA_ANTIGUEDAD = 0.10;
const ALICUOTA_PRESENTISMO = 0.15;
const ALICUOTA_DEDICACION_ESPECIAL = 0.50;

// Alícuotas que se aplican sobre el total de haberes.
const ALICUOTA_OBRA_SOCIAL = 0.05;
const ALICUOTA_APORTE_JUBILATORIO = 0.11;

/**
 * Devuelve el sueldo básico correspondiente a una categoría.
 *
 * @param string $categoria Categoría normalizada (A, B, C o D).
 * @return float|null Importe del básico o null si la categoría no existe.
 */
function obtenerSueldoBasico(string $categoria): ?float
{
    $categoria = strtoupper(trim($categoria));

    return SUELDOS_BASICOS[$categoria] ?? null;
}

/**
 * Valida los datos recibidos desde el formulario.
 *
 * Contempla que los valores pueden llegar manipulados por fuera del
 * navegador, por eso nunca se confía en la validación HTML.
 *
 * @param mixed $categoria  Valor recibido por POST.
 * @param mixed $antiguedad Valor recibido por POST.
 * @return array{valido: bool, errores: string[], categoria: string, antiguedad: int|null}
 */
function validarDatos(mixed $categoria, mixed $antiguedad): array
{
    $errores = [];

    // La categoría debe pertenecer a la escala predefinida.
    $categoriaNormalizada = is_scalar($categoria)
        ? strtoupper(trim((string) $categoria))
        : '';

    if ($categoriaNormalizada === '') {
        $errores[] = 'Seleccioná una categoría (A, B, C o D).';
    } elseif (!array_key_exists($categoriaNormalizada, SUELDOS_BASICOS)) {
        $errores[] = 'La categoría indicada no es válida. Elegí A, B, C o D.';
    }

    // La antigüedad debe ser un entero entre 1 y 30.
    $antiguedadTexto = is_scalar($antiguedad) ? trim((string) $antiguedad) : '';
    $antiguedadValidada = null;

    if ($antiguedadTexto === '' || preg_match('/^\d+$/', $antiguedadTexto) !== 1) {
        $errores[] = 'La antigüedad debe ser un número entero entre 1 y 30 años.';
    } else {
        $antiguedadEntero = (int) $antiguedadTexto;

        if ($antiguedadEntero < 1 || $antiguedadEntero > 30) {
            $errores[] = 'La antigüedad debe estar entre 1 y 30 años.';
        } else {
            $antiguedadValidada = $antiguedadEntero;
        }
    }

    return [
        'valido' => $errores === [],
        'errores' => $errores,
        'categoria' => $categoriaNormalizada,
        'antiguedad' => $antiguedadValidada,
    ];
}

/**
 * Calcula la liquidación completa a partir de la categoría y la antigüedad.
 *
 * Cada concepto se redondea a dos decimales antes de totalizar para que el
 * recibo cierre exactamente con la suma de las líneas que se muestran.
 *
 * @return array{
 *     categoria: string,
 *     antiguedad: int,
 *     sueldo_basico: float,
 *     haberes: array<int, array{codigo: string, concepto: string, importe: float}>,
 *     descuentos: array<int, array{codigo: string, concepto: string, importe: float}>,
 *     total_haberes: float,
 *     total_descuentos: float,
 *     total_neto: float
 * }
 */
function calcularLiquidacion(string $categoria, int $antiguedad): array
{
    $sueldoBasico = obtenerSueldoBasico($categoria);

    if ($sueldoBasico === null) {
        throw new InvalidArgumentException('No existe sueldo básico para la categoría indicada.');
    }

    // Haberes calculados sobre el sueldo básico.
    $importeAntiguedad = $sueldoBasico * ALICUOTA_ANTIGUEDAD * $antiguedad;
    $importePresentismo = $sueldoBasico * ALICUOTA_PRESENTISMO;
    $importeDedicacion = $sueldoBasico * ALICUOTA_DEDICACION_ESPECIAL;

    $haberes = [
        [
            'codigo' => '001',
            'concepto' => 'Sueldo Básico',
            'importe' => round($sueldoBasico, 2),
        ],
        [
            'codigo' => '002',
            'concepto' => 'Antigüedad',
            'importe' => round($importeAntiguedad, 2),
        ],
        [
            'codigo' => '003',
            'concepto' => 'Presentismo',
            'importe' => round($importePresentismo, 2),
        ],
        [
            'codigo' => '004',
            'concepto' => 'Dedicación Especial',
            'importe' => round($importeDedicacion, 2),
        ],
    ];

    $totalHaberes = round(array_sum(array_column($haberes, 'importe')), 2);

    // Descuentos calculados sobre el total de haberes.
    $descuentos = [
        [
            'codigo' => '101',
            'concepto' => 'Obra Social',
            'importe' => round($totalHaberes * ALICUOTA_OBRA_SOCIAL, 2),
        ],
        [
            'codigo' => '102',
            'concepto' => 'Aporte Jubilatorio',
            'importe' => round($totalHaberes * ALICUOTA_APORTE_JUBILATORIO, 2),
        ],
    ];

    $totalDescuentos = round(array_sum(array_column($descuentos, 'importe')), 2);

    return [
        'categoria' => strtoupper(trim($categoria)),
        'antiguedad' => $antiguedad,
        'sueldo_basico' => round($sueldoBasico, 2),
        'haberes' => $haberes,
        'descuentos' => $descuentos,
        'total_haberes' => $totalHaberes,
        'total_descuentos' => $totalDescuentos,
        'total_neto' => round($totalHaberes - $totalDescuentos, 2),
    ];
}

/**
 * Formatea un importe con formato argentino: $ 4.050,55
 */
function formatearMoneda(float $importe): string
{
    return '$ ' . number_format($importe, 2, ',', '.');
}

/**
 * Escapa un texto para mostrarlo de forma segura dentro del HTML.
 */
function escapar(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
