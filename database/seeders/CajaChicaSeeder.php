<?php

namespace Database\Seeders;

use App\Models\CajaChica;
use Illuminate\Database\Seeder;

class CajaChicaSeeder extends Seeder
{
    /**
     * Movimientos reales de caja chica (ASISTENCIA_Y_HORARIOS_2026.xlsx,
     * hoja CAJA CHICA), de Marzo a Julio 2026.
     */
    public function run(): void
    {
        $movimientos = [
            ['fecha' => '2026-03-05', 'proveedor' => 'Pasteleria', 'descripcion' => 'Compra de muffin de cumpleaños', 'monto' => 6.0],
            ['fecha' => '2026-03-19', 'proveedor' => 'Pasteleria', 'descripcion' => 'Queque de Arandanos', 'monto' => 8.0],
            ['fecha' => '2026-03-19', 'proveedor' => 'Tienda', 'descripcion' => 'Agua Mineral', 'monto' => 2.0],
            ['fecha' => '2026-03-21', 'proveedor' => 'Librería', 'descripcion' => 'Hojas Bond', 'monto' => 12.5],
            ['fecha' => '2026-03-21', 'proveedor' => 'Librería', 'descripcion' => 'Hoja Escarchada', 'monto' => 1.0],
            ['fecha' => '2026-03-23', 'proveedor' => 'MUSICARTE', 'descripcion' => 'Vuelto a mamá de Lucie', 'monto' => 20.0],
            ['fecha' => '2026-03-23', 'proveedor' => 'MUSICARTE', 'descripcion' => 'Vuelto al papá de Alize', 'monto' => 20.0],
            ['fecha' => '2026-03-23', 'proveedor' => 'Pasteleria', 'descripcion' => 'Compra de muffin de cumpleaños', 'monto' => 7.0],
            ['fecha' => '2026-03-24', 'proveedor' => 'Tienda', 'descripcion' => '2 Aguas Minerales', 'monto' => 3.3],
            ['fecha' => '2026-03-24', 'proveedor' => 'Tienda', 'descripcion' => 'Roscas', 'monto' => 3.3],
            ['fecha' => '2026-03-25', 'proveedor' => 'MUSICARTE', 'descripcion' => 'Vuelto a la mama de Ginevra', 'monto' => 10.0],
            ['fecha' => '2026-03-25', 'proveedor' => 'Botica', 'descripcion' => 'Mascarilla KN95', 'monto' => 1.5],
            ['fecha' => '2026-03-26', 'proveedor' => 'Plastiqueria', 'descripcion' => 'Sorbetones', 'monto' => 10.0],
            ['fecha' => '2026-03-26', 'proveedor' => 'Librería', 'descripcion' => 'Globos', 'monto' => 4.0],
            ['fecha' => '2026-03-26', 'proveedor' => 'Botica', 'descripcion' => 'Toma para la garganta inflamada', 'monto' => 5.0],
            ['fecha' => '2026-03-26', 'proveedor' => 'Pasteleria', 'descripcion' => 'Infusion de Anis', 'monto' => 6.0],
            ['fecha' => '2026-03-27', 'proveedor' => null, 'descripcion' => '1 toma de pastillas para la garganta', 'monto' => 4.5],
            ['fecha' => '2026-03-27', 'proveedor' => null, 'descripcion' => 'Pilas para el Mouse', 'monto' => 3.0],
            ['fecha' => '2026-03-27', 'proveedor' => null, 'descripcion' => 'Agua Mineral', 'monto' => 1.8],
            ['fecha' => '2026-03-27', 'proveedor' => null, 'descripcion' => '5 tomas de pastillas', 'monto' => 17.0],
            ['fecha' => '2026-04-01', 'proveedor' => null, 'descripcion' => 'Queque de Arandano', 'monto' => 6.0],
            ['fecha' => '2026-04-04', 'proveedor' => null, 'descripcion' => 'Queque de Arandano', 'monto' => 6.0],
            ['fecha' => '2026-04-04', 'proveedor' => null, 'descripcion' => 'Cupcake de Chocolate', 'monto' => 6.0],
            ['fecha' => '2026-04-04', 'proveedor' => null, 'descripcion' => 'Cupcake de Chocolate', 'monto' => 6.0],
            ['fecha' => '2026-04-06', 'proveedor' => null, 'descripcion' => 'Agua Mineral', 'monto' => 1.8],
            ['fecha' => '2026-04-06', 'proveedor' => null, 'descripcion' => 'Platano', 'monto' => 0.6],
            ['fecha' => '2026-04-06', 'proveedor' => null, 'descripcion' => 'Pastilla para el dolor de cabeza', 'monto' => 2.5],
            ['fecha' => '2026-04-06', 'proveedor' => null, 'descripcion' => 'Galleta Ritz (2)', 'monto' => 2.0],
            ['fecha' => '2026-04-08', 'proveedor' => null, 'descripcion' => 'Ingredientes para Pizza', 'monto' => 33.7],
            ['fecha' => '2026-04-08', 'proveedor' => null, 'descripcion' => 'Agua Mineral', 'monto' => 1.8],
            ['fecha' => '2026-04-08', 'proveedor' => null, 'descripcion' => 'Platano', 'monto' => 7.0],
            ['fecha' => '2026-04-08', 'proveedor' => null, 'descripcion' => 'Queque de Arandano', 'monto' => 6.0],
            ['fecha' => '2026-04-09', 'proveedor' => null, 'descripcion' => 'Platano', 'monto' => 0.7],
            ['fecha' => '2026-04-18', 'proveedor' => null, 'descripcion' => 'SOFI ADELANTO', 'monto' => 75.3],
            ['fecha' => '2026-04-29', 'proveedor' => null, 'descripcion' => 'Cupcake de Chocolate', 'monto' => 6.0],
            ['fecha' => '2026-05-05', 'proveedor' => null, 'descripcion' => 'Cupcake de Chocolate', 'monto' => 6.0],
            ['fecha' => '2026-05-06', 'proveedor' => null, 'descripcion' => 'Cupcake de Chocolate', 'monto' => 6.0],
            ['fecha' => '2026-05-09', 'proveedor' => null, 'descripcion' => 'Poet', 'monto' => 2.8],
            ['fecha' => '2026-05-09', 'proveedor' => null, 'descripcion' => 'Detergente', 'monto' => 1.0],
            ['fecha' => '2026-05-09', 'proveedor' => null, 'descripcion' => 'Cartulina', 'monto' => 3.0],
            ['fecha' => '2026-05-09', 'proveedor' => null, 'descripcion' => 'Agua Mineral', 'monto' => 1.0],
            ['fecha' => '2026-05-12', 'proveedor' => null, 'descripcion' => 'Papel Higenico', 'monto' => 17.0],
            ['fecha' => '2026-05-19', 'proveedor' => null, 'descripcion' => 'Hojas Bond', 'monto' => 12.5],
            ['fecha' => '2026-05-20', 'proveedor' => null, 'descripcion' => 'Cupcake de Chocolate', 'monto' => 6.0],
            ['fecha' => '2026-06-06', 'proveedor' => null, 'descripcion' => 'Cupcake de Chocolate', 'monto' => 6.0],
            ['fecha' => '2026-06-08', 'proveedor' => null, 'descripcion' => 'Empanada', 'monto' => 35.0],
            ['fecha' => '2026-06-08', 'proveedor' => null, 'descripcion' => 'Jugo', 'monto' => 4.5],
            ['fecha' => '2026-06-11', 'proveedor' => null, 'descripcion' => 'Cupcake de Chocolate', 'monto' => 6.0],
            ['fecha' => '2026-06-11', 'proveedor' => null, 'descripcion' => 'Matty', 'monto' => 2.0],
            ['fecha' => '2026-06-13', 'proveedor' => null, 'descripcion' => 'Hojas Escarchada', 'monto' => 2.0],
            ['fecha' => '2026-06-14', 'proveedor' => null, 'descripcion' => 'Platano Joaqui', 'monto' => 0.6],
            ['fecha' => '2026-06-15', 'proveedor' => null, 'descripcion' => 'Taxi para tinta', 'monto' => 6.0],
            ['fecha' => '2026-06-16', 'proveedor' => null, 'descripcion' => 'Torta Matty', 'monto' => 10.0],
            ['fecha' => '2026-06-17', 'proveedor' => null, 'descripcion' => 'Lapiz y Borrador', 'monto' => 3.6],
            ['fecha' => '2026-06-17', 'proveedor' => null, 'descripcion' => 'Sobre cerrado', 'monto' => 0.3],
            ['fecha' => '2026-06-17', 'proveedor' => null, 'descripcion' => 'Torta jeampier', 'monto' => 48.0],
            ['fecha' => '2026-06-17', 'proveedor' => null, 'descripcion' => 'Cinta Masquin', 'monto' => 5.5],
            ['fecha' => '2026-06-23', 'proveedor' => null, 'descripcion' => 'Taxi Impresoxra', 'monto' => 11.0],
            ['fecha' => '2026-06-30', 'proveedor' => null, 'descripcion' => 'Torta Miriam', 'monto' => 48.0],
            ['fecha' => '2026-06-30', 'proveedor' => null, 'descripcion' => 'Sobre cerrado', 'monto' => 0.3],
            ['fecha' => '2026-07-01', 'proveedor' => null, 'descripcion' => 'Cupcake de Chocolate', 'monto' => 6.0],
            ['fecha' => '2026-07-11', 'proveedor' => null, 'descripcion' => 'USB', 'monto' => 13.5],
            ['fecha' => '2026-07-18', 'proveedor' => null, 'descripcion' => 'Matty Agua', 'monto' => 2.0],
            ['fecha' => '2026-07-24', 'proveedor' => null, 'descripcion' => 'Cupcake de Chocolate', 'monto' => 6.0],
        ];

        foreach ($movimientos as $m) {
            CajaChica::updateOrCreate(
                ['fecha' => $m['fecha'], 'descripcion' => $m['descripcion'], 'monto' => $m['monto']],
                $m
            );
        }
    }
}
