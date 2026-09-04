<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\Horario;
use App\Models\Maestro;
use App\Models\Especialidad;
use Illuminate\Database\Seeder;

class HorarioSeeder extends Seeder
{
    /**
     * Horarios semanales reales, interpretados desde la columna HORARIO
     * de ADMINISTRACION_2026.xlsx (texto libre tipo "Martes 4pm - Viernes 3pm").
     */
    public function run(): void
    {
        $horarios = [
            ['alumno' => 'Kennan Lhi Durand Chirinos', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'dia_semana' => 2, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Kennan Lhi Durand Chirinos', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'dia_semana' => 5, 'hora_inicio' => '15:00', 'hora_fin' => '16:00'],
            ['alumno' => 'Diego Miguel Alanya Huanuco', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'dia_semana' => 5, 'hora_inicio' => '15:00', 'hora_fin' => '16:00'],
            ['alumno' => 'Ginevra Medina Bernal', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'dia_semana' => 5, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Leonardo Guadalupe Martinez', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'dia_semana' => 5, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Alessandro Aliaga Palomino', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'dia_semana' => 5, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Sami Daniela Salome Nieto', 'especialidad' => 'Bateria', 'maestro' => 'Jeanpier', 'dia_semana' => 1, 'hora_inicio' => '15:00', 'hora_fin' => '16:00'],
            ['alumno' => 'Santiago Cordova Ledezma', 'especialidad' => 'Bateria', 'maestro' => 'Jeanpier', 'dia_semana' => 5, 'hora_inicio' => '18:00', 'hora_fin' => '19:00'],
            ['alumno' => 'Guadalupe Lucia Galvan Inga', 'especialidad' => 'Bateria', 'maestro' => 'Jeanpier', 'dia_semana' => 5, 'hora_inicio' => '18:00', 'hora_fin' => '19:00'],
            ['alumno' => 'Caleb Ricardo Alanya Huanuco', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'dia_semana' => 5, 'hora_inicio' => '19:00', 'hora_fin' => '20:00'],
            ['alumno' => 'Mario Alberto Alanya Huanuco', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'dia_semana' => 5, 'hora_inicio' => '19:00', 'hora_fin' => '20:00'],
            ['alumno' => 'Cattleya Soto Chamorro', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'dia_semana' => 4, 'hora_inicio' => '18:00', 'hora_fin' => '19:00'],
            ['alumno' => 'Cattleya Soto Chamorro', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'dia_semana' => 6, 'hora_inicio' => '15:00', 'hora_fin' => '16:00'],
            ['alumno' => 'Juan Ignacio Moran Moscote', 'especialidad' => 'Bateria', 'maestro' => 'Jeanpier', 'dia_semana' => 4, 'hora_inicio' => '15:00', 'hora_fin' => '16:00'],
            ['alumno' => 'Amy Valery Villalva Huari', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'dia_semana' => 3, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Vicky Valentina Castro Vila', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'dia_semana' => 4, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Ricardo Andre Morales Borja', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'dia_semana' => 4, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Joaquin Castro Rojas', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'dia_semana' => 4, 'hora_inicio' => '18:00', 'hora_fin' => '19:00'],
            ['alumno' => 'Romano Alfaro Garcia', 'especialidad' => 'Bateria', 'maestro' => 'Jeanpier', 'dia_semana' => 1, 'hora_inicio' => '18:00', 'hora_fin' => '19:00'],
            ['alumno' => 'Fabricio Chamorro Antesana', 'especialidad' => 'Bateria', 'maestro' => 'Jeanpier', 'dia_semana' => 6, 'hora_inicio' => '15:00', 'hora_fin' => '16:00'],
            ['alumno' => 'Danna Ramos Acuña', 'especialidad' => 'Bateria', 'maestro' => 'Jeanpier', 'dia_semana' => 6, 'hora_inicio' => '15:00', 'hora_fin' => '16:00'],
            ['alumno' => 'Danna Santos Maldonado', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'dia_semana' => 3, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Paulo Taipe Sahuanay', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'dia_semana' => 3, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Sebastian Rojas Hurtado', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'dia_semana' => 3, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Gabriela Valetina Castro Blas', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'dia_semana' => 3, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Pamela Aracely Matos Lagos', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'dia_semana' => 2, 'hora_inicio' => '18:00', 'hora_fin' => '19:00'],
            ['alumno' => 'Asiri Aracelly Alcayhuaman Amaya', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'dia_semana' => 4, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Nelliel Torrecillas Marcelo', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'dia_semana' => 4, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Caleb Ccatamayo Oscanoa', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'dia_semana' => 4, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Jose Leandro Aliaga Chuquillanqui', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'dia_semana' => 4, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Andrea Illescas', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'dia_semana' => 4, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Fabricio Casavilca Castro', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'dia_semana' => 4, 'hora_inicio' => '18:00', 'hora_fin' => '19:00'],
            ['alumno' => 'Andrea Camila Piñas Sandoval', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'dia_semana' => 1, 'hora_inicio' => '15:00', 'hora_fin' => '16:00'],
            ['alumno' => 'Ariana Untiveros Suasnabar', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'dia_semana' => 5, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Roussanyel Aliaga Palomino', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'dia_semana' => 5, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Valentina Isabella Miranda Martinez', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'dia_semana' => 5, 'hora_inicio' => '18:00', 'hora_fin' => '19:00'],
            ['alumno' => 'Emily Lucia Ordoñez Campos', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'dia_semana' => 5, 'hora_inicio' => '15:00', 'hora_fin' => '16:00'],
            ['alumno' => 'Yeriko Lhi Durand Chirinos', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'dia_semana' => 5, 'hora_inicio' => '15:00', 'hora_fin' => '16:00'],
            ['alumno' => 'Lucié Mancco Avellaneda', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'dia_semana' => 4, 'hora_inicio' => '18:00', 'hora_fin' => '19:00'],
            ['alumno' => 'Ian Pallarco Escalante', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'dia_semana' => 4, 'hora_inicio' => '19:00', 'hora_fin' => '20:00'],
            ['alumno' => 'Gia Macarena Odicio Rodriguez', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'dia_semana' => 4, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Maria José Aliaga Chuquillanqui', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'dia_semana' => 4, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Valentina Videla Lazo', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'dia_semana' => 4, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Gia Condor Alanya', 'especialidad' => 'Violin', 'maestro' => 'Rosa', 'dia_semana' => 6, 'hora_inicio' => '09:00', 'hora_fin' => '10:00'],
            ['alumno' => 'Sofia Valencia Veliz', 'especialidad' => 'Violin', 'maestro' => 'Rosa', 'dia_semana' => 5, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Nicolas Gustavo Quinto Ascurra', 'especialidad' => 'Piano', 'maestro' => 'Jerry', 'dia_semana' => 5, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Valentina Taype Sahuanay', 'especialidad' => 'Piano', 'maestro' => 'Jerry', 'dia_semana' => 3, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Juan Pablo Rojas Hurtado', 'especialidad' => 'Piano', 'maestro' => 'Jerry', 'dia_semana' => 3, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Facundo Valero Maravi', 'especialidad' => 'Flauta', 'maestro' => 'Miriam', 'dia_semana' => 6, 'hora_inicio' => '15:00', 'hora_fin' => '16:00'],
            ['alumno' => 'Joab Ramos Acuña', 'especialidad' => 'Piano', 'maestro' => 'Miriam', 'dia_semana' => 6, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Catalina Gomez Vega', 'especialidad' => 'Piano', 'maestro' => 'Miriam', 'dia_semana' => 4, 'hora_inicio' => '18:00', 'hora_fin' => '19:00'],
            ['alumno' => 'Valentina Illesca Villanes', 'especialidad' => 'Piano', 'maestro' => 'Miriam', 'dia_semana' => 4, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Ava Ochoa Huari', 'especialidad' => 'Piano', 'maestro' => 'Miriam', 'dia_semana' => 4, 'hora_inicio' => '17:00', 'hora_fin' => '18:00'],
            ['alumno' => 'Karla Sotomayor Vergara', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'dia_semana' => 5, 'hora_inicio' => '19:00', 'hora_fin' => '20:00'],
            ['alumno' => 'Eduardo Miguel Palomino Ingaroca', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'dia_semana' => 5, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Joaquin Cuyutupa Rodriguez', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'dia_semana' => 5, 'hora_inicio' => '18:00', 'hora_fin' => '19:00'],
            ['alumno' => 'Almendra Fabiana Salome Caballero', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'dia_semana' => 5, 'hora_inicio' => '19:00', 'hora_fin' => '20:00'],
            ['alumno' => 'Almendra Fabiana Salome Caballero', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'dia_semana' => 6, 'hora_inicio' => '11:00', 'hora_fin' => '12:00'],
            ['alumno' => 'Kira Laureano Luis', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'dia_semana' => 5, 'hora_inicio' => '19:00', 'hora_fin' => '20:00'],
            ['alumno' => 'Kira Laureano Luis', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'dia_semana' => 6, 'hora_inicio' => '11:00', 'hora_fin' => '12:00'],
            ['alumno' => 'Valentina Yamile Morales Borja', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'dia_semana' => 5, 'hora_inicio' => '16:00', 'hora_fin' => '17:00'],
            ['alumno' => 'Thiago Condor Alanya', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'dia_semana' => 6, 'hora_inicio' => '09:00', 'hora_fin' => '10:00'],
            ['alumno' => 'Omar Diaz Medina', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'dia_semana' => 6, 'hora_inicio' => '11:00', 'hora_fin' => '12:00'],
            ['alumno' => 'Sophia Bastidas Villaverde', 'especialidad' => 'Canto', 'maestro' => 'Kris', 'dia_semana' => 2, 'hora_inicio' => '18:00', 'hora_fin' => '19:00'],
            ['alumno' => 'Jeison Huaman Asto', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'dia_semana' => 6, 'hora_inicio' => '09:00', 'hora_fin' => '11:00'],
        ];

        foreach ($horarios as $h) {
            $alumno = Alumno::where('nombre', $h['alumno'])->first();
            if (! $alumno) {
                continue;
            }
            $maestro = $h['maestro'] ? Maestro::where('nombre', $h['maestro'])->first() : null;
            $especialidad = $h['especialidad'] ? Especialidad::where('nombre', $h['especialidad'])->first() : null;

            Horario::updateOrCreate(
                [
                    'alumno_id' => $alumno->id,
                    'dia_semana' => $h['dia_semana'],
                    'hora_inicio' => $h['hora_inicio'],
                ],
                [
                    'maestro_id' => $maestro?->id,
                    'especialidad_id' => $especialidad?->id ?? $alumno->especialidad_id,
                    'hora_fin' => $h['hora_fin'],
                    'activo' => true,
                ]
            );
        }
    }
}
