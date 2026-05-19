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

    // Pesos (deben coincidir con los valores por defecto del frontend)
    protected $wPart  = 0.10;
    protected $wTeams = 0.30; // tareas + prácticas juntas
    protected $wProy  = 0.40;
    protected $wExam  = 0.20;

    public function __construct($alumnos, $actividades, $materia)
    {
        $this->alumnos     = $alumnos;
        $this->actividades = $actividades;
        $this->materia     = $materia;
    }

    public function collection()
    {
        return collect($this->alumnos)->values(); // reindexar para que el #fila sea correcto
    }

    public function headings(): array
    {
        $base = [
            '#',
            'Nombre del Alumno',
            'ID (Matrícula)',
            'Participaciones',
        ];

        // Columnas dinámicas de actividades
        $actividades = array_values($this->actividades);

        $finales = [
            'Proyecto',
            'Unidad 1',
            'Unidad 2 y 3',
            'Recuperación U1',
            'Calificación',
            'Final',
        ];

        return array_merge($base, $actividades, $finales);
    }

    // -------------------------------------------------------------------------
    // MAPEO DE FILAS
    // -------------------------------------------------------------------------
    public function map($alumno): array
    {
        static $numero = 0;
        $numero++;

        // — Columnas fijas —
        $fila = [
            $numero,                                          // A  #
            $alumno['nombre'],                               // B  Nombre
            $alumno['email'] ?? '',                          // C  ID / Matrícula
            (float)($alumno['manual']['participacion'] ?? 0), // D  Participaciones
        ];

        // — Columnas dinámicas de actividades —
        foreach ($this->actividades as $actividad) {
            $fila[] = (float)($alumno['notas'][$actividad] ?? $alumno['notas_teams'][$actividad] ?? 0);
        }

        // — Datos manuales finales —
        $proyecto  = (float)($alumno['manual']['proyecto']      ?? 0);
        $u1        = (float)($alumno['manual']['examen_u1']     ?? 0);
        $u2u3      = (float)($alumno['manual']['examen_u2_u3']  ?? 0);
        $recupRaw  = $alumno['manual']['recuperacion_u1'] ?? null;
        $recup     = ($recupRaw !== null && $recupRaw !== '') ? (float)$recupRaw : null;

        $fila[] = $proyecto;                                           // Proyecto
        $fila[] = $u1;                                                 // Unidad 1
        $fila[] = $u2u3;                                               // Unidad 2 y 3
        $fila[] = ($recup !== null) ? $recup : '-';                    // Recuperación U1

        // — Cálculo sincronizado con el frontend —

        // Promedio de actividades (tareas + prácticas mezcladas)
        $notas = [];
        foreach ($this->actividades as $actividad) {
            $notas[] = (float)($alumno['notas'][$actividad] ?? $alumno['notas_teams'][$actividad] ?? 0);
        }
        $promActividades = count($notas) > 0 ? array_sum($notas) / count($notas) : 0;

        // U1 efectiva (recuperación sustituye si existe)
        $u1Efectiva = ($recup !== null) ? $recup : $u1;

        // Promedio exámenes
        $promExamen = ($u1Efectiva + $u2u3) / 2;

        // Calificación sin redondear
        $participacion = (float)($alumno['manual']['participacion'] ?? 0);
        $calificacion  = ($participacion * $this->wPart)
                       + ($promActividades * $this->wTeams)
                       + ($proyecto        * $this->wProy)
                       + ($promExamen      * $this->wExam);

        $calificacion = round($calificacion, 2);

        // Redondeo BUAP: 5.5–5.9 → 5, resto → round()
        $final = ($calificacion >= 5.5 && $calificacion < 6.0) ? 5 : (int)round($calificacion);

        $fila[] = $calificacion; // Calificación (sin redondear)
        $fila[] = $final;        // Final

        return $fila;
    }

    // -------------------------------------------------------------------------
    // ESTILOS
    // -------------------------------------------------------------------------
    public function styles(Worksheet $sheet)
    {
        return [
            // Encabezado (fila 1)
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '002d62'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // EVENTOS POST-GENERACIÓN
    // -------------------------------------------------------------------------
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                // Altura de la fila de encabezado
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Centrar todo el contenido excepto Nombre (col B) y ID (col C)
                $sheet->getStyle('A1:' . $highestCol . $highestRow)
                      ->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                      ->setVertical(Alignment::VERTICAL_CENTER);

                // Alinear Nombre e ID a la izquierda
                $sheet->getStyle('B2:B' . $highestRow)
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('C2:C' . $highestRow)
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Formato condicional: reprobados en rojo (columna Final = última)
                for ($row = 2; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell($highestCol . $row)->getValue();
                    if (is_numeric($cellValue) && $cellValue < 6) {
                        $sheet->getStyle($highestCol . $row)
                              ->getFont()
                              ->getColor()->setRGB('CC0000');
                        $sheet->getStyle($highestCol . $row)
                              ->getFont()->setBold(true);
                    }
                }

                // Bordes en toda la tabla
                $sheet->getStyle('A1:' . $highestCol . $highestRow)
                      ->getBorders()->getAllBorders()
                      ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Columna # más estrecha
                $sheet->getColumnDimension('A')->setWidth(6);
            },
        ];
    }
}