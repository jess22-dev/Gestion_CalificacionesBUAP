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
        $m = $alumno['manual'];
        
        // Lógica de cálculo idéntica para asegurar consistencia
        $notasTeams = $alumno['notas_teams'] ?? [];
        $promedioActividades = count($notasTeams) > 0 ? array_sum($notasTeams) / count($notasTeams) : 0;
        $notaU1 = ($m['recuperacion_u1'] !== null && $m['recuperacion_u1'] !== '') ? (float)$m['recuperacion_u1'] : (float)$m['examen_u1'];
        $promExamenes = ($notaU1 + (float)$m['examen_u2_u3']) / 2;

        $promedioReal = ($m['participacion'] * 0.10) + ($promedioActividades * 0.30) + ($m['proyecto'] * 0.40) + ($promExamenes * 0.20);
        
        // Redondeo oficial
        $finalRedondeado = ($promedioReal >= 5.5 && $promedioReal < 6.0) ? 5 : round($promedioReal);

        return [
            explode('@', $alumno['email'])[0], // Matrícula
            mb_strtoupper($alumno['nombre']), // Nombre en mayúsculas para formalidad
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