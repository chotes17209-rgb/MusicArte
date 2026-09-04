<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\PagoAbono;
use Barryvdh\DomPDF\Facade\Pdf;

class ReciboController extends Controller
{
    /** Recibo "resumen" del pago completo (deuda + todos sus abonos). */
    public function pdf(Pago $pago)
    {
        $pago->load(['alumno.especialidad', 'especialidad', 'maestro', 'periodo', 'abonos']);

        $pdf = Pdf::loadView('pagos.recibo-pdf', compact('pago'))->setPaper('a5');

        return $pdf->stream("recibo-{$pago->id}.pdf");
    }

    /** Req. 10: recibo individual de un abono especifico. */
    public function pdfAbono(Pago $pago, PagoAbono $abono)
    {
        abort_if($abono->pago_id !== $pago->id, 404);

        $pago->load(['alumno', 'especialidad', 'maestro', 'periodo']);
        $abono->load('usuario');

        $pdf = Pdf::loadView('pagos.recibo-abono-pdf', compact('pago', 'abono'))->setPaper('a5');

        return $pdf->stream("recibo-abono-{$abono->id}.pdf");
    }
}
