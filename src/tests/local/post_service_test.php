<?php

namespace local_chuspasocial\local;

defined('MOODLE_INTERNAL') || die();

use advanced_testcase;

/**
 * Pruebas unitarias de post_service.
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
     * Test: un post normal se guarda en el muro general (courseid 0).
     *
     * @covers \local_chuspasocial\local\post_service::create_post
     */
    public function test_create_post_muro_general(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $id = post_service::create_post('Hola muro general', FORMAT_HTML);

        $this->assertGreaterThan(0, $id);
        $record = $DB->get_record('local_chuspasocial_post', ['id' => $id], '*', MUST_EXIST);
        $this->assertEquals($user->id, $record->userid);
        $this->assertEquals('Hola muro general', $record->content);
        $this->assertEquals(FORMAT_HTML, (int) $record->format);
        $this->assertEquals('post', $record->type);
        $this->assertEquals(0, (int) $record->courseid);
    }

    /**
     * Test: un usuario con la capacidad puede publicar un anuncio.
     *
     * @covers \local_chuspasocial\local\post_service::create_post
     */
    public function test_create_post_anuncio_con_capacidad(): void {
        global $DB;

        $this->setAdminUser();

        $id = post_service::create_post('Anuncio oficial', FORMAT_HTML, 'announcement');

        $record = $DB->get_record('local_chuspasocial_post', ['id' => $id], '*', MUST_EXIST);
        $this->assertEquals('announcement', $record->type);
    }

    /**
     * Test: sin la capacidad no se puede publicar un anuncio.
     *
     * @covers \local_chuspasocial\local\post_service::create_post
     */
    public function test_create_post_anuncio_sin_capacidad(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(\required_capability_exception::class);
        post_service::create_post('Anuncio no permitido', FORMAT_HTML, 'announcement');
    }

    /**
     * Test: si falla la capacidad, no se guarda ningun registro.
     *
     * @covers \local_chuspasocial\local\post_service::create_post
     */
    public function test_create_post_anuncio_sin_capacidad_no_guarda(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $antes = $DB->count_records('local_chuspasocial_post');

        try {
            post_service::create_post('Anuncio no permitido', FORMAT_HTML, 'announcement');
            $this->fail('Se esperaba required_capability_exception');
        } catch (\required_capability_exception $e) {
            $this->assertSame($antes, $DB->count_records('local_chuspasocial_post'));
        }
    }
}

