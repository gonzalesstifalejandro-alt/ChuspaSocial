# Casos de prueba funcionales — Comentarios y reacciones

Casos manuales para las funciones de comentarios y reacciones. Cada caso referencia
el requerimiento funcional (RF) de `docs/requerimientos.md` que verifica.

| ID | RF | Precondición | Pasos | Resultado esperado |
|---|---|---|---|---|
| CP-COM-01 | RF-010 | Usuario autenticado que puede ver una publicación del muro. | 1. Abrir el muro. 2. En la publicación, escribir un comentario. 3. Enviarlo. | El comentario queda registrado en la publicación. |
| CP-COM-02 | RF-011 | Usuario autenticado que es autor de un comentario en una publicación. | 1. Abrir la publicación. 2. En su comentario, elegir la opción Eliminar. 3. Confirmar la acción. | El comentario se elimina y deja de mostrarse en la publicación. |
| CP-COM-03 | RF-012 | Una publicación tiene al menos dos comentarios hechos en momentos distintos. | 1. Abrir el muro. 2. Revisar los comentarios de la publicación. | Los comentarios se muestran bajo la publicación, en orden cronológico. |
| CP-COM-04 | RF-013 | Usuario autenticado que puede ver una publicación a la que todavía no reaccionó. | 1. Abrir el muro. 2. Hacer clic en «Me gusta» en la publicación. | La reacción queda registrada en la publicación. |
| CP-COM-05 | RF-014 | Una publicación tiene al menos una reacción. | 1. Abrir el muro. 2. Revisar la publicación. | La publicación muestra el total de sus reacciones. |
| CP-COM-06 | RF-010 | Usuario autenticado que puede ver una publicación del muro. | 1. Abrir el muro. 2. Seleccionar una publicación visible. 3. Dejar vacío el campo de comentario. 4. Intentar enviarlo. | El sistema impide publicar el comentario vacío y no registra ningún comentario nuevo. |
