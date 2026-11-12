# Moderación de contenido

## Introducción

ChuspaSocial permite que los usuarios reporten contenido inapropiado y que los moderadores lo resuelvan. Este documento describe qué se puede reportar, el proceso de reporte, la cola de moderación y las acciones del moderador.

---

## Contenido que se puede reportar

| Tipo de contenido     | Valor (`itemtype`) |
| --------------------- | ------------------ |
| Publicación           | `post`             |
| Comentario            | `comment`          |
| Libro del marketplace | `listing`          |

Cada reporte incluye un **motivo** (`reason`) en texto libre escrito por quien reporta. Actualmente **no hay categorías de motivo predefinidas**: el sistema solo valida que el motivo no esté vacío.

Ejemplos de motivos que un usuario puede escribir:

- Contenido ofensivo: "Insulta a un compañero en el comentario."
- Spam: "Publicidad repetida que no tiene relación con el curso."
- Contenido inapropiado: "El libro publicado no corresponde a la plataforma."

Estos son solo ejemplos de texto libre; el sistema no los clasifica.

---

## Proceso de reporte

1. El usuario reporta una publicación, un comentario o un libro e indica el motivo.
2. `report_service::create_report()` valida el reporte:
   - `itemtype` debe ser `post`, `comment` o `listing`.
   - `itemid` debe ser mayor que 0.
   - El motivo es obligatorio; se eliminan los espacios al inicio y al final, y no puede quedar vacío.
   - Un usuario solo puede reportar una vez el mismo elemento. Un segundo intento falla con el mensaje "Report sent".
3. Si es válido, guarda el reporte con estado `open`.
4. El sistema envía la notificación `newreport` a cada usuario que puede moderar, excepto a quien generó el reporte.

Permiso requerido: `local/chuspasocial:report`, que por defecto tiene todo usuario autenticado.

---

## Cola de moderación

Los reportes se guardan en `local_chuspasocial_report` con su estado (`status`), quién reportó (`reporterid`) y quién lo resolvió (`resolvedby`).

| Estado     | Significado                            |
| ---------- | -------------------------------------- |
| `open`     | Reporte pendiente de revisión.         |
| `resolved` | Reporte resuelto por un moderador.     |

---

## Facultades del moderador

Permiso requerido: `local/chuspasocial:moderate`, en contexto de sistema. Por defecto solo lo tiene el rol **manager**; para que un docente modere, el administrador debe asignarle un rol con este permiso.

| Acción                  | Función                              | Efecto |
| ----------------------- | ------------------------------------ | ------ |
| Resolver y ocultar post | `report_service::resolve_and_hide()` | El reporte pasa a `resolved` y el post queda con `hidden = 1`. |

> **Pendiente de implementación:** el descarte de reportes (cerrar un reporte sin ocultar el contenido) aún no existe. Actualmente la única acción disponible para el moderador es `report_service::resolve_and_hide()`, que resuelve el reporte y oculta la publicación.

---

## Impacto en la visibilidad del post

Cuando un moderador resuelve un reporte con `resolve_and_hide()`, el post queda con `hidden = 1`. Un post oculto solo lo pueden ver:

- su **autor**, y
- los usuarios con el permiso `local/chuspasocial:moderate` en el contexto del post (el curso, o el sistema si es del muro general).

Para el resto de usuarios el post deja de ser visible (`post_service::can_view()` devuelve `false`).

Las reglas generales de visibilidad siguen aplicando antes de esta comprobación: el muro general exige `local/chuspasocial:view`, y las publicaciones de un curso solo las ven los inscritos o quienes tienen acceso al curso.

Actualmente la acción de ocultar se aplica a publicaciones (`local_chuspasocial_post`). Para reportes de comentarios o libros, `resolve_and_hide()` no tiene un efecto equivalente documentado.

### Los posts ocultos no aparecen en los listados

`can_view()` solo decide si un usuario puede acceder a un post concreto. Un post oculto (`hidden = 1`) **no aparece en los listados** del muro ni de los cursos, tampoco para su autor o para un moderador: los listados filtran los posts ocultos (`hidden = 0`). Por eso, aunque el autor o el moderador tenga permiso de acceso, solo puede llegar a un post oculto de forma directa, no navegando por el muro.

---

## Documentos relacionados

- [Permisos](permisos.md)
- [Eventos y observadores](guia-eventos.md)
