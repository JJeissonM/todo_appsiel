<?php

class HotelPermissionSqlTest extends PHPUnit_Framework_TestCase
{
    private function database()
    {
        $db = new PDO('sqlite::memory:');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec('PRAGMA foreign_keys = ON');
        $db->exec('CREATE TABLE sys_aplicaciones (id INTEGER PRIMARY KEY, app TEXT, descripcion TEXT)');
        $db->exec('CREATE TABLE permissions (id INTEGER PRIMARY KEY, core_app_id INTEGER REFERENCES sys_aplicaciones(id), modelo_id INTEGER, name TEXT UNIQUE, descripcion TEXT, url TEXT, parent INTEGER, orden INTEGER, enabled INTEGER, fa_icon TEXT, created_at TEXT, updated_at TEXT)');
        return $db;
    }

    private function runPermissionSql(PDO $db)
    {
        $sql = file_get_contents(__DIR__ . '/../../database/scripts/cambios_bd__appsiel_10.sql');
        preg_match_all('/INSERT INTO `permissions`[^;]+;/s', $sql, $matches);
        $executed = 0;
        foreach ($matches[0] as $statement) {
            if (strpos($statement, "'hotel_pedido_retirar_producto_habitacion'") !== false
                || strpos($statement, "'hotel_pedido_anular'") !== false) {
                $db->exec($statement);
                $executed++;
            }
        }
        $this->assertSame(2, $executed);
    }

    public function test_usa_id_real_y_no_duplica_al_repetir()
    {
        $db = $this->database();
        $db->exec("INSERT INTO sys_aplicaciones VALUES (45, 'hotel', 'Gestión Hotelera'), (22, 'otra', 'Otra aplicación')");
        $this->runPermissionSql($db);
        $this->runPermissionSql($db);
        $this->assertSame(2, (int) $db->query('SELECT COUNT(*) FROM permissions')->fetchColumn());
        $this->assertSame(2, (int) $db->query('SELECT COUNT(*) FROM permissions WHERE core_app_id = 45')->fetchColumn());
    }

    public function test_no_inserta_permisos_si_hotel_no_existe()
    {
        $db = $this->database();
        $db->exec("INSERT INTO sys_aplicaciones VALUES (22, 'otra', 'Otra aplicación')");
        $this->runPermissionSql($db);
        $this->assertSame(0, (int) $db->query('SELECT COUNT(*) FROM permissions')->fetchColumn());
    }

    public function test_encuentra_aplicacion_por_descripcion()
    {
        $db = $this->database();
        $db->exec("INSERT INTO sys_aplicaciones VALUES (20, '', 'Gestion Hotelera')");
        $this->runPermissionSql($db);
        $this->assertSame(2, (int) $db->query('SELECT COUNT(*) FROM permissions WHERE core_app_id = 20')->fetchColumn());
    }
}
