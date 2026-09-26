<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

/**
 * Permite subir ADMINISTRACION_2026.xlsx desde el navegador y correr
 * "pagos:importar-excel" sin necesitar el Shell de Render (que requiere
 * plan pago). El archivo se guarda temporalmente en storage/app/private
 * (fuera de git, igual que antes) y se borra apenas termina la
 * importacion real; nunca se commitea a git.
 *
 * Flujo: subir archivo -> se corre en --dry-run y se muestra el resumen
 * -> el usuario confirma -> se corre de nuevo, esta vez guardando de
 * verdad -> se borra el archivo del servidor.
 */
class ImportarExcelController extends Controller
{
    public function form()
    {
        return view('pagos.importar-excel');
    }

    public function subir(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $nombre = uniqid('admin_').'.xlsx';
        $request->file('archivo')->storeAs('imports', $nombre, 'local');
        $rutaAbsoluta = Storage::disk('local')->path('imports/'.$nombre);

        $resumen = $this->correr($rutaAbsoluta, dryRun: true);

        return view('pagos.importar-excel', [
            'resumenDryRun' => $resumen,
            'archivoTemporal' => $nombre,
        ]);
    }

    public function confirmar(Request $request)
    {
        $request->validate([
            'archivo_temporal' => ['required', 'string'],
        ]);

        $nombre = basename($request->input('archivo_temporal'));
        $rutaAbsoluta = Storage::disk('local')->path('imports/'.$nombre);

        if (! is_file($rutaAbsoluta)) {
            return back()->withErrors(['archivo_temporal' => 'El archivo temporal ya no existe, vuelve a subirlo.']);
        }

        $resumen = $this->correr($rutaAbsoluta, dryRun: false);

        // Se borra del servidor: ya cumplio su proposito y no debe
        // quedar dando vueltas (trae DNI y datos de menores).
        @unlink($rutaAbsoluta);

        return view('pagos.importar-excel', [
            'resumenFinal' => $resumen,
        ]);
    }

    private function correr(string $rutaAbsoluta, bool $dryRun): string
    {
        $parametros = ['archivo' => $rutaAbsoluta];
        if ($dryRun) {
            $parametros['--dry-run'] = true;
        }

        Artisan::call('pagos:importar-excel', $parametros);

        return Artisan::output();
    }
}
