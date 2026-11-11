# AGENTS.md

## Contexto

- Aplicación PHP 8 pura para liquidar sueldos, servida por XAMPP en `http://localhost/tpsueldos/`.
- Sin Composer, framework, base de datos, build, tests ni CI.
- Flujo: `index.php` (formulario) → POST a `liquidacion.php` (valida y renderiza) → `funciones.php` (toda la lógica de cálculo) + `style.css` (tema propio).

## Comandos

- `php` **no está en el PATH**. Usar siempre `C:/xampp/php/php.exe`.
- Verificar sintaxis de los PHP modificados:
  `C:/xampp/php/php.exe -l funciones.php`
  (ídem `index.php` y `liquidacion.php`)
- Prueba end-to-end sin Apache:
  `C:/xampp/php/php.exe -S 127.0.0.1:8099 -t .`
  `curl -X POST -d "categoria=A&antiguedad=5" http://127.0.0.1:8099/liquidacion.php`
  Resultado esperado: neto `$ 7.315,30`.
- Con Apache encendido, la app completa está en `http://localhost/tpsueldos/`.

## Reglas de negocio que no deben cambiar

- Los básicos están solo en `SUELDOS_BASICOS` (`funciones.php`): A `4050.55`, B `3209.20`, C `2575.73`, D `1812.28`.
- Haberes sobre el básico: antigüedad `10% × años`, presentismo `15%`, dedicación especial `50%`.
- Descuentos sobre el total de haberes: obra social `5%`, aporte jubilatorio `11%`.
- Redondear **cada concepto** con `round(..., 2)` antes de totalizar. Así los totales coinciden con las líneas mostradas; redondear solo al final produce una diferencia de un centavo.
- Los códigos `001`–`004` (haberes) y `101`–`102` (descuentos) son parte del contrato del recibo.
- Moneda en formato argentino con `formatearMoneda()`: `$ 4.050,55`, no el formato por defecto de `number_format`.
- Validación en servidor: categoría A–D y antigüedad entera entre 1 y 30. `liquidacion.php` solo acepta POST; un GET redirige a `index.php`.
- Todo dato dinámico se muestra con `escapar()`.

## Interfaz y assets

- Bootstrap 5.3.3 y Google Fonts se cargan por CDN; sin internet se pierde el estilado de Bootstrap.
- `style.css` se carga después de Bootstrap y define el tema en las variables de `:root`.
- El recibo incluye botón "Imprimir recibo" y reglas `@media print`; conservarlos al editar la salida.
- `.agents/skills/` lo administra `npx skills`; no editar esos archivos a mano. Usar `npx skills add ...` / `npx skills update`, con `skills-lock.json` como registro.

## Entorno local

- El servicio nativo `MySQL80` está detenido y en inicio **Manual** porque ocupaba el puerto `3306`; la MariaDB 10.4 de XAMPP corre en ese puerto.
- No iniciar `MySQL80` junto con la MariaDB de XAMPP: ambos usan `3306` y la segunda falla con error `10048`.
- `Hola.ipynb` es un archivo vacío de relleno; ignorarlo.
