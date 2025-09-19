<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            throw new \RuntimeException("La tabla 'users' no existe. Corre 'php artisan migrate' primero.");
        }

        // 1) orden_trabajo.id_creador -> users.id
        $this->repointFk(
            table: 'orden_trabajo',           // <- TU TABLA ACTUAL (singular)
            column: 'id_creador',
            oldReferencedTable: 'usuario',
            newReferencedTable: 'users',
            // nombre típico del constraint en tu SQL era fk_orden_trabajo_usuario1 (ajústalo si difiere)
            possibleConstraintNames: ['fk_orden_trabajo_usuario1']
        );

        // 2) asignacion_orden.usuario_id -> users.id
        $this->repointFk(
            table: 'asignacion_orden',
            column: 'usuario_id',
            oldReferencedTable: 'usuario',
            newReferencedTable: 'users',
            possibleConstraintNames: ['fk_orden_trabajo_has_usuario_usuario1']
        );

        // 3) especialidad_usuario.usuario_id -> users.id
        $this->repointFk(
            table: 'especialidad_usuario',
            column: 'usuario_id',
            oldReferencedTable: 'usuario',
            newReferencedTable: 'users',
            possibleConstraintNames: ['fk_especialidad_has_usuario_usuario1']
        );
    }

    public function down(): void
    {
        // Volver a referenciar a 'usuario'
        $this->repointFk(
            table: 'orden_trabajo',
            column: 'id_creador',
            oldReferencedTable: 'users',
            newReferencedTable: 'usuario',
            possibleConstraintNames: [] // el nombre puede cambiar; no es crítico para rollback local
        );

        $this->repointFk(
            table: 'asignacion_orden',
            column: 'usuario_id',
            oldReferencedTable: 'users',
            newReferencedTable: 'usuario',
            possibleConstraintNames: []
        );

        $this->repointFk(
            table: 'especialidad_usuario',
            column: 'usuario_id',
            oldReferencedTable: 'users',
            newReferencedTable: 'usuario',
            possibleConstraintNames: []
        );
    }

    private function repointFk(string $table, string $column, string $oldReferencedTable, string $newReferencedTable, array $possibleConstraintNames): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        $constraint = $this->findConstraint($table, $column, $oldReferencedTable);

        Schema::table($table, function (Blueprint $t) use ($column, $constraint, $possibleConstraintNames, $newReferencedTable) {
            if ($constraint) {
                try { $t->dropForeign($constraint); } catch (\Throwable $e) {}
            }

            foreach ($possibleConstraintNames as $name) {
                try { $t->dropForeign($name); } catch (\Throwable $e) {}
            }

            try { $t->dropForeign([$column]); } catch (\Throwable $e) {}

            $t->foreign($column)->references('id')->on($newReferencedTable)
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    private function findConstraint(string $table, string $column, string $referencedTable): ?string
    {
        $sql = "
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME = ?
            LIMIT 1
        ";
        $row = DB::selectOne($sql, [$table, $column, $referencedTable]);
        return $row?->CONSTRAINT_NAME ?? null;
    }
};
