<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\Pago;
use Illuminate\Database\Seeder;

class PagoSeeder extends Seeder
{
    /**
     * Pagos reales de mensualidad registrados en Agosto 2026
     * (ADMINISTRACION_2026.xlsx, hoja PAGOS, bloque AGOSTO).
     * Algunos alumnos llevan mas de una especialidad, por eso pueden
     * aparecer con mas de un registro de pago en el mismo mes.
     */
    public function run(): void
    {
        $pagos = [
            ['alumno' => 'Kennan Lhi Durand Chirinos', 'concepto' => 'Mensualidad Piano', 'monto_total' => 280.0, 'yape' => 280.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2099'],
            ['alumno' => 'Diego Miguel Alanya Huanuco', 'concepto' => 'Mensualidad Piano', 'monto_total' => 200.0, 'yape' => 0.0, 'efectivo' => 200.0, 'saldo' => 0.0, 'recibo' => 'RC - 2010'],
            ['alumno' => 'Ginevra Medina Bernal', 'concepto' => 'Mensualidad Piano', 'monto_total' => 280.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 280.0, 'recibo' => null],
            ['alumno' => 'Leonardo Guadalupe Martinez', 'concepto' => 'Mensualidad Piano', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2068'],
            ['alumno' => 'Alessandro Aliaga Palomino', 'concepto' => 'Mensualidad Piano', 'monto_total' => 260.0, 'yape' => 260.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2082'],
            ['alumno' => 'Sami Daniela Salome Nieto', 'concepto' => 'Mensualidad Bateria', 'monto_total' => 150.0, 'yape' => 150.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2073'],
            ['alumno' => 'Santiago Cordova Ledezma', 'concepto' => 'Mensualidad Bateria', 'monto_total' => 265.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 265.0, 'recibo' => null],
            ['alumno' => 'Guadalupe Lucia Galvan Inga', 'concepto' => 'Mensualidad Bateria', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'rc - 2067'],
            ['alumno' => 'Caleb Ricardo Alanya Huanuco', 'concepto' => 'Mensualidad Piano', 'monto_total' => 200.0, 'yape' => 0.0, 'efectivo' => 200.0, 'saldo' => 0.0, 'recibo' => 'RC - 2010'],
            ['alumno' => 'Mario Alberto Alanya Huanuco', 'concepto' => 'Mensualidad Piano', 'monto_total' => 200.0, 'yape' => 0.0, 'efectivo' => 200.0, 'saldo' => 0.0, 'recibo' => 'RC - 2010'],
            ['alumno' => 'Cattleya Soto Chamorro', 'concepto' => 'Mensualidad Piano', 'monto_total' => 200.0, 'yape' => 200.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2090'],
            ['alumno' => 'Juan Ignacio Moran Moscote', 'concepto' => 'Mensualidad Bateria', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2022'],
            ['alumno' => 'Amy Valery Villalva Huari', 'concepto' => 'Mensualidad Piano', 'monto_total' => 0.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => null],
            ['alumno' => 'Vicky Valentina Castro Vila', 'concepto' => 'Mensualidad Piano', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2101'],
            ['alumno' => 'Ricardo Andre Morales Borja', 'concepto' => 'Mensualidad Piano', 'monto_total' => 220.0, 'yape' => 220.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2085'],
            ['alumno' => 'Joaquin Castro Rojas', 'concepto' => 'Mensualidad Piano', 'monto_total' => 230.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 230.0, 'recibo' => null],
            ['alumno' => 'Romano Alfaro Garcia', 'concepto' => 'Mensualidad Bateria', 'monto_total' => 150.0, 'yape' => 35.0, 'efectivo' => 35.0, 'saldo' => 80.0, 'recibo' => 'RC - 2093 Y 2106'],
            ['alumno' => 'Fabricio Chamorro Antesana', 'concepto' => 'Mensualidad Bateria', 'monto_total' => 150.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 150.0, 'recibo' => null],
            ['alumno' => 'Danna Ramos Acuña', 'concepto' => 'Mensualidad Bateria', 'monto_total' => 150.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 150.0, 'recibo' => null],
            ['alumno' => 'Santiago Cordova Ledezma', 'concepto' => 'Mensualidad Piano', 'monto_total' => 135.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 135.0, 'recibo' => null],
            ['alumno' => 'Arlet', 'concepto' => 'Mensualidad Piano', 'monto_total' => 0.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => null],
            ['alumno' => 'Danna Santos Maldonado', 'concepto' => 'Mensualidad Piano', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2094'],
            ['alumno' => 'Paulo Taipe Sahuanay', 'concepto' => 'Mensualidad Piano', 'monto_total' => 190.0, 'yape' => 0.0, 'efectivo' => 190.0, 'saldo' => 0.0, 'recibo' => 'RC - 2088'],
            ['alumno' => 'Sebastian Rojas Hurtado', 'concepto' => 'Mensualidad Piano', 'monto_total' => 215.0, 'yape' => 215.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2030'],
            ['alumno' => 'Gabriela Valetina Castro Blas', 'concepto' => 'Mensualidad Piano', 'monto_total' => 230.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 230.0, 'recibo' => null],
            ['alumno' => 'Pamela Aracely Matos Lagos', 'concepto' => 'Mensualidad Piano', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2018'],
            ['alumno' => 'Asiri Aracelly Alcayhuaman Amaya', 'concepto' => 'Mensualidad Piano', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2096'],
            ['alumno' => 'Nelliel Torrecillas Marcelo', 'concepto' => 'Mensualidad Piano', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2025'],
            ['alumno' => 'Caleb Ccatamayo Oscanoa', 'concepto' => 'Mensualidad Piano', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2104'],
            ['alumno' => 'Jose Leandro Aliaga Chuquillanqui', 'concepto' => 'Mensualidad Piano', 'monto_total' => 215.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 215.0, 'recibo' => null],
            ['alumno' => 'Andrea Illescas', 'concepto' => 'Mensualidad Piano', 'monto_total' => 230.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 230.0, 'recibo' => null],
            ['alumno' => 'Fabricio Casavilca Castro', 'concepto' => 'Mensualidad Piano', 'monto_total' => 230.0, 'yape' => 0.0, 'efectivo' => 230.0, 'saldo' => 0.0, 'recibo' => 'RC - 2070'],
            ['alumno' => 'Andrea Camila Piñas Sandoval', 'concepto' => 'Mensualidad Violin', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2062'],
            ['alumno' => 'Ariana Untiveros Suasnabar', 'concepto' => 'Mensualidad Violin', 'monto_total' => 280.0, 'yape' => 280.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2063'],
            ['alumno' => 'Roussanyel Aliaga Palomino', 'concepto' => 'Mensualidad Violin', 'monto_total' => 260.0, 'yape' => 140.0, 'efectivo' => 0.0, 'saldo' => 120.0, 'recibo' => 'RC - 2082'],
            ['alumno' => 'Valentina Isabella Miranda Martinez', 'concepto' => 'Mensualidad Violin', 'monto_total' => 165.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 165.0, 'recibo' => null],
            ['alumno' => 'Arlet', 'concepto' => 'Mensualidad', 'monto_total' => 0.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => null],
            ['alumno' => 'Emily Lucia Ordoñez Campos', 'concepto' => 'Mensualidad Violin', 'monto_total' => 280.0, 'yape' => 280.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2098'],
            ['alumno' => 'Yeriko Lhi Durand Chirinos', 'concepto' => 'Mensualidad Violin', 'monto_total' => 280.0, 'yape' => 280.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2102'],
            ['alumno' => 'Lucié Mancco Avellaneda', 'concepto' => 'Mensualidad Violin', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2026'],
            ['alumno' => 'Santiago Cordova Ledezma', 'concepto' => 'Mensualidad Violin', 'monto_total' => 215.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 215.0, 'recibo' => null],
            ['alumno' => 'Ian Pallarco Escalante', 'concepto' => 'Mensualidad Violin', 'monto_total' => 230.0, 'yape' => 200.0, 'efectivo' => 30.0, 'saldo' => 0.0, 'recibo' => 'RC - 2074'],
            ['alumno' => 'Gia Macarena Odicio Rodriguez', 'concepto' => 'Mensualidad Violin', 'monto_total' => 230.0, 'yape' => 100.0, 'efectivo' => 0.0, 'saldo' => 130.0, 'recibo' => 'RC - 2077'],
            ['alumno' => 'Maria José Aliaga Chuquillanqui', 'concepto' => 'Mensualidad Violin', 'monto_total' => 215.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 215.0, 'recibo' => null],
            ['alumno' => 'Valentina Videla Lazo', 'concepto' => 'Mensualidad Violin', 'monto_total' => 165.0, 'yape' => 165.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2103'],
            ['alumno' => 'Gia Condor Alanya', 'concepto' => 'Mensualidad Violin', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2080'],
            ['alumno' => 'Sofia Valencia Veliz', 'concepto' => 'Mensualidad Violin', 'monto_total' => 230.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 230.0, 'recibo' => null],
            ['alumno' => 'Nicolas Gustavo Quinto Ascurra', 'concepto' => 'Mensualidad Piano', 'monto_total' => 280.0, 'yape' => 280.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2020'],
            ['alumno' => 'Valentina Taype Sahuanay', 'concepto' => 'Mensualidad Piano', 'monto_total' => 270.0, 'yape' => 0.0, 'efectivo' => 270.0, 'saldo' => 0.0, 'recibo' => 'RC - 2072 Y 2089'],
            ['alumno' => 'Juan Pablo Rojas Hurtado', 'concepto' => 'Mensualidad Piano', 'monto_total' => 215.0, 'yape' => 215.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2030 Y 2087'],
            ['alumno' => 'Facundo Valero Maravi', 'concepto' => 'Mensualidad Flauta', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2078'],
            ['alumno' => 'Joab Ramos Acuña', 'concepto' => 'Mensualidad Piano', 'monto_total' => 150.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 150.0, 'recibo' => null],
            ['alumno' => 'Catalina Gomez Vega', 'concepto' => 'Mensualidad Piano', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2064'],
            ['alumno' => 'Jose Leandro Aliaga Chuquillanqui', 'concepto' => 'Mensualidad Saxofon', 'monto_total' => 280.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 280.0, 'recibo' => null],
            ['alumno' => 'Valentina Illesca Villanes', 'concepto' => 'Mensualidad Piano', 'monto_total' => 230.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 230.0, 'recibo' => null],
            ['alumno' => 'Ava Ochoa Huari', 'concepto' => 'Mensualidad Piano', 'monto_total' => 135.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 135.0, 'recibo' => null],
            ['alumno' => 'Karla Sotomayor Vergara', 'concepto' => 'Mensualidad Guitarra', 'monto_total' => 140.0, 'yape' => 140.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 140'],
            ['alumno' => 'Eduardo Miguel Palomino Ingaroca', 'concepto' => 'Mensualidad Guitarra', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2034'],
            ['alumno' => 'Joaquin Cuyutupa Rodriguez', 'concepto' => 'Mensualidad Guitarra', 'monto_total' => 150.0, 'yape' => 150.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2066'],
            ['alumno' => 'Almendra Fabiana Salome Caballero', 'concepto' => 'Mensualidad Guitarra', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2084'],
            ['alumno' => 'Kira Laureano Luis', 'concepto' => 'Mensualidad Guitarra', 'monto_total' => 280.0, 'yape' => 280.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2069'],
            ['alumno' => 'Valentina Yamile Morales Borja', 'concepto' => 'Mensualidad Guitarra', 'monto_total' => 220.0, 'yape' => 220.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2100'],
            ['alumno' => 'Jeison Huaman Asto', 'concepto' => 'Mensualidad Guitarra', 'monto_total' => 200.0, 'yape' => 0.0, 'efectivo' => 200.0, 'saldo' => 0.0, 'recibo' => 'efetivo kris'],
            ['alumno' => 'Thiago Condor Alanya', 'concepto' => 'Mensualidad Guitarra', 'monto_total' => 230.0, 'yape' => 230.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2080'],
            ['alumno' => 'Omar Diaz Medina', 'concepto' => 'Mensualidad Guitarra', 'monto_total' => 210.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 210.0, 'recibo' => null],
            ['alumno' => 'Alessandro Aliaga Palomino', 'concepto' => 'Mensualidad Guitarra', 'monto_total' => 205.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 205.0, 'recibo' => null],
            ['alumno' => 'Maria José Aliaga Chuquillanqui', 'concepto' => 'Mensualidad Piano', 'monto_total' => 215.0, 'yape' => 0.0, 'efectivo' => 0.0, 'saldo' => 215.0, 'recibo' => null],
            ['alumno' => 'Sophia Bastidas Villaverde', 'concepto' => 'Mensualidad Canto', 'monto_total' => 150.0, 'yape' => 150.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2009'],
            ['alumno' => 'Valentina Yamile Morales Borja', 'concepto' => 'Mensualidad Canto', 'monto_total' => 140.0, 'yape' => 140.0, 'efectivo' => 0.0, 'saldo' => 0.0, 'recibo' => 'RC - 2086'],
        ];

        foreach ($pagos as $p) {
            $alumno = Alumno::where('nombre', $p['alumno'])->first();
            if (! $alumno) {
                continue;
            }

            $periodo = \App\Models\Periodo::where('mes', 8)->where('anio', 2026)->first();

            $pago = Pago::updateOrCreate(
                [
                    'alumno_id' => $alumno->id,
                    'mes' => 8,
                    'anio' => 2026,
                    'concepto' => $p['concepto'],
                ],
                [
                    'periodo_id' => $periodo->id ?? null,
                    'especialidad_id' => $alumno->especialidad_id,
                    'maestro_id' => $alumno->maestro_id,
                    'monto_total' => $p['monto_total'],
                    'yape_transferencia' => $p['yape'],
                    'efectivo' => $p['efectivo'],
                    'tarjeta' => 0,
                    'saldo' => $p['saldo'],
                    'recibo_nro' => $p['recibo'],
                    'fecha_pago' => $p['saldo'] <= 0 ? now()->subDays(rand(1, 15))->format('Y-m-d') : null,
                ]
            );

            // Cada monto por metodo se convierte en su propio abono, como
            // quedaria si se hubiera registrado desde el modulo nuevo.
            $pago->abonos()->delete();
            foreach (['transferencia' => $p['yape'], 'efectivo' => $p['efectivo']] as $metodo => $monto) {
                if ($monto > 0) {
                    $pago->abonos()->create([
                        'monto' => $monto,
                        'fecha' => $pago->fecha_pago ?? now(),
                        'metodo_pago' => $metodo,
                        'recibo_nro' => $p['recibo'],
                    ]);
                }
            }
            $pago->recalcular();
        }
    }
}
