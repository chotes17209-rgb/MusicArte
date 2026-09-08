<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\PagoAbono;
use Barryvdh\DomPDF\Facade\Pdf;

class ReciboController extends Controller
{
    /** Recibo/estado de cuenta general del pago, con el detalle de todos sus abonos. */
    public function pdf(Pago $pago)
    {
        $pago->load('alumno.especialidad', 'alumnoTaller.especialidad', 'abonos');

        $pdf = Pdf::loadView('pagos.recibo-pdf', compact('pago'))->setPaper('a5');

        return $pdf->stream("recibo-{$pago->id}.pdf");
    }

    /**
     * Recibo individual de UN abono especifico (seccion 10: cada abono
     * debe poder generar su propio recibo con su numero, fecha y metodo).
     */
    public function pdfAbono(PagoAbono $abono)
    {
        $abono->load('pago.alumno.especialidad', 'pago.alumnoTaller.especialidad');

        $pdf = Pdf::loadView('pagos.recibo-abono-pdf', compact('abono'))->setPaper('a5');

        return $pdf->stream("recibo-abono-{$abono->id}.pdf");
    }
}
