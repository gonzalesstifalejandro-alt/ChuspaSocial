<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_chuspasocial\local\persistent;

use core\persistent;

/**
 * Clase persistent para la tabla local_chuspasocial_report.
 *
 * Representa un reporte hecho por un usuario sobre una publicacion,
 * un comentario o un anuncio de libro.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report extends persistent {

    /** @var string Nombre de la tabla asociada a esta clase. */
    const TABLE = 'local_chuspasocial_report';

    const STATUS_PENDING = 0;
    const STATUS_REVIEWED = 1;
    const STATUS_DISMISSED = 2;
    const STATUS_ACTIONED = 3;

    /**
     * Define las propiedades de la tabla local_chuspasocial_report.
     *
     * @return array Definicion de cada campo con su tipo PARAM_*, valor por defecto y choices.
     */
    protected static function define_properties(): array {
        return [
            'itemtype' => [
                'type' => PARAM_ALPHA,
                'choices' => ['post', 'comment', 'listing'],
            ],
            'itemid' => [
                'type' => PARAM_INT,
            ],
            'reporterid' => [
                'type' => PARAM_INT,
            ],
            'reason' => [
                'type' => PARAM_TEXT,
            ],
            'status' => [
                'type' => PARAM_INT,
                'default' => self::STATUS_PENDING,
            ],
            'resolvedby' => [
                'type' => PARAM_INT,
                'default' => null,
                'null' => NULL_ALLOWED,
            ],
            'timeresolved' => [
                'type' => PARAM_INT,
                'default' => null,
                'null' => NULL_ALLOWED,
            ],
        ];
    }

    protected function validate_status($value) {
        if (!in_array($value, [
            self::STATUS_PENDING,
            self::STATUS_REVIEWED,
            self::STATUS_DISMISSED,
            self::STATUS_ACTIONED,
        ], true)) {
            return 'El estado del reporte no es válido.';
        }

        return true;
    }
}
