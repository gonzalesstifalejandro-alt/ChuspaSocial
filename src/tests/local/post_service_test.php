<?php

namespace local_chuspasocial\local;

defined('MOODLE_INTERNAL') || die();

use advanced_testcase;

/**
 * Pruebas unitarias para el metodo can_post_announcement.
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\local\post_service::can_post_announcement
 * @covers     \local_chuspasocial\local\post_service::create_post
 */
class post_service_test extends advanced_testcase {
    /**
     * Configuracion previa a cada test.
     */
    protected function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test: Un docente puede publicar anuncios en su curso.
     */
    public function test_can_post_announcement_docente() {
        $generator = $this->getDataGenerator();

        $course = $generator->create_course();
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'editingteacher');

        $this->setUser($user);

        $result = post_service::can_post_announcement($course->id);
        $this->assertTrue($result);
    }

    /**
     * Test: Un estudiante No puede publicar anuncios.
     */
    public function test_can_post_announcement_estudiante(): void {
        $generator = $this->getDataGenerator();

        $course = $generator->create_course();
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');

        $this->setUser($user);

        $result = post_service::can_post_announcement($course->id);
        $this->assertFalse($result);
    }

    /**
     * Test: Un administrador puede publicar anuncios en el muro general (site course / frontpage).
     */
    public function test_can_post_announcement_admin_muro_general(): void {
        global $SITE;

        $this->setAdminUser();

        $result = post_service::can_post_announcement($SITE->id);
        $this->assertTrue($result);
    }

        /**
     * Test: se puede crear una publicacion en el muro general.
     */
    public function test_create_post_muro_general(): void {
        global $DB, $SITE;

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // TODO: ajusta los argumentos a la firma real.
        $result = post_service::create_post($SITE->id, 'Hola muro general');

        $this->assertNotEmpty($result);
        // TODO: verifica el registro, p. ej. $DB->record_exists('local_chuspasocial_post', [...]).
    }

    /**
     * Test: un usuario inscrito puede publicar en su materia.
     */
    public function test_create_post_materia_inscrita(): void {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        $result = post_service::create_post($course->id, 'Hola materia');

        $this->assertNotEmpty($result);
    }

    /**
     * Test: un usuario no inscrito no puede publicar en la materia.
     */
    public function test_create_post_materia_no_inscrita(): void {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user(); // Sin inscribir.
        $this->setUser($user);

        // TODO: ajusta al comportamiento real (excepcion o false).
        $this->expectException(\moodle_exception::class);
        post_service::create_post($course->id, 'No deberia poder');
    }

    /**
     * Test: sin la capacidad requerida no se puede publicar.
     */
    public function test_create_post_sin_capacidad(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        $studentrole = $DB->get_field('role', 'id', ['shortname' => 'student']);
        // TODO: pon el nombre real de la capacidad que exige create_post().
        assign_capability('local/chuspasocial:post', CAP_PROHIBIT, $studentrole,
            \context_course::instance($course->id), true);

        $this->expectException(\required_capability_exception::class);
        post_service::create_post($course->id, 'Sin permiso');
    }
}

