<?php

namespace App\Imports;

use App\Models\Estudiante;
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

    private array $duplicados = [];
    private int $importados = 0;

    public function model(array $row)
    {
        // Ignorar filas vacías
        if (empty($row['nombre']) && empty($row['email']) && empty($row['codigo_estudiante'])) {
            return null;
        }

        // Verificar duplicados
        $existe = Estudiante::where('email', $row['email'])
                            ->orWhere('codigo_estudiante', (string) $row['codigo_estudiante'])
                            ->first();

        if ($existe) {
            $this->duplicados[] = [
                'nombre' => $row['nombre'],
                'email'  => $row['email'],
                'codigo' => (string) $row['codigo_estudiante'],
            ];
            return null;
        }

        $this->importados++;

        return new Estudiante([
            'nombre'            => $row['nombre'],
            'email'             => $row['email'],
            'codigo_estudiante' => (string) $row['codigo_estudiante'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.nombre'            => ['nullable', 'string', 'max:255'],
            '*.email'             => ['nullable', 'email', 'max:255'],
            '*.codigo_estudiante' => ['nullable'],
        ];
    }

    public function getDuplicados(): array { return $this->duplicados; }
    public function getImportados(): int   { return $this->importados; }

    public function batchSize(): int { return 100; }
    public function chunkSize(): int { return 200; }
}
