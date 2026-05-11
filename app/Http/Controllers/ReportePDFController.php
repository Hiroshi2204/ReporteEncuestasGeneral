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
    // 📄 Lista todas las escuelas para mostrar en la vista lista_escuelas.blade
    public function listaEscuelas()
    {
        $escuelas = DB::table('matricula')
            ->select('COD_ESCUELA', 'NOM_ESCUELA')
            ->distinct()
            ->orderBy('COD_ESCUELA')
            ->get();

        return view('reportes.lista_escuelas', compact('escuelas'));
    }
    //-----------------------------------------

    public function reportePorEscuela1($codEscuela)
    {
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->value('NOM_ESCUELA');

        if (!$escuela) {
            abort(404, 'La escuela no existe en la base de datos.');
        }

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
                'a.nom_area',
                'p.nom_pre as pregunta',
                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('SUM(r.cod_alt = 5) as n5'),
                DB::raw('COUNT(r.cod_alt) as total_respuestas'),
                // DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4 + SUM(r.cod_alt = 5)*5) / NULLIF(COUNT(r.cod_alt), 0)) * 4, 2) as nota_item_20')
                DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4) / NULLIF(
                    SUM(r.cod_alt = 1) + 
                    SUM(r.cod_alt = 2) + 
                    SUM(r.cod_alt = 3) + 
                    SUM(r.cod_alt = 4), 0)
                ) * 5, 2) as nota_item_20')
                )
            ->where('m.COD_ESCUELA', $codEscuela)
            ->groupBy('m.COD_PRO', 'm.PROFESOR', 'm.DES_CURSO', 'm.COD_TURNO', 'a.nom_area', 'p.nom_pre')
            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.nom_area')
            ->orderBy('p.nom_pre')
            ->get();

        $agrupado = $datos
            ->groupBy('COD_PRO')
            ->map(function ($itemsPorDocente) {
                return [
                    'docente' => $itemsPorDocente->first()->docente,
                    'cursos' => $itemsPorDocente->groupBy('curso')
                        ->map(fn($turnos) => $turnos->groupBy('turno'))
                ];
            });

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
                'cursos' => $info['cursos']
            ]);
            $fileName = $folder . '/REPORTE_' . $docenteFile . '_' . $codPro . '_' . $codEscuela . '.pdf';
            Storage::put($fileName, $pdf->output());
        }

        return "✅ PDFs generados en storage/app/public/$folder";
    }

    public function reportePorEscuela2($codEscuela)
    {
        // 🔹 1. Obtener nombre de la escuela
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->value('NOM_ESCUELA');

        if (!$escuela) {
            abort(404, 'La escuela no existe en la base de datos.');
        }

        // 🔹 2. Consultar datos base
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
                'a.nom_area',
                'p.nom_pre as pregunta',
                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('SUM(r.cod_alt = 5) as n5'),
                DB::raw('COUNT(r.cod_alt) as total_respuestas'),
                // DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4 + SUM(r.cod_alt = 5)*5) / NULLIF(COUNT(r.cod_alt), 0)) * 4, 2) as nota_item_20')
                DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4) / NULLIF(
                    SUM(r.cod_alt = 1) + 
                    SUM(r.cod_alt = 2) + 
                    SUM(r.cod_alt = 3) + 
                    SUM(r.cod_alt = 4), 0)
                ) * 5, 2) as nota_item_20')
                )
            ->where('m.COD_ESCUELA', $codEscuela)
            ->groupBy('m.COD_PRO', 'm.PROFESOR', 'm.DES_CURSO', 'm.COD_TURNO', 'a.nom_area', 'p.nom_pre')
            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.nom_area')
            ->orderBy('p.nom_pre')
            ->get();

        // 🔹 3. Agrupar datos por docente, curso y turno
        $agrupado = $datos
            ->groupBy('COD_PRO')
            ->map(function ($itemsPorDocente) {
                $docente = $itemsPorDocente->first()->docente;

                // Agrupar por curso y turno
                $cursos = $itemsPorDocente->groupBy('curso')->map(function ($turnos) {
                    return $turnos->groupBy('turno')->map(function ($preguntas) {
                        // Agrupar por área
                        $preguntasPorArea = $preguntas->groupBy('nom_area');

                        $resumenAreas = [];
                        $totalNotas = 0;
                        $totalPreguntas = 0;
                        $totalEncuestados = $preguntas->first()->total_respuestas ?? 0;

                        foreach ($preguntasPorArea as $area => $items) {
                            $sumaNotas = collect($items)->sum('nota_item_20');
                            $promedioArea = round($sumaNotas / max(count($items), 1), 2);
                            $resumenAreas[$area] = [
                                'promedio' => $promedioArea,
                                'preguntas' => $items
                            ];

                            $totalNotas += $promedioArea * count($items);
                            $totalPreguntas += count($items);
                        }

                        // Promedio final del curso (a partir de las áreas)
                        $promedioFinal = $totalPreguntas > 0
                            ? round($totalNotas / $totalPreguntas, 2)
                            : 0;

                        return [
                            'areas' => $resumenAreas,
                            'totalEncuestados' => $totalEncuestados,
                            'promedioFinal' => $promedioFinal
                        ];
                    });
                });

                return [
                    'docente' => $docente,
                    'cursos' => $cursos
                ];
            });

        // 🔹 4. Generar PDFs individuales
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
                'cursos' => $info['cursos']
            ]);

            $fileName = $folder . '/REPORTE_' . $docenteFile . '_' . $codPro . '_' . $codEscuela . '.pdf';
            Storage::put($fileName, $pdf->output());
        }

        return "✅ PDFs generados en storage/app/public/$folder";
    }

    public function reporteGeneral3($codEscuela)
    {
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->value('NOM_ESCUELA');

        if (!$escuela) {
            abort(404, 'La escuela no existe en la base de datos.');
        }

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
                'a.nom_area',
                'p.nom_pre as pregunta',
                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('SUM(r.cod_alt = 5) as n5'),
                DB::raw('COUNT(r.cod_alt) as total_respuestas'),
                // DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4 + SUM(r.cod_alt = 5)*5) / NULLIF(COUNT(r.cod_alt), 0)) * 4, 2) as nota_item_20')
                DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4) / NULLIF(
                    SUM(r.cod_alt = 1) + 
                    SUM(r.cod_alt = 2) + 
                    SUM(r.cod_alt = 3) + 
                    SUM(r.cod_alt = 4), 0)
                ) * 5, 2) as nota_item_20')
                )
            ->where('m.COD_ESCUELA', $codEscuela)
            ->groupBy('m.COD_PRO', 'm.PROFESOR', 'm.DES_CURSO', 'm.COD_TURNO', 'a.nom_area', 'p.nom_pre')
            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.nom_area')
            ->orderBy('p.nom_pre')
            ->get();

        // 🔹 Agrupar por docente, curso y turno
        $agrupado = $datos
            ->groupBy('COD_PRO')
            ->map(function ($itemsPorDocente) {
                $docente = $itemsPorDocente->first()->docente;

                $cursos = $itemsPorDocente
                    ->groupBy('curso')
                    ->map(function ($itemsCurso) {
                        $turnos = $itemsCurso->groupBy('turno')->map(function ($itemsTurno) {
                            $preguntasPorArea = $itemsTurno->groupBy('nom_area');
                            $promediosAreas = [];
                            $totalPreguntas = 0;
                            $totalNotas = 0;
                            $encuestados = $itemsTurno->first()->total_respuestas ?? 0;

                            // ✅ Misma lógica que el reporte individual
                            foreach ($preguntasPorArea as $area => $preguntas) {
                                $sumaNotas = $preguntas->sum('nota_item_20');
                                $numPreguntas = $preguntas->count();
                                $promedioArea = $numPreguntas > 0 ? round($sumaNotas / $numPreguntas, 2) : 0;
                                $promediosAreas[$area] = $promedioArea;

                                // Ponderar correctamente
                                $totalPreguntas += $numPreguntas;
                                $totalNotas += $promedioArea * $numPreguntas;
                            }

                            // ✅ Promedio final ponderado (idéntico al reporte individual)
                            $promedioFinal = $totalPreguntas > 0 ? round($totalNotas / $totalPreguntas, 2) : 0;

                            return [
                                'turno' => $itemsTurno->first()->turno,
                                'encuestados' => $encuestados,
                                'promedios_areas' => $promediosAreas,
                                'promedio_final' => $promedioFinal
                            ];
                        });

                        return $turnos;
                    });

                return [
                    'docente' => $docente,
                    'cursos' => $cursos
                ];
            });

        // 🔹 Filtrar los que tienen 10 o más encuestados
        $masDiez = [];
        foreach ($agrupado as $docenteInfo) {
            foreach ($docenteInfo['cursos'] as $curso => $turnos) {
                foreach ($turnos as $turno => $infoTurno) {
                    if ($infoTurno['encuestados'] >= 10) {
                        $masDiez[] = [
                            'docente' => $docenteInfo['docente'],
                            'curso' => $curso,
                            'grupo_horario' => $infoTurno['turno'],
                            'encuestados' => $infoTurno['encuestados'],
                            'promedio_final' => $infoTurno['promedio_final']
                        ];
                    }
                }
            }
        }

        $masDiez = collect($masDiez)->sortByDesc('promedio_final')->values();

        // 🔹 Generar PDF
        $pdf = PDF::loadView('reportes.reporte_general', [
            'escuela' => $escuela,
            'masDiez' => $masDiez
        ])->setPaper('A4', 'portrait');

        $folderPath = storage_path('app/reporte_general');
        if (!file_exists($folderPath)) mkdir($folderPath, 0777, true);

        $fileName = "Orden_de_merito_{$escuela}.pdf";
        $pdf->save($folderPath . '/' . $fileName);

        return response()->json([
            'message' => '✅ Reporte generado correctamente',
            'file' => "storage/app/reporte_general/$fileName"
        ]);
    }

    public function reporteGeneral1($codEscuela)
    {
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->value('NOM_ESCUELA');

        if (!$escuela) {
            abort(404, 'La escuela no existe en la base de datos.');
        }

        // 🔹 Traemos todas las respuestas agrupadas por curso, área y pregunta
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
                'a.nom_area',
                'p.nom_pre as pregunta',
                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('SUM(r.cod_alt = 5) as n5'),
                DB::raw('COUNT(r.cod_alt) as total_respuestas'),
                // DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4 + SUM(r.cod_alt = 5)*5) / NULLIF(COUNT(r.cod_alt), 0)) * 4, 2) as nota_item_20')
                DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4) / NULLIF(
                    SUM(r.cod_alt = 1) + 
                    SUM(r.cod_alt = 2) + 
                    SUM(r.cod_alt = 3) + 
                    SUM(r.cod_alt = 4), 0)
                ) * 5, 2) as nota_item_20')
                )
            ->where('m.COD_ESCUELA', $codEscuela)
            ->groupBy('m.COD_PRO', 'm.PROFESOR', 'm.DES_CURSO', 'm.COD_TURNO', 'a.nom_area', 'p.nom_pre')
            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.nom_area')
            ->orderBy('p.nom_pre')
            ->get();

        // 🔹 Agrupamos por docente, curso y turno
        $agrupado = $datos->groupBy('COD_PRO')->map(function ($itemsPorDocente) {
            $docente = $itemsPorDocente->first()->docente;

            $cursos = $itemsPorDocente->groupBy('curso')->map(function ($itemsCurso) {
                return $itemsCurso->groupBy('turno')->map(function ($itemsTurno) {
                    $preguntasPorArea = $itemsTurno->groupBy('nom_area');
                    $promediosAreas = [];
                    $totalPreguntas = 0;
                    $totalNotas = 0;
                    $encuestados = $itemsTurno->first()->total_respuestas ?? 0;

                    // ✅ Mismo cálculo que en el reporte individual
                    foreach ($preguntasPorArea as $area => $preguntas) {
                        $sumaNotas = $preguntas->sum('nota_item_20');
                        $numPreguntas = $preguntas->count();
                        $promedioArea = $numPreguntas > 0 ? round($sumaNotas / $numPreguntas, 2) : 0;
                        $promediosAreas[$area] = $promedioArea;

                        $totalPreguntas += $numPreguntas;
                        $totalNotas += $promedioArea * $numPreguntas;
                    }

                    $promedioFinal = $totalPreguntas > 0 ? round($totalNotas / $totalPreguntas, 2) : 0;

                    return [
                        'turno' => $itemsTurno->first()->turno,
                        'encuestados' => $encuestados,
                        'promedios_areas' => $promediosAreas,
                        'promedio_final' => $promedioFinal,
                    ];
                });
            });

            return [
                'docente' => $docente,
                'cursos' => $cursos,
            ];
        });

        // 🔹 Construimos el ranking de docentes con 10 o más encuestados
        $ranking = [];
        foreach ($agrupado as $docenteInfo) {
            foreach ($docenteInfo['cursos'] as $curso => $turnos) {
                foreach ($turnos as $turno => $infoTurno) {
                    if ($infoTurno['encuestados'] >= 10) {
                        $ranking[] = [
                            'docente' => $docenteInfo['docente'],
                            'curso' => $curso,
                            'grupo_horario' => $infoTurno['turno'],
                            'encuestados' => $infoTurno['encuestados'],
                            'promedios_areas' => $infoTurno['promedios_areas'],
                            'promedio_final' => $infoTurno['promedio_final'],
                        ];
                    }
                }
            }
        }

        $ranking = collect($ranking)->sortByDesc('promedio_final')->values();

        // 🔹 Generar PDF con los datos procesados
        $pdf = PDF::loadView('reportes.reporte_general', [
            'escuela' => $escuela,
            'ranking' => $ranking
        ])->setPaper('A4', 'portrait');

        $folderPath = storage_path('app/reporte_general');
        if (!file_exists($folderPath)) mkdir($folderPath, 0777, true);

        $fileName = "Orden_de_merito_{$escuela}.pdf";
        $pdf->save($folderPath . '/' . $fileName);

        return response()->json([
            'message' => '✅ Reporte generado correctamente',
            'file' => "storage/app/reporte_general/$fileName"
        ]);
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


    public function reportePorEscuela($codEscuela)
    {
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->value('NOM_ESCUELA');

        if (!$escuela) abort(404, 'La escuela no existe en la base de datos.');

        $facultad = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
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
                'a.nom_area',
                'p.nom_pre as pregunta',
                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('SUM(r.cod_alt = 5) as n5'),
                DB::raw('COUNT(r.cod_alt) as total_respuestas'),
                // DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4 + SUM(r.cod_alt = 5)*5) / NULLIF(COUNT(r.cod_alt), 0)) * 4, 2) as nota_item_20')
                DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4) / NULLIF(
                    SUM(r.cod_alt = 1) + 
                    SUM(r.cod_alt = 2) + 
                    SUM(r.cod_alt = 3) + 
                    SUM(r.cod_alt = 4), 0)
                ) * 5, 2) as nota_item_20')
                )
            ->where('m.COD_ESCUELA', $codEscuela)
            ->groupBy('m.COD_PRO', 'm.PROFESOR', 'm.DES_CURSO', 'm.COD_TURNO', 'a.nom_area', 'p.nom_pre')
            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.nom_area')
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


    public function reporteGeneral($codEscuela)
    {
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->value('NOM_ESCUELA');

        if (!$escuela) abort(404, 'La escuela no existe en la base de datos.');

        $facultad = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
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
                'a.nom_area',
                'p.nom_pre as pregunta',
                DB::raw('SUM(r.cod_alt = 1) as n1'),
                DB::raw('SUM(r.cod_alt = 2) as n2'),
                DB::raw('SUM(r.cod_alt = 3) as n3'),
                DB::raw('SUM(r.cod_alt = 4) as n4'),
                DB::raw('SUM(r.cod_alt = 5) as n5'),
                DB::raw('COUNT(r.cod_alt) as total_respuestas'),
                // DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4 + SUM(r.cod_alt = 5)*5) / NULLIF(COUNT(r.cod_alt), 0)) * 4, 2) as nota_item_20')
                DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4) / NULLIF(
                    SUM(r.cod_alt = 1) + 
                    SUM(r.cod_alt = 2) + 
                    SUM(r.cod_alt = 3) + 
                    SUM(r.cod_alt = 4), 0)
                ) * 5, 2) as nota_item_20')
                )
            ->where('m.COD_ESCUELA', $codEscuela)
            ->groupBy('m.COD_PRO', 'm.PROFESOR', 'm.DES_CURSO', 'm.COD_TURNO', 'a.nom_area', 'p.nom_pre')
            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.nom_area')
            ->orderBy('p.nom_pre')
            ->get();

        // --- AGRUPACIÓN ---
        $agrupado = $datos->groupBy('COD_PRO')->map(function ($itemsDocente) {

            $docente = $itemsDocente->first()->docente;

            $cursos = $itemsDocente->groupBy('curso')->map(function ($itemsCurso) {

                return $itemsCurso->groupBy('turno')->map(function ($itemsTurno) {

                    $preguntasPorArea = $itemsTurno->groupBy('nom_area');
                    $encuestados = $itemsTurno->first()->total_respuestas ?? 0;

                    // --- USAR FUNCIÓN UNIFICADA ---
                    $resultado = $this->calcularPromedioCurso($preguntasPorArea, $encuestados);

                    return [
                        'turno' => $itemsTurno->first()->turno,
                        'encuestados' => $resultado['totalEncuestados'],
                        'areas' => $resultado['areas'],              // ← exactamente como en reportePorEscuela
                        'promedio_final' => $resultado['promedioFinal'], // ← EXACTAMENTE igual
                    ];
                });
            });

            return [
                'docente' => $docente,
                'cursos' => $cursos
            ];
        });

        // --- RANKING ---
        $ranking = [];

        foreach ($agrupado as $docenteInfo) {
            foreach ($docenteInfo['cursos'] as $curso => $turnos) {
                foreach ($turnos as $turno => $infoTurno) {

                    if ($infoTurno['encuestados'] >= 10) {
                        $ranking[] = [
                            'docente' => $docenteInfo['docente'],
                            'curso' => $curso,
                            'grupo_horario' => $infoTurno['turno'],
                            'encuestados' => $infoTurno['encuestados'],
                            'promedios_areas' => collect($infoTurno['areas'])->map(fn($a) => $a['promedio']),
                            'promedio_final' => $infoTurno['promedio_final'],
                        ];
                    }
                }
            }
        }

        $ranking = collect($ranking)->sortByDesc('promedio_final')->values();

        // --- PDF ---
        $pdf = PDF::loadView('reportes.reporte_general', [
            'escuela' => $escuela,
            'facultad' => $facultad,
            'ranking' => $ranking
        ])->setPaper('A4', 'portrait');

        $folderPath = storage_path('app/reporte_general');
        if (!file_exists($folderPath)) mkdir($folderPath, 0777, true);

        $fileName = "Orden_de_merito_{$escuela}.pdf";
        $pdf->save($folderPath . '/' . $fileName);

        return response()->json([
            'message' => '✅ Reporte generado correctamente',
            'file' => "storage/app/reporte_general/$fileName"
        ]);
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
