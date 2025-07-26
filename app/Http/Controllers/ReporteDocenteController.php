<?php

namespace App\Http\Controllers;

use App\Models\Matricula;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteDocenteController extends Controller
{
    // public function generarPorEscuela($cod_escuela)
    // {
    //     // 1️⃣ Traer nombre de la escuela
    //     $escuela = DB::table('matricula')
    //         ->where('COD_ESCUELA', $cod_escuela)
    //         ->value('NOM_ESCUELA');

    //     // 2️⃣ Ejecutar query único para esa escuela
    //     $datos = DB::select("
    //         SELECT 
    //             m.COD_ESCUELA,
    //             m.NOM_ESCUELA,
    //             m.COD_PRO,
    //             m.PROFESOR,
    //             m.COD_CURSO,
    //             m.DES_CURSO,
    //             m.COD_TURNO,
    //             a.nom_area,
    //             p.nom_pre AS pregunta,
    //             SUM(r.cod_alt = 1) AS n1,
    //             SUM(r.cod_alt = 2) AS n2,
    //             SUM(r.cod_alt = 3) AS n3,
    //             SUM(r.cod_alt = 4) AS n4,
    //             SUM(r.cod_alt = 5) AS n5,
    //             COUNT(*) AS total_respuestas,
    //             ROUND( ((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4 + SUM(r.cod_alt = 5)*5) / COUNT(*)) * 4, 2) AS nota_item_20
    //         FROM matricula m
    //         JOIN enc_respuestas r 
    //             ON r.cod_alu = m.COD_ALUMNO 
    //            AND r.cod_cur = m.COD_CURSO 
    //            AND r.cod_pro = m.COD_PRO 
    //            AND TRIM(r.turno) = TRIM(m.COD_TURNO)
    //         JOIN enc_pregunta p ON p.cod_pre = r.cod_pre
    //         JOIN enc_area a ON a.cod_area = p.cod_area
    //         WHERE m.COD_ESCUELA = ?
    //         GROUP BY m.COD_ESCUELA, m.NOM_ESCUELA,
    //                  m.COD_PRO, m.PROFESOR,
    //                  m.COD_CURSO, m.DES_CURSO, m.COD_TURNO,
    //                  a.nom_area, p.nom_pre
    //         ORDER BY m.PROFESOR, m.COD_CURSO, m.COD_TURNO, p.cod_pre
    //     ", [$cod_escuela]);

    //     // 3️⃣ Agrupar datos en PHP
    //     $agrupado = [];
    //     foreach ($datos as $row) {
    //         $agrupado[$row->COD_PRO][$row->COD_CURSO][$row->COD_TURNO][] = $row;
    //     }

    //     // 4️⃣ Generar PDF
    //     $pdf = PDF::loadView('reportes.por_escuela', [
    //         'escuela' => $escuela,
    //         'cod_escuela' => $cod_escuela,
    //         'agrupado' => $agrupado
    //     ]);

    //     return $pdf->download("reporte_{$cod_escuela}.pdf");
    // }

    public function generarPorEscuela($cod_escuela)
    {
        // 1️⃣ Traer nombre de la escuela
        $escuela = DB::table('matricula')
            ->where('COD_ESCUELA', $cod_escuela)
            ->value('NOM_ESCUELA');

        // 2️⃣ Ejecutar query con Query Builder (más estable)
        $datos = DB::table('matricula as m')
            ->join('enc_respuestas as r', function ($join) {
                $join->on('r.cod_alu', '=', 'm.COD_ALUMNO')
                    ->on('r.cod_cur', '=', 'm.COD_CURSO')
                    ->on('r.cod_pro', '=', 'm.COD_PRO')
                    ->whereRaw('TRIM(r.turno) = TRIM(m.COD_TURNO)');
            })
            ->join('enc_pregunta as p', 'p.cod_pre', '=', 'r.cod_pre')
            ->join('enc_area as a', 'a.cod_area', '=', 'p.cod_area')
            ->select(
                'm.COD_ESCUELA',
                'm.NOM_ESCUELA',
                'm.COD_PRO',
                'm.PROFESOR',
                'm.COD_CURSO',
                'm.DES_CURSO',
                'm.COD_TURNO',
                'a.nom_area',
                'p.nom_pre AS pregunta',
                DB::raw('SUM(r.cod_alt = 1) AS n1'),
                DB::raw('SUM(r.cod_alt = 2) AS n2'),
                DB::raw('SUM(r.cod_alt = 3) AS n3'),
                DB::raw('SUM(r.cod_alt = 4) AS n4'),
                DB::raw('SUM(r.cod_alt = 5) AS n5'),
                DB::raw('COUNT(*) AS total_respuestas'),
                DB::raw('ROUND(((SUM(r.cod_alt = 1)*1 + SUM(r.cod_alt = 2)*2 + SUM(r.cod_alt = 3)*3 + SUM(r.cod_alt = 4)*4 + SUM(r.cod_alt = 5)*5) / COUNT(*)) * 4, 2) AS nota_item_20')
            )
            ->where('m.COD_ESCUELA', $cod_escuela)
            ->groupBy(
                'm.COD_ESCUELA',
                'm.NOM_ESCUELA',
                'm.COD_PRO',
                'm.PROFESOR',
                'm.COD_CURSO',
                'm.DES_CURSO',
                'm.COD_TURNO',
                'a.nom_area',
                'p.nom_pre'
            )
            ->orderBy('m.PROFESOR')
            ->orderBy('m.COD_CURSO')
            ->orderBy('m.COD_TURNO')
            ->orderBy('p.cod_pre')
            ->get();

        // 3️⃣ Agrupar datos en PHP (para la vista)
        $agrupado = [];
        foreach ($datos as $row) {
            $agrupado[$row->COD_PRO][$row->COD_CURSO][$row->COD_TURNO][] = $row;
        }

        // 4️⃣ Generar PDF
        $pdf = PDF::loadView('reportes.por_escuela', [
            'escuela' => $escuela,
            'cod_escuela' => $cod_escuela,
            'agrupado' => $agrupado
        ]);

        return $pdf->download("reporte_{$cod_escuela}.pdf");
    }


    public function listaEscuelas()
    {
        // Para listar las escuelas disponibles
        $escuelas = DB::table('matricula')
            ->select('COD_ESCUELA', 'NOM_ESCUELA')
            ->distinct()
            ->orderBy('NOM_ESCUELA')
            ->get();

        return view('reportes.lista_escuelas', compact('escuelas'));
    }

    //  public function listaEscuelas()
    // {
    //     // ✅ Obtener escuelas distintas ordenadas
    //     $escuelas = Matricula::select('COD_ESCUELA', 'NOM_ESCUELA')
    //         ->distinct()
    //         ->orderBy('NOM_ESCUELA')
    //         ->get();

    //     // ✅ Retornar la vista
    //     return view('reportes.lista_escuelas', compact('escuelas'));
    // }
}
