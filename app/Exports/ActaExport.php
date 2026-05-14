<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ActaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected $alumnos;
    protected $actividades;
    protected $materia;

    public function __construct($alumnos, $actividades, $materia)
    {
        $this->alumnos = $alumnos;
        $this->actividades = $actividades;
        $this->materia = $materia;
    }

    public function collection()
    {
        return collect($this->alumnos);
    }

    public function headings(): array
    {
        $filaBase = ['Nombre del Alumno', 'Correo'];
        
        // Columnas de Teams (Dinámicas)
        $teamsHeaders = $this->actividades;

        // Columnas Finales (Manuales y Cálculos)
        $filaFinal = [
            'PART (10%)', 
            'PROY (40%)', 
            'EXAM U1', 
            'EXAM U2-U3', 
            'RECUP U1', 
            'Promedio Real', 
            'Final Acta'
        ];

        return array_merge($filaBase, $teamsHeaders, $filaFinal);
    }

    public function map($alumno): array
    {
        // 1. Datos base
        $mapeo = [
            $alumno['nombre'],
            $alumno['email'],
        ];

        // 2. Notas de Teams
        foreach ($this->actividades as $actividad) {
            $mapeo[] = $alumno['notas_teams'][$actividad] ?? 0;
        }

        // 3. Datos manuales en orden
        $mapeo[] = $alumno['manual']['participacion'];
        $mapeo[] = $alumno['manual']['proyecto'];
        $mapeo[] = $alumno['manual']['examen_u1'];
        $mapeo[] = $alumno['manual']['examen_u2_u3'];
        
        $recup = $alumno['manual']['recuperacion_u1'];
        $mapeo[] = ($recup !== null && $recup !== '') ? $recup : '-';

        // --- LÓGICA DE CÁLCULO (Sincronizada con ActaApp.js) ---
        
        // A. Promedio Teams (30%)
        $promTeams = count($alumno['notas_teams']) > 0 
            ? array_sum($alumno['notas_teams']) / count($alumno['notas_teams']) 
            : 0;

        // B. Promedio Exámenes (20%) - Lógica de Recuperación
        $notaU1Efectiva = ($recup !== null && $recup !== '') ? (float)$recup : (float)$alumno['manual']['examen_u1'];
        $promExamen = ($notaU1Efectiva + (float)$alumno['manual']['examen_u2_u3']) / 2;

        // C. Calificación Final con Pesos Oficiales
        $final = ($alumno['manual']['participacion'] * 0.10) + 
                 ($promTeams * 0.30) + 
                 ($alumno['manual']['proyecto'] * 0.40) + 
                 ($promExamen * 0.20);

        // D. Redondeo BUAP
        // 5.5 a 5.9 -> 5. 6.0+ -> Redondeo normal.
        $redondeado = ($final >= 5.5 && $final < 6.0) ? 5 : round($final);

        $mapeo[] = round($final, 2); // Columna Promedio Real
        $mapeo[] = $redondeado;      // Columna Final Acta

        return $mapeo;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '002d62'], // Azul BUAP
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                // Centrar todo excepto los nombres
                $sheet->getStyle('C1:'.$highestCol.$highestRow)
                      ->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Formato condicional simple: poner en rojo los reprobados en la última columna
                for ($row = 2; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell($highestCol . $row)->getValue();
                    if (is_numeric($cellValue) && $cellValue < 6) {
                        $sheet->getStyle($highestCol . $row)->getFont()->getColor()->setRGB('FF0000');
                        $sheet->getStyle($highestCol . $row)->getFont()->setBold(true);
                    }
                }
            },
        ];
    }
}