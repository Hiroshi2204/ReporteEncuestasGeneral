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

    public function reportePorEscuela1($codEscuela)
    {
        // 1️⃣ Obtener el nombre de la escuela
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $codEscuela)
            ->value('NOM_ESCUELA');

        if (!$escuela) {
            abort(404, 'La escuela no existe en la base de datos.');
        }

        // 2️⃣ Obtener los datos incluyendo docente, curso y turno
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

        // 3️⃣ Agrupar datos
        $agrupado = $datos
            ->groupBy('docente')
            ->map(fn($cursos) => $cursos->groupBy('curso')
                ->map(fn($turnos) => $turnos->groupBy('turno')));

        // 4️⃣ Generar PDF
        $pdf = Pdf::loadView('reportes.por_escuela', [
            'escuela' => $escuela,
            'cod_escuela' => $codEscuela,
            'agrupado' => $agrupado
        ]);

        // 5️⃣ Descargar el PDF
        return $pdf->download('Reporte_' . $escuela . '.pdf');
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
        $folder = 'reportes/' . $codEscuela;
        Storage::makeDirectory($folder);

        // 5️⃣ Generar un PDF por docente
        foreach ($agrupado as $docente => $cursos) {
            $pdf = Pdf::loadView('reportes.por_escuela_docente', [
                'escuela' => $escuela,
                'cod_escuela' => $codEscuela,
                'docente' => $docente,
                'cursos' => $cursos
            ]);

            $fileName = $folder . '/Reporte_' . $docente . '.pdf';
            Storage::put($fileName, $pdf->output());
        }

        return "✅ PDFs generados en storage/app/public/$folder";
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
}
