<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ActaOficialExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $alumnos;

    public function __construct($alumnos)
    {
        $this->alumnos = $alumnos;
    }

    public function collection()
    {
        return collect($this->alumnos);
    }

    public function headings(): array
    {
        return [
            'MATRÍCULA',
            'NOMBRE DEL ALUMNO',
            'CALIFICACIÓN FINAL'
        ];
    }

    public function map($alumno): array
    {
        $m = $alumno['manual'] ?? [];
        
        $participacion   = (float)($m['participacion'] ?? 0);
        $proyecto        = (float)($m['proyecto'] ?? 0);
        $examenU1        = (float)($m['examen_u1'] ?? 0);
        $examenU2U3      = (float)($m['examen_u2_u3'] ?? 0);
        $recuperacionU1  = $m['recuperacion_u1'] ?? null;

        // Notas de Teams
        $notasTeams = $alumno['notas_teams'] ?? [];
        
        $promedioActividades = 0.0;
        if (!empty($notasTeams) && count($notasTeams) > 0) {
            $promedioActividades = array_sum($notasTeams) / count($notasTeams);
        }

        // --- LÓGICA DE EXÁMENES ---
        $notaU1 = ($recuperacionU1 !== null && $recuperacionU1 !== '') ? (float)$recuperacionU1 : $examenU1;
        $promExamenes = ($notaU1 + $examenU2U3) / 2;

        // --- CÁLCULO PONDERADO REAL ---
        $promedioReal = ($participacion * 0.10) + 
                        ($promedioActividades * 0.30) + 
                        ($proyecto * 0.40) + 
                        ($promExamenes * 0.20);
        
        // --- CRITERIO DE REDONDEO SEGURO (IGUAL A JAVASCRIPT) ---
        // Si el promedio se queda en la franja de reprobado pero alcanza el 5.5, baja a 5.
        if ($promedioReal >= 5.5 && $promedioReal < 6.0) {
            $finalRedondeado = 5;
        } else {
            // Replicamos el Math.round() de JavaScript usando PHP_ROUND_HALF_UP explicitamente
            $finalRedondeado = (int) round($promedioReal, 0, PHP_ROUND_HALF_UP);
        }

        return [
            $alumno['matricula'] ?? 'SIN MATRÍCULA', 
            mb_strtoupper($alumno['nombre'] ?? ''), 
            $finalRedondeado
        ];
    }
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002d62']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}