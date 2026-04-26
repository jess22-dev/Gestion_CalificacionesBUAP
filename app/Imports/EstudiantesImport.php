<?php

namespace App\Imports;

use App\Models\Estudiante;
use App\Models\Materia;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class EstudiantesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, WithBatchInserts, WithChunkReading
{
    use SkipsErrors;

    private string $materiaNrc;
    private array $duplicados = [];
    private array $yaEnOtraMateria = [];
    private int $importados = 0;

    public function __construct(string $materiaNrc)
    {
        $this->materiaNrc = $materiaNrc;
    }

    public function model(array $row)
    {
        if (empty($row['nombre']) && empty($row['email']) && empty($row['codigo_estudiante'])) {
            return null;
        }

        // Buscar si el estudiante ya existe en la tabla estudiantes
        $estudiante = Estudiante::where('email', $row['email'])
                                ->orWhere('codigo_estudiante', (string) $row['codigo_estudiante'])
                                ->first();

        if ($estudiante) {
            // Ya existe — verificar si ya está en ESTA materia
            if ($estudiante->estaEnMateria($this->materiaNrc)) {
                $this->duplicados[] = [
                    'nombre' => $estudiante->nombre,
                    'email'  => $estudiante->email,
                    'codigo' => $estudiante->codigo_estudiante,
                ];
                return null;
            }

            // Está en otra materia — vincularlo a esta
            if ($estudiante->estaEnOtraMateria($this->materiaNrc)) {
                $this->yaEnOtraMateria[] = [
                    'nombre' => $estudiante->nombre,
                    'email'  => $estudiante->email,
                    'codigo' => $estudiante->codigo_estudiante,
                ];
            }

            // Vincular a esta materia
            $estudiante->materias()->attach($this->materiaNrc, [
                'profesor_id' => Auth::id(),
                'status'      => 'activo',
            ]);

            $this->importados++;
            return null; // No crear nuevo registro en estudiantes
        }

        // Nuevo estudiante — crear y vincular
        $nuevoEstudiante = new Estudiante([
            'nombre'            => $row['nombre'],
            'email'             => $row['email'],
            'codigo_estudiante' => (string) $row['codigo_estudiante'],
        ]);

        $nuevoEstudiante->save();

        $nuevoEstudiante->materias()->attach($this->materiaNrc, [
            'profesor_id' => Auth::id(),
            'status'      => 'activo',
        ]);

        $this->importados++;
        return null;
    }

    public function rules(): array
    {
        return [
            '*.nombre'            => ['nullable', 'string', 'max:255'],
            '*.email'             => ['nullable', 'email', 'max:255'],
            '*.codigo_estudiante' => ['nullable'],
        ];
    }

    public function getDuplicados(): array      { return $this->duplicados; }
    public function getYaEnOtraMateria(): array { return $this->yaEnOtraMateria; }
    public function getImportados(): int        { return $this->importados; }

    public function batchSize(): int { return 100; }
    public function chunkSize(): int { return 200; }
}