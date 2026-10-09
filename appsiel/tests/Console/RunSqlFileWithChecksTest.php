<?php

use App\Console\Commands\RunSqlFileWithChecks;
use Illuminate\Support\Facades\DB;

class InspectableSqlFileCommand extends RunSqlFileWithChecks
{
    public $foreignKeys = [];
    public $indexes = [];
    public $columns = [];
    public $primary = [];
    public $equivalentForeignKey = false;

    protected function foreignKeyDefinitionExists($table, array $key) { return $this->equivalentForeignKey; }
    protected function validateForeignKeyData($clause, $table) {}
    public function validateData($clause) { return parent::validateForeignKeyData($clause, "nom_contratos"); }

    protected function foreignKeyExists($table, $name) { return in_array($name, $this->foreignKeys); }
    protected function indexExists($table, $name) { return in_array($name, $this->indexes); }
    protected function columnExists($table, $name) { return in_array($name, $this->columns); }
    protected function getPrimaryKeyColumns($table) { return $this->primary; }
    public function filter($sql) { return $this->filterAlterStatement($sql, 'example'); }
    public function existsFor($row) { return $this->rowExistsByConstraints('example', $row, ['id'], [['core_modelo_id', 'core_campo_id']]); }
}

class RunSqlFileWithChecksTest extends TestCase
{
    public function test_revisa_indice_compuesto_con_id_nulo()
    {
        $query = Mockery::mock();
        DB::shouldReceive('table')->once()->with('example')->andReturn($query);
        $query->shouldReceive('where')->once()->with('core_modelo_id', 336)->andReturnSelf();
        $query->shouldReceive('where')->once()->with('core_campo_id', 97)->andReturnSelf();
        $query->shouldReceive('exists')->once()->andReturn(true);
        $this->assertTrue((new InspectableSqlFileCommand)->existsFor(['id' => null, 'core_modelo_id' => 336, 'core_campo_id' => 97]));
    }

    public function test_revisa_indice_unico_aunque_la_clave_primaria_no_exista()
    {
        $pk = Mockery::mock();
        $unique = Mockery::mock();
        DB::shouldReceive('table')->twice()->with('example')->andReturn($pk, $unique);
        $pk->shouldReceive('where')->once()->with('id', 999)->andReturnSelf();
        $pk->shouldReceive('exists')->once()->andReturn(false);
        $unique->shouldReceive('where')->once()->with('core_modelo_id', 336)->andReturnSelf();
        $unique->shouldReceive('where')->once()->with('core_campo_id', 97)->andReturnSelf();
        $unique->shouldReceive('exists')->once()->andReturn(true);
        $this->assertTrue((new InspectableSqlFileCommand)->existsFor(['id' => 999, 'core_modelo_id' => 336, 'core_campo_id' => 97]));
    }

    public function test_omite_solo_la_clave_foranea_existente()
    {
        $command = new InspectableSqlFileCommand;
        $command->foreignKeys = ['fk_one'];
        $sql = 'ALTER TABLE `example` ADD CONSTRAINT `fk_one` FOREIGN KEY (`a`) REFERENCES `parent` (`id`), ADD CONSTRAINT `fk_two` FOREIGN KEY (`b`) REFERENCES `parent` (`id`)';
        $this->assertSame('ALTER TABLE `example` ADD CONSTRAINT `fk_two` FOREIGN KEY (`b`) REFERENCES `parent` (`id`)', $command->filter($sql));
        $command->foreignKeys[] = 'fk_two';
        $this->assertNull($command->filter($sql));
    }

    public function test_conserva_indices_pendientes_y_comas_dentro_de_parentesis_y_cadenas()
    {
        $command = new InspectableSqlFileCommand;
        $command->primary = ['id'];
        $command->indexes = ['existing'];
        $sql = "ALTER TABLE `example` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `existing` (`a`,`b`), ADD KEY `pending` (`a`,`b`), ADD `label` VARCHAR(50) DEFAULT 'a,b'";
        $this->assertSame("ALTER TABLE `example` ADD KEY `pending` (`a`,`b`), ADD `label` VARCHAR(50) DEFAULT 'a,b'", $command->filter($sql));
    }
    public function test_omite_clave_foranea_sin_nombre_si_la_relacion_ya_existe()
    {
        $command = new InspectableSqlFileCommand;
        $command->equivalentForeignKey = true;
        $this->assertNull($command->filter('ALTER TABLE `example` ADD FOREIGN KEY (`turno_default_id`) REFERENCES nom_turnos_tipos(id)'));
    }

    public function test_informa_registros_huerfanos_sin_modificarlos()
    {
        DB::shouldReceive('select')->once()->with(Mockery::on(function ($sql) {
            return strpos($sql, 'child.`turno_default_id` IS NOT NULL') !== false
                && strpos($sql, 'parent.`id` IS NULL') !== false;
        }))->andReturn([(object) ['total' => 3]]);
        try {
            (new InspectableSqlFileCommand)->validateData('ADD FOREIGN KEY (`turno_default_id`) REFERENCES nom_turnos_tipos(id)');
            $this->fail('Debe informar datos huerfanos.');
        } catch (RuntimeException $e) {
            $this->assertContains('3 registros sin referencia en nom_turnos_tipos', $e->getMessage());
            $this->assertContains('SELECT child.*', $e->getMessage());
        }
    }

    public function test_permite_clave_foranea_con_datos_validos()
    {
        DB::shouldReceive('select')->once()->andReturn([(object) ['total' => 0]]);
        $this->assertNull((new InspectableSqlFileCommand)->validateData('ADD FOREIGN KEY (`turno_default_id`) REFERENCES nom_turnos_tipos(id)'));
    }

}
