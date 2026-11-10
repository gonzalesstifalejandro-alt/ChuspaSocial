namespace local_chuspasocial\event;

use advanced_testcase;
use local_chuspasocial\local\post_service;

/**
 * Pruebas para el evento post_created.
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chuspasocial\event\post_created
 */
class post_created_test extends advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Publicar en el muro general dispara exactamente un evento post_created
     * con el objectid correcto.
     */
    public function test_publicar_en_muro_general_dispara_un_evento(): void {
        global $DB;

        // Permitir publicar a los usuarios autenticados en el contexto del sistema.
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'user'], MUST_EXIST);
        assign_capability(
            'local/chuspasocial:post',
            CAP_ALLOW,
            $roleid,
            \context_system::instance()->id,
            true
        );

        $autor = $this->getDataGenerator()->create_user();
        $this->setUser($autor);

        $sink = $this->redirectEvents();
        $post = post_service::create_post(0, 'Hola muro general', FORMAT_HTML);
        $events = $sink->get_events();
        $sink->close();

        $creados = array_values(array_filter($events, function ($event) {
            return $event instanceof post_created;
        }));

        $this->assertCount(1, $creados);
        $this->assertEquals($post->get('id'), $creados[0]->objectid);
        $this->assertEquals($autor->id, $creados[0]->userid);
        $this->assertEquals(\context_system::instance()->id, $creados[0]->contextid);
        $this->assertEventContextNotUsed($creados[0]);
    }
}