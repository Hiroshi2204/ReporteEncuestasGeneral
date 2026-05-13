<?php

namespace App\Http\Controllers;

use App\Models\EgresosAdicionales;
use App\Models\Producto;
use App\Models\RegistroEntradaDetalle;
use App\Models\RegistroSalidaDetalle;
use App\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReportePDFController extends Controller
{

    public function mostrarInicio()
    {
        return view('reportes.inicio');
    }
    // Lista todas las escuelas para mostrar en la vista
    public function listaEscuelas()
    {
        $escuelas = DB::table('matricula')
            ->select('COD_ESCUELA', 'NOM_ESCUELA')
            ->where('COD_CURSO', '=', "1234")
            ->distinct()
            ->orderBy('COD_ESCUELA')
            ->get();

        return view('reportes.estudiantes.lista_escuelas', compact('escuelas'));
    }

    public function listaEscuelasDocente()
    {
        $escuelas = DB::table('matricula')
            ->select('COD_ESCUELA', 'NOM_ESCUELA')
            ->where('COD_CURSO', '=', "12345")
            ->distinct()
            ->orderBy('COD_ESCUELA')
            ->get();

        return view('reportes.docentes.lista_escuelas', compact('escuelas'));
    }
    //-----------------------------------------

    public function reportePorEscuelademo($codEscuela)
    {
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'PREGRADO')
            ->value('NOM_ESCUELA');

        if (!$escuela) abort(404, 'La escuela no existe en la base de datos.');

        $facultad = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'PREGRADO')
            ->value('FACULTAD');
        if (!$facultad) $facultad = 'FACULTAD NO REGISTRADA';

        // --- DATOS BASE ---
        $datos = DB::table('enc_respuestas as r')
            ->join('matricula as m', function ($join) {
                $join->on('r.cod_alu', '=', 'm.COD_ALUMNO')
                    ->on('r.cod_cur', '=', 'm.COD_CURSO')
                    ->on('r.cod_pro', '=', 'm.COD_PRO')
                    ->on(DB::raw('TRIM(r.turno)'), '=', DB::raw('TRIM(m.COD_TURNO)'));
            })
            ->join('enc_pregunta_general as p', 'r.cod_pre', '=', 'p.cod_pre')
            ->join('enc_area_general as a', 'p.cod_area', '=', 'a.cod_area')
            ->select(
                'm.COD_PRO',
                'm.PROFESOR as docente',
                'm.DES_CURSO as curso',
                'm.COD_TURNO as turno',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre as pregunta',
                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('SUM(r.cod_alt = 5) as n5'),
                DB::raw('COUNT(r.cod_alt) as total_respuestas'),
                DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4) / NULLIF(
                    SUM(r.cod_alt = 1) + 
                    SUM(r.cod_alt = 2) + 
                    SUM(r.cod_alt = 3) + 
                    SUM(r.cod_alt = 4), 0)
                ) * 5, 2) as nota_item_20')
            )
            ->where('m.COD_ESCUELA', $codEscuela)
            ->where('r.tipo', 1) // Solo preguntas de tipo 1 (estudiantes)
            ->where('m.TIPO', 'PREGRADO')
            ->groupBy('m.COD_PRO', 'm.PROFESOR', 'm.DES_CURSO', 'm.COD_TURNO', 'a.cod_area', 'a.nom_area', 'p.nom_pre')
            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.cod_area')
            ->orderBy('p.nom_pre')
            ->get();

        // --- AGRUPACIÓN ---
        $agrupado = $datos
            ->groupBy('COD_PRO')
            ->map(function ($itemsPorDocente) {

                $docente = $itemsPorDocente->first()->docente;

                $cursos = $itemsPorDocente->groupBy('curso')->map(function ($itemsCurso) {

                    return $itemsCurso->groupBy('turno')->map(function ($itemsTurno) {

                        $preguntasPorArea = $itemsTurno->groupBy('nom_area');
                        $encuestados = $itemsTurno->first()->total_respuestas ?? 0;

                        return $this->calcularPromedioCurso($preguntasPorArea, $encuestados);
                    });
                });

                return [
                    'docente' => $docente,
                    'cursos' => $cursos
                ];
            });

        // --- GENERAR PDF POR DOCENTE ---
        $folder = 'reportes/' . $codEscuela;
        Storage::makeDirectory($folder);

        foreach ($agrupado as $codPro => $info) {

            $docente = $info['docente'];

            $docenteFile = strtoupper(str_replace(
                [' ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
                ['_', 'A', 'E', 'I', 'O', 'U', 'N'],
                $docente
            ));

            $pdf = Pdf::loadView('reportes.por_escuela_docente', [
                'escuela' => $escuela,
                'cod_escuela' => $codEscuela,
                'docente' => $docente,
                'facultad' => $facultad,
                'cursos' => $info['cursos']
            ]);

            $fileName = $folder . '/REPORTE_' . $docenteFile . '_' . $codPro . '_' . $codEscuela . '.pdf';
            Storage::put($fileName, $pdf->output());
        }

        return "✅ PDFs generados en storage/app/public/$folder";
    }


    /**
     * Calcula los promedios por área y el promedio final de un curso
     */
    private function calcularPromedioCurso($preguntasPorArea, $encuestados)
    {
        $resumenAreas = [];
        $totalNotas = 0;
        $totalPreguntas = 0;

        foreach ($preguntasPorArea as $area => $items) {

            $sumaNotas = collect($items)->sum('nota_item_20');
            $numPreguntas = count($items);

            $promedioArea = $numPreguntas > 0
                ? round($sumaNotas / $numPreguntas, 2)
                : 0;

            $resumenAreas[$area] = [
                'promedio' => $promedioArea,
                'preguntas' => $items
            ];

            $totalNotas += $promedioArea * $numPreguntas;
            $totalPreguntas += $numPreguntas;
        }

        $promedioFinal = $totalPreguntas > 0
            ? round($totalNotas / $totalPreguntas, 2)
            : 0;

        return [
            'areas' => $resumenAreas,
            'totalEncuestados' => $encuestados,
            'promedioFinal' => $promedioFinal
        ];
    }

    /**
     * Calcula los resultados a los estudiantes de cada escuela, sin importar el docente ni el curso
     */
    public function reportePorEscuela_PRIMERAVERSION($codEscuela)
    {
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'PREGRADO')
            ->value('NOM_ESCUELA');

        if (!$escuela) abort(404, 'La escuela no existe en la base de datos.');

        $facultad = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'PREGRADO')
            ->value('FACULTAD');

        if (!$facultad) $facultad = 'FACULTAD NO REGISTRADA';

        // ======================================================
        // DATOS BASE — SOLO PREGRADO, SOLO TIPO 1
        // ======================================================
        $datos = DB::table('enc_respuestas as r')
            ->join('matricula as m', function ($join) {
                $join->on('r.cod_alu', '=', 'm.COD_ALUMNO')
                    ->on('r.cod_cur', '=', 'm.COD_CURSO')
                    ->on('r.cod_pro', '=', 'm.COD_PRO')
                    ->on(DB::raw('TRIM(r.turno)'), '=', DB::raw('TRIM(m.COD_TURNO)'))
                    ->where('m.TIPO', 'PREGRADO');  // ← dentro del JOIN
            })
            ->join('enc_pregunta_general as p', 'r.cod_pre', '=', 'p.cod_pre')
            ->join('enc_area_general as a', 'p.cod_area', '=', 'a.cod_area')
            ->select(
                'm.COD_PRO',
                'm.PROFESOR as docente',
                'm.DES_CURSO as curso',
                'm.COD_TURNO as turno',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre as pregunta',
                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('SUM(r.cod_alt = 5) as n5'),
                DB::raw('COUNT(r.cod_alt) as total_respuestas'),
                DB::raw('ROUND(
                (
                    (
                        SUM(r.cod_alt = 1) * 1 +
                        SUM(r.cod_alt = 2) * 2 +
                        SUM(r.cod_alt = 3) * 3 +
                        SUM(r.cod_alt = 4) * 4
                    )
                    /
                    NULLIF(
                        SUM(r.cod_alt = 1) +
                        SUM(r.cod_alt = 2) +
                        SUM(r.cod_alt = 3) +
                        SUM(r.cod_alt = 4),
                    0)
                ) * 5
            , 2) as nota_item_20')
            )
            ->where('r.tipo', 1)            // ← solo respuestas de estudiantes
            ->where('m.TIPO', 'PREGRADO')   // ← doble seguro en WHERE
            ->where('m.COD_ESCUELA', $codEscuela)
            ->groupBy(
                'm.COD_PRO',
                'm.PROFESOR',
                'm.DES_CURSO',
                'm.COD_TURNO',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre'
            )
            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.cod_area')
            ->orderBy('p.nom_pre')
            ->get();

        // ======================================================
        // AGRUPACIÓN
        // ======================================================
        $agrupado = $datos
            ->groupBy('COD_PRO')
            ->map(function ($itemsPorDocente) {

                $docente = $itemsPorDocente->first()->docente;

                // $cursos = $itemsPorDocente->groupBy('curso')->map(function ($itemsCurso) {

                //     return $itemsCurso->groupBy('turno')->map(function ($itemsTurno) {

                //         $preguntasPorArea = $itemsTurno->groupBy('nom_area');
                //         $encuestados      = $itemsTurno->first()->total_respuestas ?? 0;

                //         return $this->calcularPromedioCurso($preguntasPorArea, $encuestados);
                //     });
                // });
                $cursos = $itemsPorDocente->groupBy('curso')->map(function ($itemsCurso) {

                    return $itemsCurso->groupBy('turno')->map(function ($itemsTurno) {

                        $preguntasPorArea = $itemsTurno->groupBy('nom_area');

                        $encuestados = $itemsTurno->first()->total_respuestas ?? 0;

                        // ======================================================
                        // CALCULAR DATOS
                        // ======================================================
                        $dataCurso = $this->calcularPromedioCurso(
                            $preguntasPorArea,
                            $encuestados
                        );

                        // ======================================================
                        // DATOS DEL GRÁFICO
                        // ======================================================
                        $labels = [];
                        $notas  = [];

                        foreach ($itemsTurno as $item) {

                            $labels[] =
                                mb_strimwidth($item->pregunta, 0, 55, '...');

                            $notas[] =
                                round($item->nota_item_20, 2);
                        }

                        // ======================================================
                        // ALTURA PEQUEÑA PARA QUE ENTRE EN UNA HOJA
                        // ======================================================
                        $cantidadPreguntas = count($labels);

                        $alturaChart = max(260, ($cantidadPreguntas * 22));

                        // ======================================================
                        // CONFIGURACIÓN QUICKCHART
                        // ======================================================
                        $chartConfig = [

                            'type' => 'horizontalBar',

                            'data' => [

                                'labels' => $labels,

                                'datasets' => [[

                                    'data' => $notas,

                                    'backgroundColor' => '#2563eb',

                                    'borderColor' => '#1e40af',

                                    'borderWidth' => 1,

                                    'barThickness' => 12,
                                ]]
                            ],

                            'options' => [

                                'responsive' => false,

                                'legend' => [
                                    'display' => false
                                ],

                                'layout' => [
                                    'padding' => [
                                        'right' => 25,
                                        'left'  => 5,
                                        'top'   => 5,
                                        'bottom' => 0
                                    ]
                                ],

                                'scales' => [

                                    'xAxes' => [[

                                        'ticks' => [
                                            'beginAtZero' => true,
                                            'max' => 20,
                                            'fontSize' => 9
                                        ],

                                        'gridLines' => [
                                            'color' => '#dbeafe'
                                        ]
                                    ]],

                                    'yAxes' => [[

                                        'ticks' => [
                                            'fontSize' => 8
                                        ],

                                        'gridLines' => [
                                            'display' => false
                                        ]
                                    ]]
                                ],

                                'plugins' => [

                                    'datalabels' => [

                                        'display' => true,

                                        'anchor' => 'end',

                                        'align' => 'right',

                                        'offset' => 2,

                                        'color' => '#111827',

                                        'font' => [
                                            'size' => 8,
                                            'weight' => 'bold'
                                        ],

                                        'formatter' =>
                                        'function(value){ return value.toFixed(1); }'
                                    ]
                                ]
                            ]
                        ];

                        // ======================================================
                        // GENERAR IMAGEN
                        // ======================================================
                        $url =
                            'https://quickchart.io/chart?width=700&height=' .
                            $alturaChart .
                            '&backgroundColor=white&format=png&c=' .
                            urlencode(json_encode($chartConfig));

                        try {

                            $imgData = file_get_contents($url);

                            $graficoBarras =
                                'data:image/png;base64,' .
                                base64_encode($imgData);
                        } catch (\Exception $e) {

                            $graficoBarras = null;
                        }

                        // ======================================================
                        // AGREGAR GRÁFICO AL ARRAY
                        // ======================================================
                        $dataCurso['graficoBarras'] = $graficoBarras;

                        return $dataCurso;
                    });
                });

                return [
                    'docente' => $docente,
                    'cursos'  => $cursos,
                ];
            });

        // ======================================================
        // GENERAR PDF POR DOCENTE
        // ======================================================
        $folder = 'reportes/alumno/' . $codEscuela;
        Storage::makeDirectory($folder);

        foreach ($agrupado as $codPro => $info) {

            $docente = $info['docente'];

            $docenteFile = strtoupper(str_replace(
                [' ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
                ['_', 'A', 'E', 'I', 'O', 'U', 'N'],
                $docente
            ));

            $pdf = Pdf::loadView('reportes.estudiantes.por_escuela_docente', [
                'escuela'     => $escuela,
                'cod_escuela' => $codEscuela,
                'docente'     => $docente,
                'facultad'    => $facultad,
                'cursos'      => $info['cursos'],
            ]);

            $fileName =
                $folder .
                '/REPORTE_' .
                $docenteFile .
                '_' .
                $codPro .
                '_' .
                $codEscuela .
                '.pdf';

            Storage::put($fileName, $pdf->output());
        }

        return "✅ PDFs generados en storage/app/public/$folder";
    }
    public function reportePorEscuela($codEscuela)
    {
        // ======================================================
        // ESCUELA Y FACULTAD — SOLO PREGRADO
        // ======================================================
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'PREGRADO')
            ->value('NOM_ESCUELA');

        if (!$escuela) abort(404, 'La escuela no existe en la base de datos.');

        $facultad = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'PREGRADO')
            ->value('FACULTAD') ?? 'FACULTAD NO REGISTRADA';

        // ======================================================
        // DATOS BASE — SOLO PREGRADO, SOLO TIPO 1
        // ======================================================
        $datos = DB::table('enc_respuestas as r')
            ->join('matricula as m', function ($join) {
                $join->on('r.cod_alu', '=', 'm.COD_ALUMNO')
                    ->on('r.cod_cur', '=', 'm.COD_CURSO')
                    ->on('r.cod_pro', '=', 'm.COD_PRO')
                    ->on(DB::raw('TRIM(r.turno)'), '=', DB::raw('TRIM(m.COD_TURNO)'))
                    ->where('m.TIPO', 'PREGRADO');
            })
            ->join('enc_pregunta_general as p', 'r.cod_pre', '=', 'p.cod_pre')
            ->join('enc_area_general as a', 'p.cod_area', '=', 'a.cod_area')
            ->select(
                'm.COD_PRO',
                'm.PROFESOR as docente',
                'm.DES_CURSO as curso',
                'm.COD_TURNO as turno',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre as pregunta',
                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('SUM(r.cod_alt = 5) as n5'),
                DB::raw('COUNT(r.cod_alt) as total_respuestas'),
                DB::raw('ROUND(
                (
                    (
                        SUM(r.cod_alt = 1) * 1 +
                        SUM(r.cod_alt = 2) * 2 +
                        SUM(r.cod_alt = 3) * 3 +
                        SUM(r.cod_alt = 4) * 4
                    )
                    /
                    NULLIF(
                        SUM(r.cod_alt = 1) +
                        SUM(r.cod_alt = 2) +
                        SUM(r.cod_alt = 3) +
                        SUM(r.cod_alt = 4),
                    0)
                ) * 5
            , 2) as nota_item_20')
            )
            ->where('r.tipo', 1)
            ->where('m.TIPO', 'PREGRADO')
            ->where('m.COD_ESCUELA', $codEscuela)
            ->groupBy(
                'm.COD_PRO',
                'm.PROFESOR',
                'm.DES_CURSO',
                'm.COD_TURNO',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre'
            )
            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.cod_area')
            ->orderBy('p.nom_pre')
            ->get();

        if ($datos->isEmpty()) {
            abort(404, 'No se encontraron datos para esta escuela.');
        }

        // ======================================================
        // DATOS DEL ÚNICO DOCENTE
        // ======================================================
        $docente = $datos->first()->docente;
        $codPro  = $datos->first()->COD_PRO;

        // ======================================================
        // AGRUPAR POR CURSO → TURNO (sin agrupar por docente)
        // ======================================================
        $cursos = $datos->groupBy('curso')->map(function ($itemsCurso) {

            return $itemsCurso->groupBy('turno')->map(function ($itemsTurno) {

                $preguntasPorArea = $itemsTurno->groupBy('nom_area');
                $encuestados      = $itemsTurno->first()->total_respuestas ?? 0;

                // ======================================================
                // CALCULAR PROMEDIOS
                // ======================================================
                $dataCurso = $this->calcularPromedioCurso($preguntasPorArea, $encuestados);

                // ======================================================
                // DATOS DEL GRÁFICO
                // ======================================================
                $labels = [];
                $notas  = [];

                foreach ($itemsTurno as $item) {
                    $labels[] = mb_strimwidth($item->pregunta, 0, 55, '...');
                    $notas[]  = round($item->nota_item_20, 2);
                }

                $alturaChart = max(260, (count($labels) * 22));

                $chartConfig = [
                    'type' => 'horizontalBar',
                    'data' => [
                        'labels'   => $labels,
                        'datasets' => [[
                            'data'            => $notas,
                            'backgroundColor' => '#2563eb',
                            'borderColor'     => '#1e40af',
                            'borderWidth'     => 1,
                            'barThickness'    => 12,
                        ]]
                    ],
                    'options' => [
                        'responsive' => false,
                        'legend'     => ['display' => false],
                        'layout'     => ['padding' => ['right' => 25, 'left' => 5, 'top' => 5, 'bottom' => 0]],
                        'scales' => [
                            'xAxes' => [[
                                'ticks'     => ['beginAtZero' => true, 'max' => 20, 'fontSize' => 9],
                                'gridLines' => ['color' => '#dbeafe'],
                            ]],
                            'yAxes' => [[
                                'ticks'     => ['fontSize' => 8],
                                'gridLines' => ['display' => false],
                            ]]
                        ],
                        'plugins' => [
                            'datalabels' => [
                                'display'   => true,
                                'anchor'    => 'end',
                                'align'     => 'right',
                                'offset'    => 2,
                                'color'     => '#111827',
                                'font'      => ['size' => 8, 'weight' => 'bold'],
                                'formatter' => 'function(value){ return value.toFixed(1); }',
                            ]
                        ]
                    ]
                ];

                // ======================================================
                // GENERAR IMAGEN BASE64
                // ======================================================
                $url =
                    'https://quickchart.io/chart?width=700&height=' .
                    $alturaChart .
                    '&backgroundColor=white&format=png&c=' .
                    urlencode(json_encode($chartConfig));

                try {
                    $dataCurso['graficoBarras'] = 'data:image/png;base64,' . base64_encode(file_get_contents($url));
                } catch (\Exception $e) {
                    $dataCurso['graficoBarras'] = null;
                }

                return $dataCurso;
            });
        });

        // ======================================================
        // NOMBRE DEL ARCHIVO
        // ======================================================
        $docenteFile = strtoupper(str_replace(
            [' ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
            ['_', 'A', 'E', 'I', 'O', 'U', 'N'],
            $docente
        ));

        $fileName = 'REPORTE_' . $docenteFile . '_' . $codPro . '_' . $codEscuela . '.pdf';

        // ======================================================
        // GENERAR Y DESCARGAR PDF DIRECTO
        // ======================================================
        return Pdf::loadView('reportes.estudiantes.por_escuela_docente', [
            'escuela'     => $escuela,
            'cod_escuela' => $codEscuela,
            'docente'     => $docente,
            'facultad'    => $facultad,
            'cursos'      => $cursos,
        ])->download($fileName);
    }

    public function reporteGraficoPorEscuela_PRIMERAVERSION($codEscuela)
    {
        // ======================================================
        // ESCUELA Y FACULTAD — SOLO PREGRADO
        // ======================================================
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'PREGRADO')
            ->value('NOM_ESCUELA');

        if (!$escuela) {
            abort(404, 'La escuela no existe en la base de datos.');
        }

        $facultad = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'PREGRADO')
            ->value('FACULTAD');

        if (!$facultad) {
            $facultad = 'FACULTAD NO REGISTRADA';
        }

        // ======================================================
        // DATOS BASE — MISMA LÓGICA QUE REPORTE TABLA
        // SOLO PREGRADO + SOLO ENCUESTA TIPO 1
        // ======================================================
        $datos = DB::table('enc_respuestas as r')
            ->join('matricula as m', function ($join) {
                $join->on('r.cod_alu', '=', 'm.COD_ALUMNO')
                    ->on('r.cod_cur', '=', 'm.COD_CURSO')
                    ->on('r.cod_pro', '=', 'm.COD_PRO')
                    ->on(DB::raw('TRIM(r.turno)'), '=', DB::raw('TRIM(m.COD_TURNO)'))
                    ->where('m.TIPO', 'PREGRADO');
            })
            ->join('enc_pregunta_general as p', 'r.cod_pre', '=', 'p.cod_pre')
            ->join('enc_area_general as a', 'p.cod_area', '=', 'a.cod_area')

            ->select(
                'm.COD_PRO',
                'm.PROFESOR as docente',
                'm.DES_CURSO as curso',
                'm.COD_TURNO as turno',

                'a.cod_area',
                'a.nom_area',

                'p.nom_pre as pregunta',

                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('SUM(r.cod_alt = 5) as n5'),

                DB::raw('COUNT(r.cod_alt) as total_respuestas'),

                // ======================================================
                // MISMA FÓRMULA EXACTA DEL REPORTE TABLA
                // ======================================================
                DB::raw('ROUND(
                (
                    (
                        SUM(r.cod_alt = 1) * 1 +
                        SUM(r.cod_alt = 2) * 2 +
                        SUM(r.cod_alt = 3) * 3 +
                        SUM(r.cod_alt = 4) * 4
                    )
                    /
                    NULLIF(
                        SUM(r.cod_alt = 1) +
                        SUM(r.cod_alt = 2) +
                        SUM(r.cod_alt = 3) +
                        SUM(r.cod_alt = 4),
                    0)
                ) * 5
            , 2) as nota_pregunta')
            )

            // ======================================================
            // FILTROS IMPORTANTES
            // ======================================================
            ->where('r.tipo', 1)
            ->where('m.TIPO', 'PREGRADO')
            ->where('m.COD_ESCUELA', $codEscuela)

            ->groupBy(
                'm.COD_PRO',
                'm.PROFESOR',
                'm.DES_CURSO',
                'm.COD_TURNO',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre'
            )

            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.cod_area')
            ->orderBy('p.nom_pre')

            ->get();

        // ======================================================
        // AGRUPAR POR DOCENTE
        // ======================================================
        $agrupado = $datos->groupBy('COD_PRO');

        // ======================================================
        // CREAR CARPETA
        // ======================================================
        $folder = 'reportes_graficos/alumno/' . $codEscuela;

        Storage::makeDirectory($folder);

        // ======================================================
        // GENERAR PDF POR DOCENTE
        // ======================================================
        foreach ($agrupado as $codPro => $items) {

            $docente = $items->first()->docente;
            $curso   = $items->first()->curso;
            $turno   = $items->first()->turno;

            // ======================================================
            // DATOS DEL GRÁFICO
            // ======================================================
            $labelsPreguntas    = [];
            $promediosPreguntas = [];

            foreach ($items as $item) {

                $labelsPreguntas[] =
                    mb_strimwidth($item->pregunta, 0, 75, '...');

                $promediosPreguntas[] =
                    (float) $item->nota_pregunta;
            }

            // ======================================================
            // PROMEDIO GENERAL
            // MISMA LÓGICA DEL REPORTE TABLA
            // ======================================================
            $totalPonderado  = 0;
            $totalRespuestas = 0;

            foreach ($items as $item) {

                $totalPonderado +=
                    ($item->n1 * 1) +
                    ($item->n2 * 2) +
                    ($item->n3 * 3) +
                    ($item->n4 * 4);

                $totalRespuestas +=
                    $item->n1 +
                    $item->n2 +
                    $item->n3 +
                    $item->n4;
            }

            $promedioGeneral = $totalRespuestas > 0
                ? round(($totalPonderado / $totalRespuestas) * 5, 2)
                : 0;

            // ======================================================
            // CONFIGURACIÓN GRÁFICO
            // ======================================================
            $cantPreguntas = count($labelsPreguntas);

            $alturaChart = max(450, ($cantPreguntas * 32) + 120);

            $chartConfigBar = [

                'type' => 'horizontalBar',

                'data' => [

                    'labels' => $labelsPreguntas,

                    'datasets' => [[
                        'label' => 'Nota Promedio',

                        'data' => $promediosPreguntas,

                        'backgroundColor' => '#1d4ed8',

                        'borderColor' => '#1e3a8a',

                        'borderWidth' => 1,

                        'barThickness' => 14,
                    ]]
                ],

                'options' => [

                    'responsive' => false,

                    'legend' => [
                        'display' => false
                    ],

                    'title' => [
                        'display' => false
                    ],

                    'layout' => [
                        'padding' => [
                            'left' => 15,
                            'right' => 50,
                            'top' => 15,
                            'bottom' => 15
                        ]
                    ],

                    'scales' => [

                        'xAxes' => [[

                            'ticks' => [
                                'beginAtZero' => true,
                                'max' => 20,
                                'fontSize' => 11
                            ],

                            'gridLines' => [
                                'color' => '#dbeafe'
                            ]
                        ]],

                        'yAxes' => [[

                            'ticks' => [
                                'fontSize' => 10
                            ],

                            'gridLines' => [
                                'display' => false
                            ]
                        ]]
                    ],

                    // ======================================================
                    // MOSTRAR NOTA EN CADA BARRA
                    // ======================================================
                    'plugins' => [

                        'datalabels' => [

                            'display' => true,

                            'color' => '#111827',

                            'anchor' => 'end',

                            'align' => 'right',

                            'offset' => 4,

                            'font' => [
                                'weight' => 'bold',
                                'size' => 10
                            ],

                            'formatter' => 'function(value){ return value.toFixed(2); }'
                        ]
                    ]
                ]
            ];

            // ======================================================
            // GENERAR IMAGEN BASE64
            // ======================================================
            $urlBarras =
                'https://quickchart.io/chart?width=1100&height=' .
                $alturaChart .
                '&backgroundColor=white&format=png&c=' .
                urlencode(json_encode($chartConfigBar));

            try {

                $imgData = file_get_contents($urlBarras);

                $graficoBarras =
                    'data:image/png;base64,' .
                    base64_encode($imgData);
            } catch (\Exception $e) {

                $graficoBarras = null;
            }

            // ======================================================
            // NOMBRE ARCHIVO
            // ======================================================
            $docenteFile = strtoupper(str_replace(
                [' ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
                ['_', 'A', 'E', 'I', 'O', 'U', 'N'],
                $docente
            ));

            // ======================================================
            // GENERAR PDF
            // ======================================================
            $pdf = Pdf::loadView('reportes.estudiantes.grafico_por_escuela', [

                'escuela'         => $escuela,
                'facultad'        => $facultad,

                'docente'         => $docente,

                'curso'           => $curso,

                'turno'           => $turno,

                'promedioGeneral' => $promedioGeneral,

                'graficoBarras'   => $graficoBarras,
            ]);

            // ======================================================
            // GUARDAR PDF
            // ======================================================
            $fileName =
                $folder .
                '/GRAFICO_' .
                $docenteFile .
                '_' .
                $codPro .
                '.pdf';

            Storage::put($fileName, $pdf->output());
        }

        return "✅ PDFs gráficos generados en storage/app/public/$folder";
    }

    public function reporteGraficoPorEscuela($codEscuela)
    {
        // ======================================================
        // ESCUELA Y FACULTAD — SOLO PREGRADO
        // ======================================================
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'PREGRADO')
            ->value('NOM_ESCUELA');

        if (!$escuela) {
            abort(404, 'La escuela no existe en la base de datos.');
        }

        $facultad = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'PREGRADO')
            ->value('FACULTAD') ?? 'FACULTAD NO REGISTRADA';

        // ======================================================
        // DATOS BASE
        // ======================================================
        $datos = DB::table('enc_respuestas as r')
            ->join('matricula as m', function ($join) {
                $join->on('r.cod_alu', '=', 'm.COD_ALUMNO')
                    ->on('r.cod_cur', '=', 'm.COD_CURSO')
                    ->on('r.cod_pro', '=', 'm.COD_PRO')
                    ->on(DB::raw('TRIM(r.turno)'), '=', DB::raw('TRIM(m.COD_TURNO)'))
                    ->where('m.TIPO', 'PREGRADO');
            })
            ->join('enc_pregunta_general as p', 'r.cod_pre', '=', 'p.cod_pre')
            ->join('enc_area_general as a', 'p.cod_area', '=', 'a.cod_area')
            ->select(
                'm.COD_PRO',
                'm.PROFESOR as docente',
                'm.DES_CURSO as curso',
                'm.COD_TURNO as turno',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre as pregunta',
                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('COUNT(r.cod_alt) as total_respuestas'),
                DB::raw('ROUND(
                (
                    (
                        SUM(r.cod_alt = 1) * 1 +
                        SUM(r.cod_alt = 2) * 2 +
                        SUM(r.cod_alt = 3) * 3 +
                        SUM(r.cod_alt = 4) * 4
                    )
                    /
                    NULLIF(
                        SUM(r.cod_alt = 1) +
                        SUM(r.cod_alt = 2) +
                        SUM(r.cod_alt = 3) +
                        SUM(r.cod_alt = 4),
                    0)
                ) * 5
            , 2) as nota_pregunta')
            )
            ->where('r.tipo', 1)
            ->where('m.TIPO', 'PREGRADO')
            ->where('m.COD_ESCUELA', $codEscuela)
            ->groupBy(
                'm.COD_PRO',
                'm.PROFESOR',
                'm.DES_CURSO',
                'm.COD_TURNO',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre'
            )
            ->orderBy('a.cod_area')
            ->orderBy('p.nom_pre')
            ->get();

        if ($datos->isEmpty()) {
            abort(404, 'No se encontraron datos para esta escuela.');
        }

        // ======================================================
        // DATOS DEL ÚNICO DOCENTE
        // ======================================================
        $docente = $datos->first()->docente;
        $codPro  = $datos->first()->COD_PRO;
        $curso   = $datos->first()->curso;
        $turno   = $datos->first()->turno;

        // ======================================================
        // DATOS DEL GRÁFICO
        // ======================================================
        $labelsPreguntas    = [];
        $promediosPreguntas = [];

        foreach ($datos as $item) {
            $labelsPreguntas[]    = mb_strimwidth($item->pregunta, 0, 75, '...');
            $promediosPreguntas[] = (float) $item->nota_pregunta;
        }

        // ======================================================
        // PROMEDIO GENERAL
        // ======================================================
        $totalPonderado  = 0;
        $totalRespuestas = 0;

        foreach ($datos as $item) {
            $totalPonderado  += ($item->n1 * 1) + ($item->n2 * 2) + ($item->n3 * 3) + ($item->n4 * 4);
            $totalRespuestas += $item->n1 + $item->n2 + $item->n3 + $item->n4;
        }

        $promedioGeneral = $totalRespuestas > 0
            ? round(($totalPonderado / $totalRespuestas) * 5, 2)
            : 0;

        // ======================================================
        // CONFIGURACIÓN GRÁFICO
        // ======================================================
        $alturaChart = max(450, (count($labelsPreguntas) * 32) + 120);

        $chartConfigBar = [
            'type' => 'horizontalBar',
            'data' => [
                'labels'   => $labelsPreguntas,
                'datasets' => [[
                    'label'           => 'Nota Promedio',
                    'data'            => $promediosPreguntas,
                    'backgroundColor' => '#1d4ed8',
                    'borderColor'     => '#1e3a8a',
                    'borderWidth'     => 1,
                    'barThickness'    => 14,
                ]]
            ],
            'options' => [
                'responsive' => false,
                'legend'     => ['display' => false],
                'title'      => ['display' => false],
                'layout'     => ['padding' => ['left' => 15, 'right' => 50, 'top' => 15, 'bottom' => 15]],
                'scales' => [
                    'xAxes' => [[
                        'ticks'     => ['beginAtZero' => true, 'max' => 20, 'fontSize' => 11],
                        'gridLines' => ['color' => '#dbeafe'],
                    ]],
                    'yAxes' => [[
                        'ticks'     => ['fontSize' => 10],
                        'gridLines' => ['display' => false],
                    ]]
                ],
                'plugins' => [
                    'datalabels' => [
                        'display'   => true,
                        'color'     => '#111827',
                        'anchor'    => 'end',
                        'align'     => 'right',
                        'offset'    => 4,
                        'font'      => ['weight' => 'bold', 'size' => 10],
                        'formatter' => 'function(value){ return value.toFixed(2); }',
                    ]
                ]
            ]
        ];

        // ======================================================
        // GENERAR IMAGEN BASE64
        // ======================================================
        $urlBarras =
            'https://quickchart.io/chart?width=1100&height=' .
            $alturaChart .
            '&backgroundColor=white&format=png&c=' .
            urlencode(json_encode($chartConfigBar));

        try {
            $graficoBarras = 'data:image/png;base64,' . base64_encode(file_get_contents($urlBarras));
        } catch (\Exception $e) {
            $graficoBarras = null;
        }

        // ======================================================
        // NOMBRE DEL ARCHIVO
        // ======================================================
        $docenteFile = strtoupper(str_replace(
            [' ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
            ['_', 'A', 'E', 'I', 'O', 'U', 'N'],
            $docente
        ));

        $fileName = 'GRAFICO_' . $docenteFile . '_' . $codPro . '.pdf';

        // ======================================================
        // GENERAR Y DESCARGAR PDF DIRECTO
        // ======================================================
        return Pdf::loadView('reportes.estudiantes.grafico_por_escuela', [
            'escuela'         => $escuela,
            'facultad'        => $facultad,
            'docente'         => $docente,
            'curso'           => $curso,
            'turno'           => $turno,
            'promedioGeneral' => $promedioGeneral,
            'graficoBarras'   => $graficoBarras,
        ])->download($fileName);
    }

    public function reporteGraficoGeneral()
    {
        // ======================================================
        // DATOS POR PREGUNTA — SOLO PREGRADO
        // ======================================================
        $datos = DB::table('enc_respuestas as r')
            ->join('matricula as m', function ($join) {
                $join->on('r.cod_alu', '=', 'm.COD_ALUMNO')
                    ->on('r.cod_cur', '=', 'm.COD_CURSO')
                    ->on('r.cod_pro', '=', 'm.COD_PRO')
                    ->on(DB::raw('TRIM(r.turno)'), '=', DB::raw('TRIM(m.COD_TURNO)'));
            })
            ->join('enc_pregunta_general as p', 'r.cod_pre', '=', 'p.cod_pre')
            ->join('enc_area_general as a', 'p.cod_area', '=', 'a.cod_area')
            ->where('m.TIPO', 'PREGRADO')
            ->select(
                'a.cod_area',
                'a.nom_area',
                'p.cod_pre',
                'p.nom_pre as pregunta',

                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),

                DB::raw('ROUND(
                (
                    (
                        SUM(r.cod_alt = 1) * 1 +
                        SUM(r.cod_alt = 2) * 2 +
                        SUM(r.cod_alt = 3) * 3 +
                        SUM(r.cod_alt = 4) * 4
                    )
                    /
                    NULLIF(
                        SUM(r.cod_alt = 1) +
                        SUM(r.cod_alt = 2) +
                        SUM(r.cod_alt = 3) +
                        SUM(r.cod_alt = 4),
                    0)
                ) * 5
            , 2) as nota_pregunta')
            )
            ->groupBy(
                'a.cod_area',
                'a.nom_area',
                'p.cod_pre',
                'p.nom_pre'
            )
            ->orderBy('a.cod_area')
            ->orderBy('p.cod_pre')
            ->get();

        if ($datos->isEmpty()) {
            abort(404, 'No hay respuestas de tipo PREGRADO registradas.');
        }

        // ======================================================
        // TOTAL ENCUESTADOS ÚNICOS
        // ======================================================
        $totalEncuestados = DB::table('matricula')
            ->where('TIPO', 'PREGRADO')
            ->where('ENCUESTADO', 1)
            ->count();

        // ======================================================
        // TOTALES GLOBALES
        // ======================================================
        $totalN1 = $datos->sum('n1');
        $totalN2 = $datos->sum('n2');
        $totalN3 = $datos->sum('n3');
        $totalN4 = $datos->sum('n4');

        $totalPonderado =
            ($totalN1 * 1) +
            ($totalN2 * 2) +
            ($totalN3 * 3) +
            ($totalN4 * 4);

        $totalRespuestas = $totalN1 + $totalN2 + $totalN3 + $totalN4;

        $promedioGeneral = $totalRespuestas > 0
            ? round(($totalPonderado / $totalRespuestas) * 5, 2)
            : 0;

        // ======================================================
        // AGRUPAR POR ÁREA PARA LA TABLA
        // ======================================================
        $areas = [];

        foreach ($datos->groupBy('nom_area') as $nomArea => $preguntas) {

            $ponderadoArea  = 0;
            $respuestasArea = 0;

            foreach ($preguntas as $p) {
                $ponderadoArea  += ($p->n1 * 1) + ($p->n2 * 2) + ($p->n3 * 3) + ($p->n4 * 4);
                $respuestasArea += $p->n1 + $p->n2 + $p->n3 + $p->n4;
            }

            $areas[$nomArea] = [
                'preguntas'      => $preguntas,
                'promedioArea'   => $respuestasArea > 0
                    ? round(($ponderadoArea / $respuestasArea) * 5, 2)
                    : 0,
            ];
        }

        // ======================================================
        // DATOS PARA EL GRÁFICO DE BARRAS
        // ======================================================
        $labelsPreguntas    = [];
        $promediosPreguntas = [];
        $coloresBarras      = [];

        $paleta = [
            '#1e3a8a',
            '#0f766e',
            '#7c3aed',
            '#b45309',
            '#0369a1',
            '#15803d',
            '#c2410c',
            '#6d28d9',
        ];

        $areaIndex  = 0;
        $areaActual = null;

        foreach ($datos as $item) {

            if ($item->nom_area !== $areaActual) {
                $areaActual = $item->nom_area;
                $areaIndex++;
            }

            $color = $paleta[($areaIndex - 1) % count($paleta)];

            $labelsPreguntas[]    = mb_strimwidth($item->pregunta, 0, 60, '...');
            $promediosPreguntas[] = (float) $item->nota_pregunta;
            $coloresBarras[]      = $color;
        }

        // ======================================================
        // CONFIGURACIÓN GRÁFICO DE BARRAS HORIZONTAL
        // ======================================================
        $cantPreguntas = count($labelsPreguntas);
        $alturaChart   = max(300, $cantPreguntas * 28 + 60);

        $chartConfigBar = [
            'type' => 'horizontalBar',

            'data' => [
                'labels'   => $labelsPreguntas,
                'datasets' => [[
                    'label'           => 'Nota Promedio',
                    'data'            => $promediosPreguntas,
                    'backgroundColor' => $coloresBarras,
                    'borderColor'     => '#0f172a',
                    'borderWidth'     => 1,
                    'barThickness'    => 12,
                ]]
            ],

            'options' => [
                'responsive' => false,
                'legend'     => ['display' => false],
                'title'      => ['display' => false],

                'layout' => [
                    'padding' => [
                        'right'  => 40,
                        'left'   => 10,
                        'top'    => 10,
                        'bottom' => 10,
                    ]
                ],

                'scales' => [
                    'xAxes' => [[
                        'ticks' => [
                            'beginAtZero' => true,
                            'max'         => 20,
                            'fontSize'    => 11,
                        ],
                        'gridLines' => ['color' => '#e2e8f0'],
                    ]],
                    'yAxes' => [[
                        'ticks' => ['fontSize' => 9],
                    ]],
                ],

                'plugins' => [
                    'datalabels' => [
                        'color'  => '#0f172a',
                        'anchor' => 'end',
                        'align'  => 'right',
                        'font'   => ['weight' => 'bold', 'size' => 9],
                    ]
                ]
            ]
        ];

        // ======================================================
        // CONVERTIR A BASE64 PARA DOMPDF
        // ======================================================
        $urlBarras =
            'https://quickchart.io/chart?width=800&height=' . $alturaChart .
            '&backgroundColor=white&format=png&c=' .
            urlencode(json_encode($chartConfigBar));

        try {
            $imgData       = file_get_contents($urlBarras);
            $graficoBarras = 'data:image/png;base64,' . base64_encode($imgData);
        } catch (\Exception $e) {
            $graficoBarras = null;
        }

        // ======================================================
        // GENERAR Y DESCARGAR PDF
        // ======================================================
        $pdf = Pdf::loadView('reportes.estudiantes.grafico_general', [
            'promedioGeneral'  => $promedioGeneral,
            'totalRespuestas'  => $totalRespuestas,
            'totalEncuestados' => $totalEncuestados,
            'areas'            => $areas,
            'graficoBarras'    => $graficoBarras,
        ]);

        return $pdf->download('REPORTE_GENERAL_ALUMNOS.pdf');
    }

    /**
     * Calcula los resultados a los docentes de cada escuela, sin importar el curso
     */
    public function reportePorEscuelaDocente_PRIMERAVERSION($codEscuela)
    {
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'ESPECIALIDAD')
            ->value('NOM_ESCUELA');

        if (!$escuela) abort(404, 'La escuela no existe en la base de datos.');

        $facultad = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'ESPECIALIDAD')
            ->value('FACULTAD');

        if (!$facultad) $facultad = 'FACULTAD NO REGISTRADA';

        // ======================================================
        // DATOS BASE — SOLO ESPECIALIDAD, SOLO TIPO 2
        // ======================================================
        $datos = DB::table('enc_respuestas as r')
            ->join('matricula as m', function ($join) {
                $join->on('r.cod_alu', '=', 'm.COD_ALUMNO')
                    ->on('r.cod_cur', '=', 'm.COD_CURSO')
                    ->on('r.cod_pro', '=', 'm.COD_PRO')
                    ->on(DB::raw('TRIM(r.turno)'), '=', DB::raw('TRIM(m.COD_TURNO)'))
                    ->where('m.TIPO', 'ESPECIALIDAD');  // ← dentro del JOIN
            })
            ->join('enc_pregunta_general as p', 'r.cod_pre', '=', 'p.cod_pre')
            ->join('enc_area_general as a', 'p.cod_area', '=', 'a.cod_area')
            ->select(
                'm.COD_PRO',
                'm.PROFESOR as docente',
                'm.DES_CURSO as curso',
                'm.COD_TURNO as turno',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre as pregunta',
                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('SUM(r.cod_alt = 5) as n5'),
                DB::raw('COUNT(r.cod_alt) as total_respuestas'),
                DB::raw('ROUND(
                (
                    (
                        SUM(r.cod_alt = 1) * 1 +
                        SUM(r.cod_alt = 2) * 2 +
                        SUM(r.cod_alt = 3) * 3 +
                        SUM(r.cod_alt = 4) * 4
                    )
                    /
                    NULLIF(
                        SUM(r.cod_alt = 1) +
                        SUM(r.cod_alt = 2) +
                        SUM(r.cod_alt = 3) +
                        SUM(r.cod_alt = 4),
                    0)
                ) * 5
            , 2) as nota_item_20')
            )
            ->where('r.tipo', 3)            // ← solo respuestas de DOCENTES
            ->where('m.TIPO', 'ESPECIALIDAD')   // ← doble seguro en WHERE
            ->where('m.COD_ESCUELA', $codEscuela)
            ->groupBy(
                'm.COD_PRO',
                'm.PROFESOR',
                'm.DES_CURSO',
                'm.COD_TURNO',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre'
            )
            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.cod_area')
            ->orderBy('p.nom_pre')
            ->get();

        // ======================================================
        // AGRUPACIÓN
        // ======================================================
        $agrupado = $datos
            ->groupBy('COD_PRO')
            ->map(function ($itemsPorDocente) {

                $docente = $itemsPorDocente->first()->docente;

                // $cursos = $itemsPorDocente->groupBy('curso')->map(function ($itemsCurso) {

                //     return $itemsCurso->groupBy('turno')->map(function ($itemsTurno) {

                //         $preguntasPorArea = $itemsTurno->groupBy('nom_area');
                //         $encuestados      = $itemsTurno->first()->total_respuestas ?? 0;

                //         return $this->calcularPromedioCurso($preguntasPorArea, $encuestados);
                //     });
                // });
                $cursos = $itemsPorDocente->groupBy('curso')->map(function ($itemsCurso) {

                    return $itemsCurso->groupBy('turno')->map(function ($itemsTurno) {

                        $preguntasPorArea = $itemsTurno->groupBy('nom_area');

                        $encuestados = $itemsTurno->first()->total_respuestas ?? 0;

                        // ======================================================
                        // CALCULAR DATOS
                        // ======================================================
                        $dataCurso = $this->calcularPromedioCurso(
                            $preguntasPorArea,
                            $encuestados
                        );

                        // ======================================================
                        // DATOS DEL GRÁFICO
                        // ======================================================
                        $labels = [];
                        $notas  = [];

                        foreach ($itemsTurno as $item) {

                            $labels[] =
                                mb_strimwidth($item->pregunta, 0, 55, '...');

                            $notas[] =
                                round($item->nota_item_20, 2);
                        }

                        // ======================================================
                        // ALTURA PEQUEÑA PARA QUE ENTRE EN UNA HOJA
                        // ======================================================
                        $cantidadPreguntas = count($labels);

                        $alturaChart = max(260, ($cantidadPreguntas * 22));

                        // ======================================================
                        // CONFIGURACIÓN QUICKCHART
                        // ======================================================
                        $chartConfig = [

                            'type' => 'horizontalBar',

                            'data' => [

                                'labels' => $labels,

                                'datasets' => [[

                                    'data' => $notas,

                                    'backgroundColor' => '#2563eb',

                                    'borderColor' => '#1e40af',

                                    'borderWidth' => 1,

                                    'barThickness' => 12,
                                ]]
                            ],

                            'options' => [

                                'responsive' => false,

                                'legend' => [
                                    'display' => false
                                ],

                                'layout' => [
                                    'padding' => [
                                        'right' => 25,
                                        'left'  => 5,
                                        'top'   => 5,
                                        'bottom' => 0
                                    ]
                                ],

                                'scales' => [

                                    'xAxes' => [[

                                        'ticks' => [
                                            'beginAtZero' => true,
                                            'max' => 20,
                                            'fontSize' => 9
                                        ],

                                        'gridLines' => [
                                            'color' => '#dbeafe'
                                        ]
                                    ]],

                                    'yAxes' => [[

                                        'ticks' => [
                                            'fontSize' => 8
                                        ],

                                        'gridLines' => [
                                            'display' => false
                                        ]
                                    ]]
                                ],

                                'plugins' => [

                                    'datalabels' => [

                                        'display' => true,

                                        'anchor' => 'end',

                                        'align' => 'right',

                                        'offset' => 2,

                                        'color' => '#111827',

                                        'font' => [
                                            'size' => 8,
                                            'weight' => 'bold'
                                        ],

                                        'formatter' =>
                                        'function(value){ return value.toFixed(1); }'
                                    ]
                                ]
                            ]
                        ];

                        // ======================================================
                        // GENERAR IMAGEN
                        // ======================================================
                        $url =
                            'https://quickchart.io/chart?width=700&height=' .
                            $alturaChart .
                            '&backgroundColor=white&format=png&c=' .
                            urlencode(json_encode($chartConfig));

                        try {

                            $imgData = file_get_contents($url);

                            $graficoBarras =
                                'data:image/png;base64,' .
                                base64_encode($imgData);
                        } catch (\Exception $e) {

                            $graficoBarras = null;
                        }

                        // ======================================================
                        // AGREGAR GRÁFICO AL ARRAY
                        // ======================================================
                        $dataCurso['graficoBarras'] = $graficoBarras;

                        return $dataCurso;
                    });
                });

                return [
                    'docente' => $docente,
                    'cursos'  => $cursos,
                ];
            });

        // ======================================================
        // GENERAR PDF POR DOCENTE
        // ======================================================
        $folder = 'reportes/docentes/' . $codEscuela;
        Storage::makeDirectory($folder);

        foreach ($agrupado as $codPro => $info) {

            $docente = $info['docente'];

            $docenteFile = strtoupper(str_replace(
                [' ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
                ['_', 'A', 'E', 'I', 'O', 'U', 'N'],
                $docente
            ));

            $pdf = Pdf::loadView('reportes.docentes.por_escuela_docente', [
                'escuela'     => $escuela,
                'cod_escuela' => $codEscuela,
                'docente'     => $docente,
                'facultad'    => $facultad,
                'cursos'      => $info['cursos'],
            ]);

            $fileName =
                $folder .
                '/REPORTE_' .
                $docenteFile .
                '_' .
                $codPro .
                '_' .
                $codEscuela .
                '.pdf';

            Storage::put($fileName, $pdf->output());
        }

        return "✅ PDFs generados en storage/app/public/$folder";
    }

    public function reportePorEscuelaDocente($codEscuela)
    {
        // ======================================================
        // ESCUELA Y FACULTAD — SOLO ESPECIALIDAD
        // ======================================================
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'ESPECIALIDAD')
            ->value('NOM_ESCUELA');

        if (!$escuela) abort(404, 'La escuela no existe en la base de datos.');

        $facultad = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'ESPECIALIDAD')
            ->value('FACULTAD') ?? 'FACULTAD NO REGISTRADA';

        // ======================================================
        // DATOS BASE — SOLO ESPECIALIDAD, SOLO TIPO 3
        // ======================================================
        $datos = DB::table('enc_respuestas as r')
            ->join('matricula as m', function ($join) {
                $join->on('r.cod_alu', '=', 'm.COD_ALUMNO')
                    ->on('r.cod_cur', '=', 'm.COD_CURSO')
                    ->on('r.cod_pro', '=', 'm.COD_PRO')
                    ->on(DB::raw('TRIM(r.turno)'), '=', DB::raw('TRIM(m.COD_TURNO)'))
                    ->where('m.TIPO', 'ESPECIALIDAD');
            })
            ->join('enc_pregunta_general as p', 'r.cod_pre', '=', 'p.cod_pre')
            ->join('enc_area_general as a', 'p.cod_area', '=', 'a.cod_area')
            ->select(
                'm.COD_PRO',
                'm.PROFESOR as docente',
                'm.DES_CURSO as curso',
                'm.COD_TURNO as turno',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre as pregunta',
                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('SUM(r.cod_alt = 5) as n5'),
                DB::raw('COUNT(r.cod_alt) as total_respuestas'),
                DB::raw('ROUND(
                (
                    (
                        SUM(r.cod_alt = 1) * 1 +
                        SUM(r.cod_alt = 2) * 2 +
                        SUM(r.cod_alt = 3) * 3 +
                        SUM(r.cod_alt = 4) * 4
                    )
                    /
                    NULLIF(
                        SUM(r.cod_alt = 1) +
                        SUM(r.cod_alt = 2) +
                        SUM(r.cod_alt = 3) +
                        SUM(r.cod_alt = 4),
                    0)
                ) * 5
            , 2) as nota_item_20')
            )
            ->where('r.tipo', 3)
            ->where('m.TIPO', 'ESPECIALIDAD')
            ->where('m.COD_ESCUELA', $codEscuela)
            ->groupBy(
                'm.COD_PRO',
                'm.PROFESOR',
                'm.DES_CURSO',
                'm.COD_TURNO',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre'
            )
            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.cod_area')
            ->orderBy('p.nom_pre')
            ->get();

        if ($datos->isEmpty()) {
            abort(404, 'No se encontraron datos para esta escuela.');
        }

        // ======================================================
        // DATOS DEL ÚNICO DOCENTE
        // ======================================================
        $docente = $datos->first()->docente;
        $codPro  = $datos->first()->COD_PRO;

        // ======================================================
        // AGRUPAR POR CURSO → TURNO (sin nivel de docente)
        // ======================================================
        $cursos = $datos->groupBy('curso')->map(function ($itemsCurso) {

            return $itemsCurso->groupBy('turno')->map(function ($itemsTurno) {

                $preguntasPorArea = $itemsTurno->groupBy('nom_area');
                $encuestados      = $itemsTurno->first()->total_respuestas ?? 0;

                // ======================================================
                // CALCULAR PROMEDIOS
                // ======================================================
                $dataCurso = $this->calcularPromedioCurso($preguntasPorArea, $encuestados);

                // ======================================================
                // DATOS DEL GRÁFICO
                // ======================================================
                $labels = [];
                $notas  = [];

                foreach ($itemsTurno as $item) {
                    $labels[] = mb_strimwidth($item->pregunta, 0, 55, '...');
                    $notas[]  = round($item->nota_item_20, 2);
                }

                $alturaChart = max(260, (count($labels) * 22));

                $chartConfig = [
                    'type' => 'horizontalBar',
                    'data' => [
                        'labels'   => $labels,
                        'datasets' => [[
                            'data'            => $notas,
                            'backgroundColor' => '#2563eb',
                            'borderColor'     => '#1e40af',
                            'borderWidth'     => 1,
                            'barThickness'    => 12,
                        ]]
                    ],
                    'options' => [
                        'responsive' => false,
                        'legend'     => ['display' => false],
                        'layout'     => ['padding' => ['right' => 25, 'left' => 5, 'top' => 5, 'bottom' => 0]],
                        'scales' => [
                            'xAxes' => [[
                                'ticks'     => ['beginAtZero' => true, 'max' => 20, 'fontSize' => 9],
                                'gridLines' => ['color' => '#dbeafe'],
                            ]],
                            'yAxes' => [[
                                'ticks'     => ['fontSize' => 8],
                                'gridLines' => ['display' => false],
                            ]]
                        ],
                        'plugins' => [
                            'datalabels' => [
                                'display'   => true,
                                'anchor'    => 'end',
                                'align'     => 'right',
                                'offset'    => 2,
                                'color'     => '#111827',
                                'font'      => ['size' => 8, 'weight' => 'bold'],
                                'formatter' => 'function(value){ return value.toFixed(1); }',
                            ]
                        ]
                    ]
                ];

                // ======================================================
                // GENERAR IMAGEN BASE64
                // ======================================================
                $url =
                    'https://quickchart.io/chart?width=700&height=' .
                    $alturaChart .
                    '&backgroundColor=white&format=png&c=' .
                    urlencode(json_encode($chartConfig));

                try {
                    $dataCurso['graficoBarras'] = 'data:image/png;base64,' . base64_encode(file_get_contents($url));
                } catch (\Exception $e) {
                    $dataCurso['graficoBarras'] = null;
                }

                return $dataCurso;
            });
        });

        // ======================================================
        // NOMBRE DEL ARCHIVO
        // ======================================================
        $docenteFile = strtoupper(str_replace(
            [' ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
            ['_', 'A', 'E', 'I', 'O', 'U', 'N'],
            $docente
        ));

        $fileName = 'REPORTE_' . $docenteFile . '_' . $codPro . '_' . $codEscuela . '.pdf';

        // ======================================================
        // GENERAR Y DESCARGAR PDF DIRECTO
        // ======================================================
        return Pdf::loadView('reportes.docentes.por_escuela_docente', [
            'escuela'     => $escuela,
            'cod_escuela' => $codEscuela,
            'docente'     => $docente,
            'facultad'    => $facultad,
            'cursos'      => $cursos,
        ])->download($fileName);
    }

    public function reporteGraficoPorEscuelaDocente_PRIMERAVERSION($codEscuela)
    {
        // ======================================================
        // ESCUELA Y FACULTAD — SOLO PREGRADO
        // ======================================================
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'PREGRADO')
            ->value('NOM_ESCUELA');

        if (!$escuela) {
            abort(404, 'La escuela no existe en la base de datos.');
        }

        $facultad = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'PREGRADO')
            ->value('FACULTAD');

        if (!$facultad) {
            $facultad = 'FACULTAD NO REGISTRADA';
        }

        // ======================================================
        // DATOS BASE — MISMA LÓGICA QUE REPORTE TABLA
        // SOLO PREGRADO + SOLO ENCUESTA TIPO 1
        // ======================================================
        $datos = DB::table('enc_respuestas as r')
            ->join('matricula as m', function ($join) {
                $join->on('r.cod_alu', '=', 'm.COD_ALUMNO')
                    ->on('r.cod_cur', '=', 'm.COD_CURSO')
                    ->on('r.cod_pro', '=', 'm.COD_PRO')
                    ->on(DB::raw('TRIM(r.turno)'), '=', DB::raw('TRIM(m.COD_TURNO)'))
                    ->where('m.TIPO', 'PREGRADO');
            })
            ->join('enc_pregunta_general as p', 'r.cod_pre', '=', 'p.cod_pre')
            ->join('enc_area_general as a', 'p.cod_area', '=', 'a.cod_area')

            ->select(
                'm.COD_PRO',
                'm.PROFESOR as docente',
                'm.DES_CURSO as curso',
                'm.COD_TURNO as turno',

                'a.cod_area',
                'a.nom_area',

                'p.nom_pre as pregunta',

                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('SUM(r.cod_alt = 5) as n5'),

                DB::raw('COUNT(r.cod_alt) as total_respuestas'),

                // ======================================================
                // MISMA FÓRMULA EXACTA DEL REPORTE TABLA
                // ======================================================
                DB::raw('ROUND(
                (
                    (
                        SUM(r.cod_alt = 1) * 1 +
                        SUM(r.cod_alt = 2) * 2 +
                        SUM(r.cod_alt = 3) * 3 +
                        SUM(r.cod_alt = 4) * 4
                    )
                    /
                    NULLIF(
                        SUM(r.cod_alt = 1) +
                        SUM(r.cod_alt = 2) +
                        SUM(r.cod_alt = 3) +
                        SUM(r.cod_alt = 4),
                    0)
                ) * 5
            , 2) as nota_pregunta')
            )

            // ======================================================
            // FILTROS IMPORTANTES
            // ======================================================
            ->where('r.tipo', 1)
            ->where('m.TIPO', 'PREGRADO')
            ->where('m.COD_ESCUELA', $codEscuela)

            ->groupBy(
                'm.COD_PRO',
                'm.PROFESOR',
                'm.DES_CURSO',
                'm.COD_TURNO',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre'
            )

            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.cod_area')
            ->orderBy('p.nom_pre')

            ->get();

        // ======================================================
        // AGRUPAR POR DOCENTE
        // ======================================================
        $agrupado = $datos->groupBy('COD_PRO');

        // ======================================================
        // CREAR CARPETA
        // ======================================================
        $folder = 'reportes_graficos/docentes/' . $codEscuela;

        Storage::makeDirectory($folder);

        // ======================================================
        // GENERAR PDF POR DOCENTE
        // ======================================================
        foreach ($agrupado as $codPro => $items) {

            $docente = $items->first()->docente;
            $curso   = $items->first()->curso;
            $turno   = $items->first()->turno;

            // ======================================================
            // DATOS DEL GRÁFICO
            // ======================================================
            $labelsPreguntas    = [];
            $promediosPreguntas = [];

            foreach ($items as $item) {

                $labelsPreguntas[] =
                    mb_strimwidth($item->pregunta, 0, 75, '...');

                $promediosPreguntas[] =
                    (float) $item->nota_pregunta;
            }

            // ======================================================
            // PROMEDIO GENERAL
            // MISMA LÓGICA DEL REPORTE TABLA
            // ======================================================
            $totalPonderado  = 0;
            $totalRespuestas = 0;

            foreach ($items as $item) {

                $totalPonderado +=
                    ($item->n1 * 1) +
                    ($item->n2 * 2) +
                    ($item->n3 * 3) +
                    ($item->n4 * 4);

                $totalRespuestas +=
                    $item->n1 +
                    $item->n2 +
                    $item->n3 +
                    $item->n4;
            }

            $promedioGeneral = $totalRespuestas > 0
                ? round(($totalPonderado / $totalRespuestas) * 5, 2)
                : 0;

            // ======================================================
            // CONFIGURACIÓN GRÁFICO
            // ======================================================
            $cantPreguntas = count($labelsPreguntas);

            $alturaChart = max(450, ($cantPreguntas * 32) + 120);

            $chartConfigBar = [

                'type' => 'horizontalBar',

                'data' => [

                    'labels' => $labelsPreguntas,

                    'datasets' => [[
                        'label' => 'Nota Promedio',

                        'data' => $promediosPreguntas,

                        'backgroundColor' => '#1d4ed8',

                        'borderColor' => '#1e3a8a',

                        'borderWidth' => 1,

                        'barThickness' => 14,
                    ]]
                ],

                'options' => [

                    'responsive' => false,

                    'legend' => [
                        'display' => false
                    ],

                    'title' => [
                        'display' => false
                    ],

                    'layout' => [
                        'padding' => [
                            'left' => 15,
                            'right' => 50,
                            'top' => 15,
                            'bottom' => 15
                        ]
                    ],

                    'scales' => [

                        'xAxes' => [[

                            'ticks' => [
                                'beginAtZero' => true,
                                'max' => 20,
                                'fontSize' => 11
                            ],

                            'gridLines' => [
                                'color' => '#dbeafe'
                            ]
                        ]],

                        'yAxes' => [[

                            'ticks' => [
                                'fontSize' => 10
                            ],

                            'gridLines' => [
                                'display' => false
                            ]
                        ]]
                    ],

                    // ======================================================
                    // MOSTRAR NOTA EN CADA BARRA
                    // ======================================================
                    'plugins' => [

                        'datalabels' => [

                            'display' => true,

                            'color' => '#111827',

                            'anchor' => 'end',

                            'align' => 'right',

                            'offset' => 4,

                            'font' => [
                                'weight' => 'bold',
                                'size' => 10
                            ],

                            'formatter' => 'function(value){ return value.toFixed(2); }'
                        ]
                    ]
                ]
            ];

            // ======================================================
            // GENERAR IMAGEN BASE64
            // ======================================================
            $urlBarras =
                'https://quickchart.io/chart?width=1100&height=' .
                $alturaChart .
                '&backgroundColor=white&format=png&c=' .
                urlencode(json_encode($chartConfigBar));

            try {

                $imgData = file_get_contents($urlBarras);

                $graficoBarras =
                    'data:image/png;base64,' .
                    base64_encode($imgData);
            } catch (\Exception $e) {

                $graficoBarras = null;
            }

            // ======================================================
            // NOMBRE ARCHIVO
            // ======================================================
            $docenteFile = strtoupper(str_replace(
                [' ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
                ['_', 'A', 'E', 'I', 'O', 'U', 'N'],
                $docente
            ));

            // ======================================================
            // GENERAR PDF
            // ======================================================
            $pdf = Pdf::loadView('reportes.docentes.grafico_por_escuela', [

                'escuela'         => $escuela,
                'facultad'        => $facultad,

                'docente'         => $docente,

                'curso'           => $curso,

                'turno'           => $turno,

                'promedioGeneral' => $promedioGeneral,

                'graficoBarras'   => $graficoBarras,
            ]);

            // ======================================================
            // GUARDAR PDF
            // ======================================================
            $fileName =
                $folder .
                '/GRAFICO_' .
                $docenteFile .
                '_' .
                $codPro .
                '.pdf';

            Storage::put($fileName, $pdf->output());
        }

        return "✅ PDFs gráficos generados en storage/app/public/$folder";
    }

    public function reporteGraficoPorEscuelaDocente($codEscuela)
    {
        // ======================================================
        // ESCUELA Y FACULTAD — SOLO PREGRADO
        // ======================================================
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'PREGRADO')
            ->value('NOM_ESCUELA');

        if (!$escuela) {
            abort(404, 'La escuela no existe en la base de datos.');
        }

        $facultad = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->where('TIPO', 'PREGRADO')
            ->value('FACULTAD') ?? 'FACULTAD NO REGISTRADA';

        // ======================================================
        // DATOS BASE — SOLO PREGRADO, SOLO TIPO 1
        // ======================================================
        $datos = DB::table('enc_respuestas as r')
            ->join('matricula as m', function ($join) {
                $join->on('r.cod_alu', '=', 'm.COD_ALUMNO')
                    ->on('r.cod_cur', '=', 'm.COD_CURSO')
                    ->on('r.cod_pro', '=', 'm.COD_PRO')
                    ->on(DB::raw('TRIM(r.turno)'), '=', DB::raw('TRIM(m.COD_TURNO)'))
                    ->where('m.TIPO', 'PREGRADO');
            })
            ->join('enc_pregunta_general as p', 'r.cod_pre', '=', 'p.cod_pre')
            ->join('enc_area_general as a', 'p.cod_area', '=', 'a.cod_area')
            ->select(
                'm.COD_PRO',
                'm.PROFESOR as docente',
                'm.DES_CURSO as curso',
                'm.COD_TURNO as turno',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre as pregunta',
                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('COUNT(r.cod_alt) as total_respuestas'),
                DB::raw('ROUND(
                (
                    (
                        SUM(r.cod_alt = 1) * 1 +
                        SUM(r.cod_alt = 2) * 2 +
                        SUM(r.cod_alt = 3) * 3 +
                        SUM(r.cod_alt = 4) * 4
                    )
                    /
                    NULLIF(
                        SUM(r.cod_alt = 1) +
                        SUM(r.cod_alt = 2) +
                        SUM(r.cod_alt = 3) +
                        SUM(r.cod_alt = 4),
                    0)
                ) * 5
            , 2) as nota_pregunta')
            )
            ->where('r.tipo', 1)
            ->where('m.TIPO', 'PREGRADO')
            ->where('m.COD_ESCUELA', $codEscuela)
            ->groupBy(
                'm.COD_PRO',
                'm.PROFESOR',
                'm.DES_CURSO',
                'm.COD_TURNO',
                'a.cod_area',
                'a.nom_area',
                'p.nom_pre'
            )
            ->orderBy('a.cod_area')
            ->orderBy('p.nom_pre')
            ->get();

        if ($datos->isEmpty()) {
            abort(404, 'No se encontraron datos para esta escuela.');
        }

        // ======================================================
        // DATOS DEL ÚNICO DOCENTE
        // ======================================================
        $docente = $datos->first()->docente;
        $codPro  = $datos->first()->COD_PRO;
        $curso   = $datos->first()->curso;
        $turno   = $datos->first()->turno;

        // ======================================================
        // DATOS DEL GRÁFICO
        // ======================================================
        $labelsPreguntas    = [];
        $promediosPreguntas = [];

        foreach ($datos as $item) {
            $labelsPreguntas[]    = mb_strimwidth($item->pregunta, 0, 75, '...');
            $promediosPreguntas[] = (float) $item->nota_pregunta;
        }

        // ======================================================
        // PROMEDIO GENERAL
        // ======================================================
        $totalPonderado  = 0;
        $totalRespuestas = 0;

        foreach ($datos as $item) {
            $totalPonderado  += ($item->n1 * 1) + ($item->n2 * 2) + ($item->n3 * 3) + ($item->n4 * 4);
            $totalRespuestas += $item->n1 + $item->n2 + $item->n3 + $item->n4;
        }

        $promedioGeneral = $totalRespuestas > 0
            ? round(($totalPonderado / $totalRespuestas) * 5, 2)
            : 0;

        // ======================================================
        // CONFIGURACIÓN GRÁFICO
        // ======================================================
        $alturaChart = max(450, (count($labelsPreguntas) * 32) + 120);

        $chartConfigBar = [
            'type' => 'horizontalBar',
            'data' => [
                'labels'   => $labelsPreguntas,
                'datasets' => [[
                    'label'           => 'Nota Promedio',
                    'data'            => $promediosPreguntas,
                    'backgroundColor' => '#1d4ed8',
                    'borderColor'     => '#1e3a8a',
                    'borderWidth'     => 1,
                    'barThickness'    => 14,
                ]]
            ],
            'options' => [
                'responsive' => false,
                'legend'     => ['display' => false],
                'title'      => ['display' => false],
                'layout'     => ['padding' => ['left' => 15, 'right' => 50, 'top' => 15, 'bottom' => 15]],
                'scales' => [
                    'xAxes' => [[
                        'ticks'     => ['beginAtZero' => true, 'max' => 20, 'fontSize' => 11],
                        'gridLines' => ['color' => '#dbeafe'],
                    ]],
                    'yAxes' => [[
                        'ticks'     => ['fontSize' => 10],
                        'gridLines' => ['display' => false],
                    ]]
                ],
                'plugins' => [
                    'datalabels' => [
                        'display'   => true,
                        'color'     => '#111827',
                        'anchor'    => 'end',
                        'align'     => 'right',
                        'offset'    => 4,
                        'font'      => ['weight' => 'bold', 'size' => 10],
                        'formatter' => 'function(value){ return value.toFixed(2); }',
                    ]
                ]
            ]
        ];

        // ======================================================
        // GENERAR IMAGEN BASE64
        // ======================================================
        $urlBarras =
            'https://quickchart.io/chart?width=1100&height=' .
            $alturaChart .
            '&backgroundColor=white&format=png&c=' .
            urlencode(json_encode($chartConfigBar));

        try {
            $graficoBarras = 'data:image/png;base64,' . base64_encode(file_get_contents($urlBarras));
        } catch (\Exception $e) {
            $graficoBarras = null;
        }

        // ======================================================
        // NOMBRE DEL ARCHIVO
        // ======================================================
        $docenteFile = strtoupper(str_replace(
            [' ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
            ['_', 'A', 'E', 'I', 'O', 'U', 'N'],
            $docente
        ));

        $fileName = 'GRAFICO_' . $docenteFile . '_' . $codPro . '.pdf';

        // ======================================================
        // GENERAR Y DESCARGAR PDF DIRECTO
        // ======================================================
        return Pdf::loadView('reportes.docentes.grafico_por_escuela', [
            'escuela'         => $escuela,
            'facultad'        => $facultad,
            'docente'         => $docente,
            'curso'           => $curso,
            'turno'           => $turno,
            'promedioGeneral' => $promedioGeneral,
            'graficoBarras'   => $graficoBarras,
        ])->download($fileName);
    }

    public function reporteGraficoGeneralDocente()
    {
        // ======================================================
        // DATOS POR PREGUNTA — SOLO ESPECIALIDAD
        // ======================================================
        $datos = DB::table('enc_respuestas as r')
            ->join('matricula as m', function ($join) {
                $join->on('r.cod_alu', '=', 'm.COD_ALUMNO')
                    ->on('r.cod_cur', '=', 'm.COD_CURSO')
                    ->on('r.cod_pro', '=', 'm.COD_PRO')
                    ->on(DB::raw('TRIM(r.turno)'), '=', DB::raw('TRIM(m.COD_TURNO)'));
            })
            ->join('enc_pregunta_general as p', 'r.cod_pre', '=', 'p.cod_pre')
            ->join('enc_area_general as a', 'p.cod_area', '=', 'a.cod_area')
            ->where('m.TIPO', 'ESPECIALIDAD')
            ->select(
                'a.cod_area',
                'a.nom_area',
                'p.cod_pre',
                'p.nom_pre as pregunta',

                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),

                DB::raw('ROUND(
                (
                    (
                        SUM(r.cod_alt = 1) * 1 +
                        SUM(r.cod_alt = 2) * 2 +
                        SUM(r.cod_alt = 3) * 3 +
                        SUM(r.cod_alt = 4) * 4
                    )
                    /
                    NULLIF(
                        SUM(r.cod_alt = 1) +
                        SUM(r.cod_alt = 2) +
                        SUM(r.cod_alt = 3) +
                        SUM(r.cod_alt = 4),
                    0)
                ) * 5
            , 2) as nota_pregunta')
            )
            ->groupBy(
                'a.cod_area',
                'a.nom_area',
                'p.cod_pre',
                'p.nom_pre'
            )
            ->orderBy('a.cod_area')
            ->orderBy('p.cod_pre')
            ->get();

        if ($datos->isEmpty()) {
            abort(404, 'No hay respuestas de tipo ESPECIALIDAD registradas.');
        }

        // ======================================================
        // TOTAL ENCUESTADOS ÚNICOS
        // ======================================================
        $totalEncuestados = DB::table('matricula')
            ->where('TIPO', 'ESPECIALIDAD')
            ->where('ENCUESTADO', 1)
            ->count();

        // ======================================================
        // TOTALES GLOBALES
        // ======================================================
        $totalN1 = $datos->sum('n1');
        $totalN2 = $datos->sum('n2');
        $totalN3 = $datos->sum('n3');
        $totalN4 = $datos->sum('n4');

        $totalPonderado =
            ($totalN1 * 1) +
            ($totalN2 * 2) +
            ($totalN3 * 3) +
            ($totalN4 * 4);

        $totalRespuestas = $totalN1 + $totalN2 + $totalN3 + $totalN4;

        $promedioGeneral = $totalRespuestas > 0
            ? round(($totalPonderado / $totalRespuestas) * 5, 2)
            : 0;

        // ======================================================
        // AGRUPAR POR ÁREA PARA LA TABLA
        // ======================================================
        $areas = [];

        foreach ($datos->groupBy('nom_area') as $nomArea => $preguntas) {

            $ponderadoArea  = 0;
            $respuestasArea = 0;

            foreach ($preguntas as $p) {
                $ponderadoArea  += ($p->n1 * 1) + ($p->n2 * 2) + ($p->n3 * 3) + ($p->n4 * 4);
                $respuestasArea += $p->n1 + $p->n2 + $p->n3 + $p->n4;
            }

            $areas[$nomArea] = [
                'preguntas'      => $preguntas,
                'promedioArea'   => $respuestasArea > 0
                    ? round(($ponderadoArea / $respuestasArea) * 5, 2)
                    : 0,
            ];
        }

        // ======================================================
        // DATOS PARA EL GRÁFICO DE BARRAS
        // ======================================================
        $labelsPreguntas    = [];
        $promediosPreguntas = [];
        $coloresBarras      = [];

        $paleta = [
            '#1e3a8a',
            '#0f766e',
            '#7c3aed',
            '#b45309',
            '#0369a1',
            '#15803d',
            '#c2410c',
            '#6d28d9',
        ];

        $areaIndex  = 0;
        $areaActual = null;

        foreach ($datos as $item) {

            if ($item->nom_area !== $areaActual) {
                $areaActual = $item->nom_area;
                $areaIndex++;
            }

            $color = $paleta[($areaIndex - 1) % count($paleta)];

            $labelsPreguntas[]    = mb_strimwidth($item->pregunta, 0, 60, '...');
            $promediosPreguntas[] = (float) $item->nota_pregunta;
            $coloresBarras[]      = $color;
        }

        // ======================================================
        // CONFIGURACIÓN GRÁFICO DE BARRAS HORIZONTAL
        // ======================================================
        $cantPreguntas = count($labelsPreguntas);
        $alturaChart   = max(300, $cantPreguntas * 28 + 60);

        $chartConfigBar = [
            'type' => 'horizontalBar',

            'data' => [
                'labels'   => $labelsPreguntas,
                'datasets' => [[
                    'label'           => 'Nota Promedio',
                    'data'            => $promediosPreguntas,
                    'backgroundColor' => $coloresBarras,
                    'borderColor'     => '#0f172a',
                    'borderWidth'     => 1,
                    'barThickness'    => 12,
                ]]
            ],

            'options' => [
                'responsive' => false,
                'legend'     => ['display' => false],
                'title'      => ['display' => false],

                'layout' => [
                    'padding' => [
                        'right'  => 40,
                        'left'   => 10,
                        'top'    => 10,
                        'bottom' => 10,
                    ]
                ],

                'scales' => [
                    'xAxes' => [[
                        'ticks' => [
                            'beginAtZero' => true,
                            'max'         => 20,
                            'fontSize'    => 11,
                        ],
                        'gridLines' => ['color' => '#e2e8f0'],
                    ]],
                    'yAxes' => [[
                        'ticks' => ['fontSize' => 9],
                    ]],
                ],

                'plugins' => [
                    'datalabels' => [
                        'color'  => '#0f172a',
                        'anchor' => 'end',
                        'align'  => 'right',
                        'font'   => ['weight' => 'bold', 'size' => 9],
                    ]
                ]
            ]
        ];

        // ======================================================
        // CONVERTIR A BASE64 PARA DOMPDF
        // ======================================================
        $urlBarras =
            'https://quickchart.io/chart?width=800&height=' . $alturaChart .
            '&backgroundColor=white&format=png&c=' .
            urlencode(json_encode($chartConfigBar));

        try {
            $imgData       = file_get_contents($urlBarras);
            $graficoBarras = 'data:image/png;base64,' . base64_encode($imgData);
        } catch (\Exception $e) {
            $graficoBarras = null;
        }

        // ======================================================
        // GENERAR Y DESCARGAR PDF
        // ======================================================
        $pdf = Pdf::loadView('reportes.docentes.grafico_general', [
            'promedioGeneral'  => $promedioGeneral,
            'totalRespuestas'  => $totalRespuestas,
            'totalEncuestados' => $totalEncuestados,
            'areas'            => $areas,
            'graficoBarras'    => $graficoBarras,
        ]);

        return $pdf->download('REPORTE_GENERAL_DOCENTES.pdf');
    }
























    // public function generarPorEscuelas()
    // {
    //     ini_set('memory_limit', '1024M');
    //     set_time_limit(600); // hasta 10 minutos para evitar timeout

    //     // Obtiene todas las escuelas
    //     $escuelas = DB::table('matricula')
    //         ->select('COD_ESCUELA', 'NOM_ESCUELA')
    //         ->distinct()
    //         ->get();

    //     foreach ($escuelas as $escuela) {

    //         // Aquí debes traer los datos filtrados por escuela
    //         $agrupado = $this->obtenerDatosPorEscuela($escuela->COD_ESCUELA);

    //         // Genera el PDF para esa escuela
    //         $pdf = Pdf::loadView('reportes.escuela', [
    //             'escuela' => $escuela->NOM_ESCUELA,
    //             'cod_escuela' => $escuela->COD_ESCUELA,
    //             'agrupado' => $agrupado
    //         ])->setPaper('a4', 'portrait');

    //         // Guarda cada PDF individualmente
    //         $nombreArchivo = "reportes/escuela_{$escuela->COD_ESCUELA}.pdf";
    //         Storage::put($nombreArchivo, $pdf->output());
    //     }

    //     return response()->json([
    //         'message' => 'PDFs generados correctamente por escuela y guardados en storage/app/reportes'
    //     ]);
    // }

    // private function obtenerDatosPorEscuela($codigoEscuela)
    // {
    //     $resultados = DB::table('enc_respuestas as r')
    //         ->join('matricula as m', 'r.cod_alu', '=', 'm.COD_ALUMNO')
    //         ->join('enc_pregunta as p', 'r.cod_pre', '=', 'p.cod_pre')
    //         ->join('enc_area as a', 'p.cod_area', '=', 'a.cod_area')
    //         ->select(
    //             'm.PROFESOR as docente',
    //             'm.DES_CURSO as curso',
    //             'm.COD_TURNO as turno',
    //             'a.nom_area',
    //             'p.nom_pre as pregunta',
    //             DB::raw('SUM(r.cod_alt = 1) as n1'),
    //             DB::raw('SUM(r.cod_alt = 2) as n2'),
    //             DB::raw('SUM(r.cod_alt = 3) as n3'),
    //             DB::raw('SUM(r.cod_alt = 4) as n4'),
    //             DB::raw('SUM(r.cod_alt = 5) as n5'),
    //             DB::raw('COUNT(r.cod_alt) as total_respuestas'),
    //             DB::raw('ROUND((SUM(r.cod_alt) / COUNT(r.cod_alt)) * 4, 2) as nota_item_20')
    //         )
    //         ->where('m.COD_ESCUELA', $codigoEscuela)
    //         ->groupBy('m.PROFESOR', 'm.DES_CURSO', 'm.COD_TURNO', 'a.nom_area', 'p.nom_pre')
    //         ->orderBy('m.PROFESOR')
    //         ->orderBy('m.DES_CURSO')
    //         ->orderBy('m.COD_TURNO')
    //         ->orderBy('a.nom_area')
    //         ->orderBy('p.nom_pre')
    //         ->get();

    //     // 🔄 Agrupamos datos
    //     $agrupado = [];
    //     foreach ($resultados as $fila) {
    //         $docente = $fila->docente;
    //         $curso   = $fila->curso;
    //         $turno   = $fila->turno;

    //         if (!isset($agrupado[$docente])) {
    //             $agrupado[$docente] = [];
    //         }
    //         if (!isset($agrupado[$docente][$curso])) {
    //             $agrupado[$docente][$curso] = [];
    //         }
    //         if (!isset($agrupado[$docente][$curso][$turno])) {
    //             $agrupado[$docente][$curso][$turno] = [];
    //         }

    //         $agrupado[$docente][$curso][$turno][] = (object) [
    //             'nom_area' => $fila->nom_area,
    //             'pregunta' => $fila->pregunta,
    //             'n1' => $fila->n1,
    //             'n2' => $fila->n2,
    //             'n3' => $fila->n3,
    //             'n4' => $fila->n4,
    //             'n5' => $fila->n5,
    //             'total_respuestas' => $fila->total_respuestas,
    //             'nota_item_20' => $fila->nota_item_20
    //         ];
    //     }

    //     return $agrupado;
    // }




    public function index()
    {
        $escuelas = DB::table('matricula')
            ->select('NOM_ESCUELA')
            ->distinct()
            ->orderBy('NOM_ESCUELA')
            ->get();

        return view('encuestas.escuelas', compact('escuelas'));
    }

    public function reporteEscuela($nombre)
    {
        $alumnos = DB::table('matricula')
            ->where('NOM_ESCUELA', $nombre)
            ->select('COD_ALUMNO', 'NOM_ALUMNO')
            ->groupBy('COD_ALUMNO', 'NOM_ALUMNO')
            ->get();

        $encuestados = [];
        $noEncuestados = [];

        foreach ($alumnos as $alumno) {
            $cursos = DB::table('matricula')
                ->where('COD_ALUMNO', $alumno->COD_ALUMNO)
                ->get();

            $respondidas = $cursos->where('ENCUESTADO', 1)->count();

            if ($respondidas > 0) {
                $encuestados[] = $alumno;
            } else {
                $noEncuestados[] = $alumno;
            }
        }

        return view('encuestas.reporte_por_escuela', compact('nombre', 'encuestados', 'noEncuestados'));
    }

    public function reportePDF($nombre)
    {
        $alumnos = DB::table('matricula')
            ->where('NOM_ESCUELA', $nombre)
            ->select('COD_ALUMNO', 'NOM_ALUMNO')
            ->groupBy('COD_ALUMNO', 'NOM_ALUMNO')
            ->get();

        $encuestados = [];
        $noEncuestados = [];

        foreach ($alumnos as $alumno) {
            $cursos = DB::table('matricula')
                ->where('COD_ALUMNO', $alumno->COD_ALUMNO)
                ->get();

            $respondidas = $cursos->where('ENCUESTADO', 1)->count();

            if ($respondidas > 0) {
                $encuestados[] = $alumno;
            } else {
                $noEncuestados[] = $alumno;
            }
        }

        $pdf = Pdf::loadView('encuestas.reporte_pdf', compact('nombre', 'encuestados', 'noEncuestados'))
            ->setPaper('A4', 'portrait');

        return $pdf->download("Reporte_{$nombre}.pdf");
    }
}
