<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Barryvdh\DomPDF\Facade\Pdf;

class ReciboController extends Controller
{
    public function pdf(Pago $pago)
    {
        $pago->load('alumno.especialidad');

        $pdf = Pdf::loadView('pagos.recibo-pdf', compact('pago'))->setPaper('a5');

        return $pdf->stream("recibo-{$pago->id}.pdf");
    }
}
