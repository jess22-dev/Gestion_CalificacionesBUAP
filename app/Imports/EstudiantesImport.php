<?php

namespace App\Imports;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        $estudiante = Estudiante::where('email', $row['email'])
                                ->orWhere('codigo_estudiante', (string) $row['codigo_estudiante'])
                                ->first();

        if ($estudiante) {
            if ($estudiante->estaEnMateria($this->materiaNrc)) {
                $this->duplicados[] = [
                    'nombre' => $estudiante->nombre,
                    'email'  => $estudiante->email,
                    'codigo' => $estudiante->codigo_estudiante,
                ];
                return null;
            }

            if ($estudiante->estaEnOtraMateria($this->materiaNrc)) {
                $this->yaEnOtraMateria[] = [
                    'nombre' => $estudiante->nombre,
                    'email'  => $estudiante->email,
                    'codigo' => $estudiante->codigo_estudiante,
                ];
            }

            // Vincular a materia_estudiante
            $estudiante->materias()->attach($this->materiaNrc, [
                'profesor_id' => Auth::id(),
                'status'      => 'activo',
            ]);

            // Vincular en alumno_materia si tiene user
            $userAlumno = User::where('email', $estudiante->email)->first();
            if ($userAlumno) {
                $this->vincularAlumnoMateria($userAlumno, $estudiante->codigo_estudiante);
            }

            $this->importados++;
            return null;
        }

        // Nuevo estudiante
        $claveUnica = Estudiante::generarClaveUnica();

        $nuevoEstudiante = Estudiante::create([
            'nombre'            => $row['nombre'],
            'email'             => $row['email'],
            'codigo_estudiante' => (string) $row['codigo_estudiante'],
            'clave_unica'       => $claveUnica,
        ]);

        // Crear user con rol alumno
        $userAlumno = User::firstOrCreate(
            ['email' => $row['email']],
            [
                'name'     => $row['nombre'],
                'password' => Hash::make(Str::random(16)),
                'role'     => 'alumno',
            ]
        );

        // Vincular en materia_estudiante
        $nuevoEstudiante->materias()->attach($this->materiaNrc, [
            'profesor_id' => Auth::id(),
            'status'      => 'activo',
        ]);

        // Vincular en alumno_materia
        $this->vincularAlumnoMateria($userAlumno, (string) $row['codigo_estudiante'], $claveUnica);

        $this->importados++;
        return null;
    }

    private function vincularAlumnoMateria(User $user, string $codigoEstudiante, string $claveAsistencia = null): void
    {
        $yaVinculado = $user->materias()->where('materia_nrc', $this->materiaNrc)->exists();

        if (!$yaVinculado) {
            $user->materias()->attach($this->materiaNrc, [
                'clave_unica'      => $codigoEstudiante,
                'clave_asistencia' => $claveAsistencia ?? Estudiante::generarClaveUnica(),
                'status'           => 'activo',
            ]);
        }
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