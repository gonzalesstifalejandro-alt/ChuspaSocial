# Revisión de CSRF: sesskey en las acciones que modifican datos

**Fecha:** 2026-10-18
**Rama:** security/audit-sesskey
**Alcance:** todos los archivos PHP del plugin (`src/`).
**Método:** para cada archivo PHP se buscaron las acciones que cambian datos
(procesar el envío de un formulario, atender una petición que escribe o declarar
un servicio web de escritura) y se comprobó que la acción pase por una
verificación de sesión: `moodleform` (que la agrega sola) o `require_sesskey()`.
Las búsquedas se hicieron sobre todo `src/`:

    grep -rn --include=*.php -E 'require_sesskey|confirm_sesskey|sesskey' src/  →  (sin coincidencias)
    grep -rn --include=*.php 'moodleform' src/  →  2 formularios
    grep -rn --include=*.php "'type' => 'write'" src/db/services.php  →  3 servicios

## Resumen

- Archivos PHP revisados: 23.
- Formularios (`moodleform`): 2.
- Servicios web AJAX de escritura declarados: 3.
- Llamadas a `require_sesskey()` en el plugin: 0.
- Problemas encontrados: ninguno.

## Acciones que modifican datos

| Archivo | Línea | Acción | Estado |
| --- | --- | --- | --- |
| src/classes/form/post_form.php | 34 | Formulario de alta y edición de publicaciones. | OK: extiende `moodleform`, que agrega el campo oculto `sesskey` y llama a `confirm_sesskey()` al enviarse (formslib.php de Moodle, líneas 219 y 317). |
| src/classes/form/listing_form.php | 16 | Formulario de alta de un anuncio del marketplace. | OK: extiende `moodleform` (misma verificación de sesskey). |
| src/db/services.php | 32 | Servicio web `local_chuspasocial_toggle_follow`, `type => write`, `ajax => true`. | OK: Moodle llama a `require_sesskey()` antes de ejecutar los servicios web AJAX que requieren login (external_api.php de Moodle, líneas 216-223). |
| src/db/services.php | 39 | Servicio web `local_chuspasocial_toggle_reaction`, `type => write`, `ajax => true`. | OK: misma verificación de sesskey. |
| src/db/services.php | 46 | Servicio web `local_chuspasocial_add_comment`, `type => write`, `ajax => true`. | OK: misma verificación de sesskey. |

Ninguna parte del plugin llama a `require_sesskey()` directamente porque sus
dos puntos de entrada que escriben (los formularios y los servicios web AJAX)
delegan la comprobación en el núcleo de Moodle. Un servicio web de escritura
con `ajax => true` y sin `loginrequired => false` es de tipo login obligatorio,
y por eso Moodle exige la sesskey antes de ejecutarlo.

## Escrituras internas sin punto de entrada HTTP

| Archivo | Línea | Acción | Estado |
| --- | --- | --- | --- |
| src/classes/local/post_service.php | 49 | `update_post()`: `$post->update()`. | No aplica dentro del servicio: no lee la petición; la verificación de sesskey corresponde al código que lo invoque (una página o un servicio web). |
| src/classes/local/post_service.php | 103, 109, 123 | `delete_post()`: tres `delete_records` (comentarios, reacciones y publicación). | No aplica dentro del servicio: misma razón. |

Estos métodos son la capa de servicio, no un punto de entrada: hoy el plugin no
tiene ninguna página que los llame ni las clases de los servicios web
declarados, así que no hay entrada HTTP que los dispare.

## Archivos revisados sin acciones

- src/classes/event/comment_created.php
- src/classes/event/listing_created.php
- src/classes/event/post_created.php
- src/classes/event/post_deleted.php
- src/classes/event/reaction_added.php
- src/classes/local/hook_callbacks.php
- src/classes/local/listing_service.php
- src/db/access.php
- src/db/events.php
- src/db/messages.php
- src/db/tag.php
- src/index.php
- src/lang/en/local_chuspasocial.php
- src/lang/es/local_chuspasocial.php
- src/settings.php
- src/tests/lib_test.php
- src/tests/local/persistent/report_test.php
- src/tests/local/post_service_test.php
- src/version.php

`src/index.php` es la única página del plugin y solo imprime el encabezado y el
pie de Moodle: no lee datos ni escribe, por lo que no necesita sesskey.

## Ejemplos de validación de sesskey

Los siguientes ejemplos son patrones de referencia para nuevas páginas o puntos
de entrada que modifiquen datos. En este plugin no se necesitan todavía porque
los formularios y servicios web existentes delegan la comprobación en Moodle,
pero deben aplicarse en cuanto se agregue una acción manual.

### `require_sesskey()` en una acción que modifica datos

`require_sesskey()` lee el parámetro `sesskey` de la petición y lanza una
excepción si no coincide con la sesión actual. Debe ejecutarse antes de leer o
modificar datos:

    require_sesskey();

    $postid = required_param('postid', PARAM_INT);
    \local_chuspasocial\local\post_service::delete_post($postid);

Este patrón es indispensable en acciones como eliminar o editar una
publicación, crear o resolver un reporte, alternar un seguimiento o una
reacción, o agregar un comentario.

### Formularios

Los formularios que extienden `moodleform` ya incluyen el campo `sesskey` y la
validación al procesar el envío, así que no deben duplicar la llamada. En un
formulario HTML manual hay que agregar el campo oculto y validarlo al recibir el
POST:

    <form method="post" action="eliminar.php">
        <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
        <input type="hidden" name="postid" value="<?php echo $postid; ?>">
        <button type="submit">Eliminar</button>
    </form>

    require_sesskey();

    $postid = required_param('postid', PARAM_INT);
    \local_chuspasocial\local\post_service::delete_post($postid);

### `confirm_sesskey()` cuando se necesita evaluar el resultado

`confirm_sesskey()` devuelve `true` o `false`, por lo que permite manejar el
error de forma explícita. Si se usa, siempre hay que comprobar el valor devuelto
y detener la acción cuando sea `false`:

    if (!confirm_sesskey()) {
        throw new \moodle_exception('invalidsesskey');
    }

    $reportid = required_param('reportid', PARAM_INT);
    \local_chuspasocial\local\report_service::resolve_and_hide($reportid);

### Escenarios donde la validación es indispensable

- Cualquier petición HTTP que cree, edite, elimine o cambie el estado de un
  dato.
- Acciones disparadas desde enlaces, formularios o AJAX que requieran sesión
  iniciada.
- Operaciones que solo comprueban capacidades con `require_capability()`: la
  capacidad autoriza la acción, pero no protege contra CSRF.
- Formularios que no extiendan `moodleform`: en ese caso hay que llamar a
  `require_sesskey()` o a `confirm_sesskey()` manualmente.
- Servicios web de escritura: si no son servicios AJAX gestionados por Moodle,
  el punto de entrada debe validar la sesskey.

## Conclusión

No se encontró ninguna acción que modifique datos sin verificación de sesión:
los formularios usan `moodleform` y los servicios web de escritura los protege
el núcleo de Moodle. Por eso no hace falta abrir ningún issue de seguridad
nuevo.

Observación (fuera del alcance de este informe): los tres servicios web de
`src/db/services.php` apuntan a las clases `local_chuspasocial\external\*`, que
todavía no están en el repo (no existe `src/classes/external/`). La declaración
ya queda correcta para cuando se implementen; hoy no se pueden ejecutar.
