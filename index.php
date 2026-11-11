<?php
/**
 * index.php
 * -------------------------------------------------------------------------
 * Formulario de ingreso para generar la liquidación de sueldo.
 */

declare(strict_types=1);

require_once __DIR__ . '/funciones.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Calculá la liquidación de sueldo con haberes, descuentos y neto a cobrar.">
    <title>Liquidación de Sueldo</title>

    <!-- Tipografías -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <!-- Bootstrap 5 por CDN -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >

    <!-- Estilos propios -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="page-shell">
        <header class="site-header">
            <span class="brand-mark" aria-hidden="true">LS</span>
            <p class="eyebrow">Recibos de haberes</p>
            <h1 class="display-title">Liquidación de Sueldo</h1>
            <p class="header-copy">
                Completá la categoría y la antigüedad. El sistema calcula los haberes,
                los descuentos y el neto a cobrar en el acto.
            </p>
        </header>

        <main>
            <section class="surface-card form-card" aria-labelledby="form-title">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <span class="section-number" aria-hidden="true">01</span>
                        <div>
                            <h2 id="form-title" class="h4 mb-1">Datos del empleado</h2>
                            <p class="text-secondary small mb-0">Todos los campos son obligatorios.</p>
                        </div>
                    </div>

                    <form action="liquidacion.php" method="post">
                        <div class="mb-4">
                            <label for="categoria" class="form-label">Categoría</label>
                            <select class="form-select form-select-lg" id="categoria" name="categoria" required>
                                <option value="" selected disabled>Seleccioná una categoría</option>
                                <?php foreach (SUELDOS_BASICOS as $categoria => $sueldoBasico): ?>
                                    <option value="<?= escapar($categoria) ?>">
                                        Categoría <?= escapar($categoria) ?> &middot; <?= escapar(formatearMoneda($sueldoBasico)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Cada categoría tiene un sueldo básico predefinido.</div>
                        </div>

                        <div class="mb-4">
                            <label for="antiguedad" class="form-label">Antigüedad (años)</label>
                            <div class="input-group input-group-lg">
                                <input
                                    type="number"
                                    class="form-control"
                                    id="antiguedad"
                                    name="antiguedad"
                                    min="1"
                                    max="30"
                                    step="1"
                                    inputmode="numeric"
                                    placeholder="Ej.: 5"
                                    required
                                >
                                <span class="input-group-text">años</span>
                            </div>
                            <div class="form-text">Ingresá un número entero entre 1 y 30.</div>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary btn-lg w-100 d-inline-flex align-items-center justify-content-center gap-2"
                        >
                            Calcular liquidación
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M5 12h14"/>
                                <path d="m12 5 7 7-7 7"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </section>
        </main>

        <footer class="site-footer">
            <p class="mb-0">Los importes se expresan en pesos argentinos. Cálculo orientativo.</p>
        </footer>
    </div>
</body>
</html>
