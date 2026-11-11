<?php
/**
 * liquidacion.php
 * -------------------------------------------------------------------------
 * Recibe el formulario, valida los datos y muestra el recibo de sueldo.
 */

declare(strict_types=1);

require_once __DIR__ . '/funciones.php';

// Esta página solo debe responderse ante un envío del formulario.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Se validan los datos en el servidor; nunca se confía solo en el navegador.
$datosValidados = validarDatos(
    $_POST['categoria'] ?? null,
    $_POST['antiguedad'] ?? null
);

// Si hay errores, se muestra una pantalla de aviso con un botón de retorno.
if (!$datosValidados['valido']) {
    http_response_code(422);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Error en la liquidación | Liquidación de Sueldo</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap"
            rel="stylesheet"
        >
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
            crossorigin="anonymous"
        >
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="page-shell">
            <header class="site-header">
                <span class="brand-mark" aria-hidden="true">LS</span>
                <p class="eyebrow">Liquidación de sueldo</p>
                <h1 class="display-title">Revisá los datos</h1>
                <p class="header-copy">
                    No generamos el recibo porque encontramos un problema en la información enviada.
                </p>
            </header>

            <main>
                <section class="surface-card error-card" aria-labelledby="error-title">
                    <div class="card-body p-4 p-md-5">
                        <div class="alert alert-danger" role="alert">
                            <h2 id="error-title" class="alert-heading h5">No pudimos calcular la liquidación</h2>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($datosValidados['errores'] as $error): ?>
                                    <li><?= escapar($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <a href="index.php" class="btn btn-primary w-100">Volver al formulario</a>
                    </div>
                </section>
            </main>

            <footer class="site-footer">
                <p class="mb-0">Los importes se expresan en pesos argentinos. Cálculo orientativo.</p>
            </footer>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Datos válidos: se calcula la liquidación.
$antiguedad = (int) $datosValidados['antiguedad'];
$liquidacion = calcularLiquidacion($datosValidados['categoria'], $antiguedad);
$textoAntiguedad = $antiguedad === 1 ? '1 año' : $antiguedad . ' años';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Recibo de liquidación de sueldo con haberes, descuentos y neto.">
    <title>Recibo de sueldo | Liquidación de Sueldo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="page-shell">
        <main>
            <article class="surface-card receipt-card" aria-labelledby="receipt-title">
                <div class="receipt-head">
                    <div class="receipt-brand">
                        <span class="brand-mark brand-mark--sm" aria-hidden="true">LS</span>
                        <div>
                            <p class="eyebrow mb-1">Recibo de haberes</p>
                            <h1 id="receipt-title" class="receipt-title">Liquidación de Sueldo</h1>
                        </div>
                    </div>
                    <span class="receipt-badge">Comprobante</span>
                </div>

                <div class="receipt-meta">
                    <div class="meta-item">
                        <span class="meta-label">Categoría</span>
                        <span class="meta-value">Categoría <?= escapar($liquidacion['categoria']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Antigüedad</span>
                        <span class="meta-value"><?= escapar($textoAntiguedad) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Sueldo básico</span>
                        <span class="meta-value"><?= escapar(formatearMoneda($liquidacion['sueldo_basico'])) ?></span>
                    </div>
                </div>

                <div class="receipt-body">
                    <div class="table-responsive">
                        <table class="table receipt-table align-middle mb-0">
                            <caption class="visually-hidden">
                                Detalle de haberes y descuentos de la liquidación
                            </caption>
                            <thead>
                                <tr>
                                    <th scope="col">Código</th>
                                    <th scope="col">Concepto</th>
                                    <th scope="col" class="text-end">Haberes</th>
                                    <th scope="col" class="text-end">Descuentos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($liquidacion['haberes'] as $concepto): ?>
                                    <tr>
                                        <td class="concept-code"><?= escapar($concepto['codigo']) ?></td>
                                        <td><?= escapar($concepto['concepto']) ?></td>
                                        <td class="text-end amount"><?= escapar(formatearMoneda($concepto['importe'])) ?></td>
                                        <td class="text-end amount amount--empty">&mdash;</td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php foreach ($liquidacion['descuentos'] as $concepto): ?>
                                    <tr>
                                        <td class="concept-code"><?= escapar($concepto['codigo']) ?></td>
                                        <td><?= escapar($concepto['concepto']) ?></td>
                                        <td class="text-end amount amount--empty">&mdash;</td>
                                        <td class="text-end amount"><?= escapar(formatearMoneda($concepto['importe'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="totals-row">
                                    <th scope="row" colspan="2">Total Haberes</th>
                                    <td class="text-end amount"><?= escapar(formatearMoneda($liquidacion['total_haberes'])) ?></td>
                                    <td class="text-end amount amount--empty">&mdash;</td>
                                </tr>
                                <tr class="totals-row">
                                    <th scope="row" colspan="2">Total Descuentos</th>
                                    <td class="text-end amount amount--empty">&mdash;</td>
                                    <td class="text-end amount"><?= escapar(formatearMoneda($liquidacion['total_descuentos'])) ?></td>
                                </tr>
                                <tr class="net-row">
                                    <th scope="row" colspan="2">Total Neto a Cobrar</th>
                                    <td colspan="2" class="text-end net-amount"><?= escapar(formatearMoneda($liquidacion['total_neto'])) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="receipt-actions no-print">
                    <a href="index.php" class="btn btn-primary">Nueva liquidación</a>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        Imprimir recibo
                    </button>
                </div>
            </article>
        </main>

        <footer class="site-footer no-print">
            <p class="mb-0">Los importes se expresan en pesos argentinos. Cálculo orientativo.</p>
        </footer>
    </div>
</body>
</html>
