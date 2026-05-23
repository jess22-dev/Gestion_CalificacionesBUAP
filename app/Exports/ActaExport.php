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
    protected $tipos;        // [ nombre => tipo ]
    protected $ponderaciones; // [ tipo => % total ]
    protected $pesosIndividuales; // [ nombre => % individual ]

    public function __construct($alumnos, $actividades, $materia, $tipos = [], $ponderaciones = [])
    {
        $this->alumnos      = $alumnos;
        $this->actividades  = $actividades;
        $this->materia      = $materia;
        
        // Normalizar claves a minúsculas y reemplazar nulls por 'tarea'
        $tiposNorm = [];
        foreach ($tipos as $k => $v) {
            $tiposNorm[strtolower(trim($k))] = $v ?? 'tarea';
        }
        $this->tipos = $tiposNorm;

        $this->ponderaciones = array_merge([
            'participacion' => 10,
            'tarea'         => 10,
            'practica'      => 20,
            'proyecto'      => 40,
            'examen'        => 20,
            'recuperacion'  => 20,
        ], $ponderaciones);

        // Calcular peso individual por actividad
        // Contar cuántas actividades hay de cada tipo
        $contPorTipo = [];
        foreach ($actividades as $act) {
            $tipo = $this->tipos[strtolower(trim($act))] ?? 'tarea';
            $contPorTipo[$tipo] = ($contPorTipo[$tipo] ?? 0) + 1;
        }

        $this->pesosIndividuales = [];
        foreach ($actividades as $act) {
            $tipo = $this->tipos[strtolower(trim($act))] ?? 'tarea';
            $totalTipo = $this->ponderaciones[$tipo] ?? 10;
            $count     = $contPorTipo[$tipo] ?? 1;
            $this->pesosIndividuales[$act] = round($totalTipo / $count, 1);
        }
    }

    public function collection()
    {
        return collect($this->alumnos);
    }

    public function headings(): array
    {
        $actHeaders = array_map(function ($act) {
            $peso = $this->pesosIndividuales[$act] ?? 0;
            $tipo = ucfirst($this->tipos[strtolower(trim($act))] ?? 'tarea');
            return "{$act}\n({$tipo} {$peso}%)";
        }, $this->actividades);

        $wPart = $this->ponderaciones['participacion'] ?? 10;

        return array_merge(
            ['MATRÍCULA', 'NOMBRE DEL ALUMNO', 'CORREO'], // ── INCLUIDA LA COLUMNA DE MATRÍCULA
            $actHeaders,
            [
                "PARTICIPACIÓN ({$wPart}%)",
                'Promedio Real',
                'Final Acta',
            ]
        );
    }

    public function map($alumno): array
    {
        // ── MAPEO DE LA MATRÍCULA REAL COMO PRIMER ELEMENTO DE LA FILA ──
        $mapeo = [
            $alumno['matricula'], 
            mb_strtoupper($alumno['nombre']), // En mayúsculas para homologar con el acta oficial
            $alumno['email']
        ];

        foreach ($this->actividades as $act) {
            $mapeo[] = $alumno['notas_teams'][$act] ?? 0;
        }

        // Participación manual (columna fija)
        $mapeo[] = $alumno['manual']['participacion'] ?? 0;

        // ── Cálculo con pesos individuales por tipo ──
        $sumasPorTipo = [];
        $contPorTipo  = [];

        foreach ($this->actividades as $act) {
            $nota = (float)($alumno['notas_teams'][$act] ?? 0);
            $tipo = $this->tipos[strtolower(trim($act))] ?? 'tarea';
            $sumasPorTipo[$tipo] = ($sumasPorTipo[$tipo] ?? 0) + $nota;
            $contPorTipo[$tipo]  = ($contPorTipo[$tipo]  ?? 0) + 1;
        }

        $final = 0;

        // Participación
        $wPart = ($this->ponderaciones['participacion'] ?? 10) / 100;
        $final += (float)($alumno['manual']['participacion'] ?? 0) * $wPart;

        // Actividades por tipo
        foreach ($sumasPorTipo as $tipo => $suma) {
            $cont  = $contPorTipo[$tipo] ?? 1;
            $prom  = $suma / $cont;
            $peso  = ($this->ponderaciones[$tipo] ?? 10) / 100;
            $final += $prom * $peso;
        }

        $redondeado = ($final >= 5.5 && $final < 6.0) ? 5 : (int)round($final);

        $mapeo[] = round($final, 2);
        $mapeo[] = $redondeado;

        return $mapeo;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '002d62']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                $sheet->getRowDimension(1)->setRowHeight(35);

                // Como agregamos una columna al inicio, centramos desde la columna D (notas) en adelante
                $sheet->getStyle('D1:' . $highestCol . $highestRow)
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                      ->setVertical(Alignment::VERTICAL_CENTER);

                for ($row = 2; $row <= $highestRow; $row++) {
                    $val = $sheet->getCell($highestCol . $row)->getValue();
                    if (is_numeric($val) && $val < 6) {
                        $sheet->getStyle($highestCol . $row)->getFont()->getColor()->setRGB('FF0000');
                        $sheet->getStyle($highestCol . $row)->getFont()->setBold(true);
                    }
                }
            },
        ];
    }
}