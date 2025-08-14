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


    public function reportePorEscuela($codEscuela)
    {
        // 1️⃣ Obtener nombre de la escuela
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->value('NOM_ESCUELA');

        if (!$escuela) {
            abort(404, 'La escuela no existe en la base de datos.');
        }

        // 2️⃣ Traer datos completos (incluyendo COD_PRO)
        $datos = DB::table('enc_respuestas as r')
            ->join('matricula as m', function ($join) {
                $join->on('r.cod_alu', '=', 'm.COD_ALUMNO')
                    ->on('r.cod_cur', '=', 'm.COD_CURSO')
                    ->on('r.cod_pro', '=', 'm.COD_PRO')
                    ->on(DB::raw('TRIM(r.turno)'), '=', DB::raw('TRIM(m.COD_TURNO)'));
            })
            ->join('enc_pregunta as p', 'r.cod_pre', '=', 'p.cod_pre')
            ->join('enc_area as a', 'p.cod_area', '=', 'a.cod_area')
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
                DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4 + SUM(r.cod_alt = 5)*5) / NULLIF(COUNT(r.cod_alt), 0)) * 4, 2) as nota_item_20')
            )
            ->where('m.COD_ESCUELA', $codEscuela)
            ->groupBy('m.COD_PRO', 'm.PROFESOR', 'm.DES_CURSO', 'm.COD_TURNO', 'a.nom_area', 'p.nom_pre')
            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.nom_area')
            ->orderBy('p.nom_pre')
            ->get();

        // 3️⃣ Agrupar datos por COD_PRO
        $agrupado = $datos
            ->groupBy('COD_PRO')
            ->map(function ($itemsPorDocente) {
                return [
                    'docente' => $itemsPorDocente->first()->docente,
                    'cursos' => $itemsPorDocente->groupBy('curso')
                        ->map(fn($turnos) => $turnos->groupBy('turno'))
                ];
            });

        // 4️⃣ Carpeta donde guardaremos PDFs (en storage/app/public)
        $folder = 'reportes/' . $codEscuela;
        Storage::makeDirectory($folder);

        // 5️⃣ Generar PDF por docente con su nombre en el archivo
        foreach ($agrupado as $codPro => $info) {
            $docente = $info['docente'];

            // ✅ Limpiamos el nombre del docente para que sea seguro en el nombre del archivo
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

            // ✅ Nombre del archivo: REPORTE_{NOMBRE_DOCENTE}_{CODPRO}_{COD_ESC}.pdf
            $fileName = $folder . '/REPORTE_' . $docenteFile . '_' . $codPro . '_' . $codEscuela . '.pdf';
            Storage::put($fileName, $pdf->output());
        }

        return "✅ PDFs generados en storage/app/public/$folder";
    }



    public function reportePorEscuela2($codEscuela)
    {
        // 1️⃣ Obtener nombre de la escuela
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->value('NOM_ESCUELA');

        if (!$escuela) {
            abort(404, 'La escuela no existe en la base de datos.');
        }

        // 2️⃣ Traer datos completos
        $datos = DB::table('enc_respuestas as r')
            ->join('matricula as m', function ($join) {
                $join->on('r.cod_alu', '=', 'm.COD_ALUMNO')
                    ->on('r.cod_cur', '=', 'm.COD_CURSO')
                    ->on('r.cod_pro', '=', 'm.COD_PRO')
                    ->on(DB::raw('TRIM(r.turno)'), '=', DB::raw('TRIM(m.COD_TURNO)'));
            })
            ->join('enc_pregunta as p', 'r.cod_pre', '=', 'p.cod_pre')
            ->join('enc_area as a', 'p.cod_area', '=', 'a.cod_area')
            ->select(
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
                DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4 + SUM(r.cod_alt = 5)*5) / NULLIF(COUNT(r.cod_alt), 0)) * 4, 2) as nota_item_20')
            )
            ->where('m.COD_ESCUELA', $codEscuela)
            ->groupBy('m.PROFESOR', 'm.DES_CURSO', 'm.COD_TURNO', 'a.nom_area', 'p.nom_pre')
            ->orderBy('m.PROFESOR')
            ->orderBy('m.DES_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('a.nom_area')
            ->orderBy('p.nom_pre')
            ->get();

        // 3️⃣ Agrupar datos por docente → curso → turno
        $agrupado = $datos
            ->groupBy('docente')
            ->map(fn($cursos) => $cursos->groupBy('curso')
                ->map(fn($turnos) => $turnos->groupBy('turno')));

        // 4️⃣ Carpeta donde guardaremos PDFs (en storage/app/public)
        $baseFolder = 'reportes/' . $codEscuela;
        Storage::makeDirectory($baseFolder . '/mayores10Encuestados');
        Storage::makeDirectory($baseFolder . '/menores10Encuestados');

        // 5️⃣ Generar un PDF por docente
        foreach ($agrupado as $docente => $cursos) {

            // 📌 Calcular total de encuestados de TODO el docente
            $totalEncuestadosDocente = 0;
            foreach ($cursos as $turnos) {
                foreach ($turnos as $preguntas) {
                    $totalEncuestadosDocente += $preguntas->first()->total_respuestas;
                }
            }

            // 📌 Decidir carpeta según cantidad de encuestados
            $destino = $totalEncuestadosDocente < 10 ? 'menores10Encuestados' : 'mayores10Encuestados';

            // 📄 Generar PDF
            $pdf = Pdf::loadView('reportes.por_escuela_docente', [
                'escuela' => $escuela,
                'cod_escuela' => $codEscuela,
                'docente' => $docente,
                'cursos' => $cursos
            ]);

            // 📂 Guardar PDF en la carpeta correspondiente
            $fileName = $baseFolder . '/' . $destino . '/Reporte_' . $docente . '.pdf';
            Storage::put($fileName, $pdf->output());
        }

        return "✅ PDFs generados en storage/app/public/$baseFolder (carpetas mayores10 y menores10)";
    }

    public function reporteGeneral1($codEscuela)
    {
        // Ejecutar query con CTEs usando DB::select
        $resultados = DB::select("
        WITH mat_esc AS (          
            SELECT  m.COD_ALUMNO AS cod_alu,
                    m.COD_CURSO  AS cod_cur,
                    m.COD_PRO    AS cod_pro,
                    TRIM(m.COD_TURNO) AS grupo,
                    m.NOM_ESCUELA,
                    m.DES_CURSO  AS curso,
                    m.PROFESOR   AS docente
            FROM    matricula m
            WHERE   m.COD_ESCUELA = ?
        ),
        resp AS (                  
            SELECT  r.cod_alu,
                    r.cod_cur,
                    r.cod_pro,
                    TRIM(r.turno) AS grupo,
                    r.cod_alt
            FROM    enc_respuestas r
            JOIN    mat_esc m
                   ON  r.cod_alu = m.cod_alu
                  AND r.cod_cur = m.cod_cur
                  AND r.cod_pro = m.cod_pro
                  AND TRIM(r.turno) = m.grupo
        ),
        agrup AS (                 
            SELECT  m.NOM_ESCUELA,
                    m.docente,
                    m.curso,
                    m.grupo,
                    COUNT(DISTINCT r.cod_alu)     AS encuestados,
                    ROUND(AVG(r.cod_alt)*4,2)     AS puntaje_promedio
            FROM    resp r
            JOIN    mat_esc m
                   ON r.cod_alu = m.cod_alu
                  AND r.cod_cur = m.cod_cur
                  AND r.cod_pro = m.cod_pro
                  AND r.grupo   = m.grupo
            GROUP  BY m.NOM_ESCUELA,
                     m.docente,
                     m.curso,
                     m.grupo
        )
        SELECT  ROW_NUMBER() OVER (ORDER BY puntaje_promedio DESC) AS orden,
                CASE 
                  WHEN encuestados >= 10 THEN '≥10 encuestas'
                  ELSE '<10 encuestas'
                END AS categoria,
                docente,
                curso,
                grupo          AS grupo_horario,
                encuestados,
                puntaje_promedio
        FROM    agrup
        ORDER BY puntaje_promedio DESC
    ", [$codEscuela]);

        // 📊 Dividimos los resultados en dos grupos
        $masDiez = array_filter($resultados, fn($r) => $r->encuestados >= 10);
        $menosDiez = array_filter($resultados, fn($r) => $r->encuestados < 10);

        // ✅ Mandamos todo a la vista PDF
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->value('NOM_ESCUELA');

        $pdf = PDF::loadView('reportes.reporte_general', compact('escuela', 'masDiez', 'menosDiez'))
            ->setPaper('A4', 'portrait');

        //return $pdf->download("Reporte-$escuela.pdf");
        // 📂 Carpeta de destino en storage
        $folderPath = storage_path('app/reporte_general');

        // Si no existe la carpeta, la crea
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        // 📄 Nombre del archivo PDF
        $fileName = "Orden_de_merito_{$escuela}.pdf";

        // 📥 Guardamos el archivo en storage/app/reporte_general/
        $pdf->save($folderPath . '/' . $fileName);

        // 🔙 Opción 1: Devolver mensaje de éxito
        return response()->json([
            'message' => '✅ Reporte generado correctamente',
            'file' => "storage/app/reporte_general/$fileName"
        ]);
    }


    public function reporteGeneral($codEscuela)
    {
        // Ejecutar query con CTEs usando DB::select
        $resultados = DB::select("
        WITH mat_esc AS (          
            SELECT  m.COD_ALUMNO AS cod_alu,
                    m.COD_CURSO  AS cod_cur,
                    m.COD_PRO    AS cod_pro,
                    TRIM(m.COD_TURNO) AS grupo,
                    m.NOM_ESCUELA,
                    m.DES_CURSO  AS curso,
                    m.PROFESOR   AS docente
            FROM    matricula m
            WHERE   m.COD_ESCUELA = ?
        ),
        resp AS (                  
            SELECT  r.cod_alu,
                    r.cod_cur,
                    r.cod_pro,
                    TRIM(r.turno) AS grupo,
                    r.cod_alt
            FROM    enc_respuestas r
            JOIN    mat_esc m
                   ON  r.cod_alu = m.cod_alu
                  AND r.cod_cur = m.cod_cur
                  AND r.cod_pro = m.cod_pro
                  AND TRIM(r.turno) = m.grupo
        ),
        agrup AS (                 
            SELECT  m.NOM_ESCUELA,
                    m.docente,
                    m.curso,
                    m.grupo,
                    COUNT(DISTINCT r.cod_alu)     AS encuestados,
                    ROUND(AVG(r.cod_alt)*4,2)     AS puntaje_promedio
            FROM    resp r
            JOIN    mat_esc m
                   ON r.cod_alu = m.cod_alu
                  AND r.cod_cur = m.cod_cur
                  AND r.cod_pro = m.cod_pro
                  AND r.grupo   = m.grupo
            GROUP  BY m.NOM_ESCUELA,
                     m.docente,
                     m.curso,
                     m.grupo
        )
        SELECT  ROW_NUMBER() OVER (ORDER BY puntaje_promedio DESC) AS orden,
                docente,
                curso,
                grupo          AS grupo_horario,
                encuestados,
                puntaje_promedio
        FROM    agrup
        WHERE   encuestados >= 10
        ORDER BY puntaje_promedio DESC"
        , [$codEscuela]);

        // ✅ Ya no dividimos en dos grupos porque solo hay >=10
        $masDiez = $resultados;

        // ✅ Mandamos todo a la vista PDF
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->value('NOM_ESCUELA');

        $pdf = PDF::loadView('reportes.reporte_general', compact('escuela', 'masDiez'))
            ->setPaper('A4', 'portrait');

        // 📂 Carpeta de destino en storage
        $folderPath = storage_path('app/reporte_general');
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

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
