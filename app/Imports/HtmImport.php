<?php

namespace App\Imports;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HtmImport
{
    private string $materiaNrc;
    private array $importados      = [];
    private array $duplicados      = [];
    private array $yaEnOtraMateria = [];
    private array $codigosHtm      = []; // códigos encontrados en el HTM

    public function __construct(string $materiaNrc)
    {
        $this->materiaNrc = $materiaNrc;
    }

    /**
     * Procesar HTM de BUAP
     */
    public function procesar(string $htmContent): void
    {
        $alumnos = $this->parsearHtm($htmContent);
        foreach ($alumnos as $alumno) {
            if ($alumno['codigo']) {
                $this->codigosHtm[] = $alumno['codigo'];
            }
            $this->registrarAlumno($alumno);
        }
    }

    private function parsearHtm(string $html): array
    {
        $alumnos = [];

        preg_match_all('/href="mailto:([^"]+)"/i', $html, $emailMatches);
        $emails = $emailMatches[1] ?? [];

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $filas = $xpath->query('//table[contains(@summary,"lista de alumnos")]//tr');

        $emailIndex = 0;

        foreach ($filas as $fila) {
            $celdas = $fila->getElementsByTagName('td');
            if ($celdas->length < 3) continue;

            $numero = trim($celdas->item(0)->textContent);
            if (!is_numeric($numero) || (int)$numero === 0) continue;

            $nombre = strtoupper(preg_replace('/\s+/', ' ', trim($celdas->item(1)->textContent)));
            $codigo = trim($celdas->item(2)->textContent);
            $email  = isset($emails[$emailIndex]) ? strtolower(trim($emails[$emailIndex])) : null;
            $emailIndex++;

            if ($nombre && $codigo) {
                $alumnos[] = [
                    'nombre' => $nombre,
                    'email'  => $email,
                    'codigo' => $codigo,
                ];
            }
        }

        return $alumnos;
    }


    private function registrarAlumno(array $data): void
    {
        $nombre = $data['nombre'];
        $email  = $data['email'] ?? null;
        $codigo = $data['codigo'] ?? null;

        $existente = null;
        if ($codigo) $existente = Estudiante::where('codigo_estudiante', $codigo)->first();
        if (!$existente && $email) $existente = Estudiante::where('email', $email)->first();

        if ($existente) {
            // Verificar si ya está en la materia y con qué status
            $pivot = $existente->materias()
                ->where('materia_nrc', $this->materiaNrc)
                ->first();

            if ($pivot) {
                if ($pivot->pivot->status === 'activo') {
                    // Ya está activo -> duplicado, no hacer nada
                    $this->duplicados[] = ['nombre' => $existente->nombre, 'codigo' => $existente->codigo_estudiante];
                    return;
                } else {
                    // Estaba dado de baja -> reactivar
                    $existente->materias()->updateExistingPivot($this->materiaNrc, [
                        'status' => 'activo',
                    ]);

                    // Reactivar también en alumno_materia si existe
                    if ($email) {
                        $user = User::where('email', $email)->first();
                        if ($user) {
                            DB::table('alumno_materia')
                                ->where('alumno_id', $user->id)
                                ->where('materia_nrc', $this->materiaNrc)
                                ->update(['status' => 'activo', 'fecha_baja' => null]);
                        }
                    }

                    $this->importados[] = $existente->nombre;
                    return;
                }
            }

            // No está en esta materia -> verificar si está en otra
            if ($existente->estaEnOtraMateria($this->materiaNrc)) {
                $this->yaEnOtraMateria[] = ['nombre' => $existente->nombre];
            }

            $existente->materias()->attach($this->materiaNrc, ['profesor_id' => Auth::id(), 'status' => 'activo']);

            if ($email) {
                $user = User::where('email', $email)->first();
                if ($user) $this->vincularAlumnoMateria($user);
            }

            $this->importados[] = $existente->nombre;
            return;
        }

        $claveUnica      = Estudiante::generarClaveUnica();
        $nuevoEstudiante = Estudiante::create([
            'nombre'            => strtoupper($nombre),
            'email'             => $email,
            'codigo_estudiante' => $codigo,
            'clave_unica' => $claveUnica,
        ]);

        $nuevoEstudiante->materias()->attach($this->materiaNrc, ['profesor_id' => Auth::id(), 'status' => 'activo']);

        if ($email) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => strtoupper($nombre), 'password' => Hash::make(Str::random(16)), 'role' => 'alumno']
            );
            $this->vincularAlumnoMateria($user);
        }

        $this->importados[] = $nombre;
    }

    private function vincularAlumnoMateria(User $user): void
    {
        if ($user->materias()->where('materia_nrc', $this->materiaNrc)->exists()) return;

        do { $cu = strtoupper(Str::random(10)); }
        while (DB::table('alumno_materia')->where('clave_unica', $cu)->exists());

        do { $ca = strtoupper(Str::random(10)); }
        while (DB::table('alumno_materia')->where('clave_asistencia', $ca)->exists());

        $user->materias()->attach($this->materiaNrc, [
            'clave_unica'      => $cu,
            'clave_asistencia' => $ca,
            'status'           => 'activo',
        ]);
    }

    public function totalImportados(): int      { return count($this->importados); }
    public function getDuplicados(): array      { return $this->duplicados; }
    public function getYaEnOtraMateria(): array { return $this->yaEnOtraMateria; }
    public function getCodigosHtm(): array      { return $this->codigosHtm; }
}