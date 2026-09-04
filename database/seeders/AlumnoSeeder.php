<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\Especialidad;
use App\Models\Maestro;
use Illuminate\Database\Seeder;

class AlumnoSeeder extends Seeder
{
    /**
     * Padron real de alumnos activos de Musica Arte (Agosto 2026), extraido
     * directamente de ADMINISTRACION_2026.xlsx (hoja PAGOS).
     */
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'Kennan Lhi Durand Chirinos', 'edad' => '17 AÑOS', 'fecha_nacimiento' => '2009-01-07', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'tutor' => 'Roberto Chirinos', 'celular' => '929784004/ 925862109', 'dni' => '72209030', 'diagnostico' => null],
            ['nombre' => 'Diego Miguel Alanya Huanuco', 'edad' => '10 AÑOS', 'fecha_nacimiento' => '2016-03-06', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'tutor' => 'Rosario Huanuco', 'celular' => '986005245', 'dni' => '41329409', 'diagnostico' => null],
            ['nombre' => 'Ginevra Medina Bernal', 'edad' => '13 AÑOS', 'fecha_nacimiento' => '2012-12-06', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'tutor' => 'Rosario Bernal Camposano', 'celular' => '959632210', 'dni' => '20060426', 'diagnostico' => null],
            ['nombre' => 'Leonardo Guadalupe Martinez', 'edad' => '06 AÑOS', 'fecha_nacimiento' => '2019-06-25', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'tutor' => 'Ida Urtecho Flores', 'celular' => '964776621', 'dni' => null, 'diagnostico' => null],
            ['nombre' => 'Alessandro Aliaga Palomino', 'edad' => '12 AÑOS', 'fecha_nacimiento' => '2013-10-29', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'tutor' => 'Angely Palomino Cortez', 'celular' => '964663381', 'dni' => '40048392', 'diagnostico' => null],
            ['nombre' => 'Sami Daniela Salome Nieto', 'edad' => '11 AÑOS', 'fecha_nacimiento' => '2014-10-17', 'especialidad' => 'Bateria', 'maestro' => 'Jeanpier', 'tutor' => 'Gabriela Nieto Mendez', 'celular' => '999909502', 'dni' => null, 'diagnostico' => null],
            ['nombre' => 'Santiago Cordova Ledezma', 'edad' => '10 AÑOS', 'fecha_nacimiento' => '2017-12-25', 'especialidad' => 'Bateria', 'maestro' => 'Jeanpier', 'tutor' => 'Obed Alexander Cordova', 'celular' => '971474525', 'dni' => '43480251', 'diagnostico' => null],
            ['nombre' => 'Guadalupe Lucia Galvan Inga', 'edad' => '14 AÑOS', 'fecha_nacimiento' => '2011-09-29', 'especialidad' => 'Bateria', 'maestro' => 'Jeanpier', 'tutor' => 'Jackeline Inga Clemente', 'celular' => '964180592', 'dni' => '42318083', 'diagnostico' => null],
            ['nombre' => 'Caleb Ricardo Alanya Huanuco', 'edad' => '07 AÑOS', 'fecha_nacimiento' => '2018-08-28', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'tutor' => 'Rosario Huanuco', 'celular' => '986005245', 'dni' => '41329409', 'diagnostico' => null],
            ['nombre' => 'Mario Alberto Alanya Huanuco', 'edad' => '07 AÑOS', 'fecha_nacimiento' => '2018-08-28', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'tutor' => 'Rosario Huanuco', 'celular' => '986005245', 'dni' => '41329409', 'diagnostico' => null],
            ['nombre' => 'Cattleya Soto Chamorro', 'edad' => '07 AÑOS', 'fecha_nacimiento' => '2018-04-14', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'tutor' => 'Deddy Chamorro', 'celular' => '954043536', 'dni' => '42449831', 'diagnostico' => null],
            ['nombre' => 'Juan Ignacio Moran Moscote', 'edad' => '06 AÑOS', 'fecha_nacimiento' => '2019-06-11', 'especialidad' => 'Bateria', 'maestro' => 'Jeanpier', 'tutor' => 'Johana Moscote', 'celular' => '946118642', 'dni' => null, 'diagnostico' => null],
            ['nombre' => 'Amy Valery Villalva Huari', 'edad' => '12 AÑOS', 'fecha_nacimiento' => '2013-05-21', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'tutor' => 'Giancarlo Villalva', 'celular' => '942795477', 'dni' => '44848703 / 45352811', 'diagnostico' => null],
            ['nombre' => 'Vicky Valentina Castro Vila', 'edad' => '08 AÑOS', 'fecha_nacimiento' => '2017-04-12', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'tutor' => 'Vicky Vila Paucarcaja', 'celular' => '941496320', 'dni' => '41245268', 'diagnostico' => null],
            ['nombre' => 'Ricardo Andre Morales Borja', 'edad' => '11 AÑOS', 'fecha_nacimiento' => '2014-09-18', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'tutor' => 'Lizeth Borja Silva', 'celular' => '921859703', 'dni' => '47604544', 'diagnostico' => null],
            ['nombre' => 'Joaquin Castro Rojas', 'edad' => '08 AÑOS', 'fecha_nacimiento' => '2018-05-01', 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'tutor' => 'Fanny Rojas Lazo', 'celular' => '964004009', 'dni' => '40635009', 'diagnostico' => null],
            ['nombre' => 'Romano Alfaro Garcia', 'edad' => '16 AÑOS', 'fecha_nacimiento' => '2010-01-08', 'especialidad' => 'Bateria', 'maestro' => 'Jeanpier', 'tutor' => 'Javier Alfaro Camborda', 'celular' => '979799875/976929806', 'dni' => '7965737', 'diagnostico' => null],
            ['nombre' => 'Fabricio Chamorro Antesana', 'edad' => '12 AÑOS', 'fecha_nacimiento' => '2013-06-02', 'especialidad' => 'Bateria', 'maestro' => 'Jeanpier', 'tutor' => 'Deeedy Chamorro', 'celular' => '954043536', 'dni' => '42449831', 'diagnostico' => null],
            ['nombre' => 'Danna Ramos Acuña', 'edad' => '07 AÑOS', 'fecha_nacimiento' => '2018-09-28', 'especialidad' => 'Bateria', 'maestro' => 'Jeanpier', 'tutor' => 'Rusvelt Ramos', 'celular' => '998400406', 'dni' => '44757775', 'diagnostico' => null],
            ['nombre' => 'Arlet', 'edad' => null, 'fecha_nacimiento' => null, 'especialidad' => 'Piano', 'maestro' => 'Jeanpier', 'tutor' => null, 'celular' => null, 'dni' => null, 'diagnostico' => null],
            ['nombre' => 'Danna Santos Maldonado', 'edad' => '07 AÑOS', 'fecha_nacimiento' => '2021-08-12', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'tutor' => 'Grecia Maldonafo', 'celular' => '963570350', 'dni' => '72724556', 'diagnostico' => null],
            ['nombre' => 'Paulo Taipe Sahuanay', 'edad' => '3 AÑÑOS', 'fecha_nacimiento' => '2026-06-08', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'tutor' => 'Gina Sahuanay', 'celular' => '901483175', 'dni' => '70937894', 'diagnostico' => null],
            ['nombre' => 'Sebastian Rojas Hurtado', 'edad' => '05 AÑOS', 'fecha_nacimiento' => '2020-05-06', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'tutor' => 'Judith Rojas Hurtado', 'celular' => '961767781', 'dni' => '45158273', 'diagnostico' => null],
            ['nombre' => 'Gabriela Valetina Castro Blas', 'edad' => '09 AÑOS', 'fecha_nacimiento' => '2017-07-24', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'tutor' => 'Erika Blas', 'celular' => '985001677', 'dni' => '42413924', 'diagnostico' => null],
            ['nombre' => 'Pamela Aracely Matos Lagos', 'edad' => '6 AÑOS', 'fecha_nacimiento' => '2019-06-29', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'tutor' => 'Pamela Katherine Lagos Arieta', 'celular' => '981 817 239', 'dni' => '44077423', 'diagnostico' => null],
            ['nombre' => 'Asiri Aracelly Alcayhuaman Amaya', 'edad' => '16 AÑOS', 'fecha_nacimiento' => '2009-11-27', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'tutor' => 'Marcia Amai Cordova', 'celular' => '975703377', 'dni' => '41377955', 'diagnostico' => null],
            ['nombre' => 'Nelliel Torrecillas Marcelo', 'edad' => '04 AÑOS', 'fecha_nacimiento' => '2021-10-18', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'tutor' => 'Carla Marcelo Maraví', 'celular' => '944654564', 'dni' => '72396805', 'diagnostico' => null],
            ['nombre' => 'Caleb Ccatamayo Oscanoa', 'edad' => '03 AÑOS', 'fecha_nacimiento' => '2022-05-04', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'tutor' => 'Yelina Oscanoa', 'celular' => '944251376', 'dni' => '44521854', 'diagnostico' => null],
            ['nombre' => 'Jose Leandro Aliaga Chuquillanqui', 'edad' => '09 AÑOS', 'fecha_nacimiento' => '2016-12-07', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'tutor' => 'Lizet Fiorella Chuquillanqui', 'celular' => '956305544', 'dni' => '43935292', 'diagnostico' => null],
            ['nombre' => 'Andrea Illescas', 'edad' => '12 AÑOS', 'fecha_nacimiento' => '2012-02-21', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'tutor' => 'Gianina Villanes', 'celular' => '964591221', 'dni' => null, 'diagnostico' => null],
            ['nombre' => 'Fabricio Casavilca Castro', 'edad' => '05 AÑOS', 'fecha_nacimiento' => '2020-03-05', 'especialidad' => 'Piano', 'maestro' => 'Noemi', 'tutor' => 'Anshikrly Castro Lagones', 'celular' => '961377081', 'dni' => '72632913', 'diagnostico' => null],
            ['nombre' => 'Andrea Camila Piñas Sandoval', 'edad' => '11 AÑOS', 'fecha_nacimiento' => '2014-11-16', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'tutor' => 'Magaly Jaida Sandoval Serpa', 'celular' => '954920277', 'dni' => '41858039', 'diagnostico' => null],
            ['nombre' => 'Ariana Untiveros Suasnabar', 'edad' => '05 AÑOS', 'fecha_nacimiento' => '2020-10-31', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'tutor' => 'Coralay Suasnabar Ricaldi', 'celular' => '959465079', 'dni' => '46457264', 'diagnostico' => null],
            ['nombre' => 'Roussanyel Aliaga Palomino', 'edad' => '15 AÑOS', 'fecha_nacimiento' => '2009-08-12', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'tutor' => 'Angely Palomino Cortez', 'celular' => '964663381', 'dni' => '40048392', 'diagnostico' => null],
            ['nombre' => 'Valentina Isabella Miranda Martinez', 'edad' => '07 AÑOS', 'fecha_nacimiento' => '2019-02-14', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'tutor' => 'Agela Martinez Jimenez', 'celular' => '910810667', 'dni' => null, 'diagnostico' => null],
            ['nombre' => 'Emily Lucia Ordoñez Campos', 'edad' => '06 AÑOS', 'fecha_nacimiento' => '2019-10-08', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'tutor' => 'Nancy Beatriz Campos Payano', 'celular' => '954470746', 'dni' => '45536693', 'diagnostico' => null],
            ['nombre' => 'Yeriko Lhi Durand Chirinos', 'edad' => '16 AÑOS', 'fecha_nacimiento' => '2009-12-18', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'tutor' => 'Roberto Chirinos', 'celular' => '900577955', 'dni' => '72209930', 'diagnostico' => null],
            ['nombre' => 'Lucié Mancco Avellaneda', 'edad' => '11 AÑOS', 'fecha_nacimiento' => '2014-11-03', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'tutor' => 'Heidy Avellaneda Guerrero', 'celular' => '964890290', 'dni' => '42569768', 'diagnostico' => null],
            ['nombre' => 'Ian Pallarco Escalante', 'edad' => '11 AÑOS', 'fecha_nacimiento' => '2015-03-04', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'tutor' => 'Jemny Escalante', 'celular' => '949149296', 'dni' => '46494000', 'diagnostico' => null],
            ['nombre' => 'Gia Macarena Odicio Rodriguez', 'edad' => '6 AÑOS', 'fecha_nacimiento' => '2019-09-04', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'tutor' => 'Lidney Rodriguez Armas', 'celular' => '934444220', 'dni' => '47248977', 'diagnostico' => null],
            ['nombre' => 'Maria José Aliaga Chuquillanqui', 'edad' => '13 AÑOS', 'fecha_nacimiento' => '2012-01-07', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'tutor' => 'Lizet Fiorella Chuquillanqui', 'celular' => '956305544', 'dni' => '43935292', 'diagnostico' => null],
            ['nombre' => 'Valentina Videla Lazo', 'edad' => '05 AÑOS', 'fecha_nacimiento' => '2021-04-09', 'especialidad' => 'Violin', 'maestro' => 'Josue', 'tutor' => 'Pamela Lazo De La Haza', 'celular' => '954409747', 'dni' => null, 'diagnostico' => null],
            ['nombre' => 'Gia Condor Alanya', 'edad' => '08 AÑOS', 'fecha_nacimiento' => '2017-11-22', 'especialidad' => 'Violin', 'maestro' => 'Rosa', 'tutor' => 'Lisbeth Alanya', 'celular' => '959484883', 'dni' => '45194807', 'diagnostico' => null],
            ['nombre' => 'Sofia Valencia Veliz', 'edad' => '12 AÑOS', 'fecha_nacimiento' => '2013-08-01', 'especialidad' => 'Violin', 'maestro' => 'Rosa', 'tutor' => 'Liz Veliz Inga', 'celular' => '917345993', 'dni' => '42730037', 'diagnostico' => null],
            ['nombre' => 'Nicolas Gustavo Quinto Ascurra', 'edad' => null, 'fecha_nacimiento' => null, 'especialidad' => 'Piano', 'maestro' => 'Jerry', 'tutor' => 'Marganel Edith Quinto Huamani', 'celular' => '920622700', 'dni' => '40732516', 'diagnostico' => null],
            ['nombre' => 'Valentina Taype Sahuanay', 'edad' => '09 AÑOS', 'fecha_nacimiento' => '2017-05-06', 'especialidad' => 'Piano', 'maestro' => 'Jerry', 'tutor' => 'Gina Sahuanay', 'celular' => '901483175', 'dni' => '70937894', 'diagnostico' => null],
            ['nombre' => 'Juan Pablo Rojas Hurtado', 'edad' => '03 AÑOS', 'fecha_nacimiento' => '2022-02-08', 'especialidad' => 'Piano', 'maestro' => 'Jerry', 'tutor' => 'Judith Hurtado Santiago', 'celular' => '961767781', 'dni' => '45158273', 'diagnostico' => null],
            ['nombre' => 'Facundo Valero Maravi', 'edad' => '07 AÑOS', 'fecha_nacimiento' => '2018-10-29', 'especialidad' => 'Flauta', 'maestro' => 'Miriam', 'tutor' => 'Sheyla Valero Maravi', 'celular' => '991375157', 'dni' => null, 'diagnostico' => null],
            ['nombre' => 'Joab Ramos Acuña', 'edad' => '10 AÑOS', 'fecha_nacimiento' => '2016-01-08', 'especialidad' => 'Piano', 'maestro' => 'Miriam', 'tutor' => 'Rusvelt Ramos', 'celular' => '998400406', 'dni' => '44757775', 'diagnostico' => null],
            ['nombre' => 'Catalina Gomez Vega', 'edad' => '05 AÑOS', 'fecha_nacimiento' => '2020-08-22', 'especialidad' => 'Piano', 'maestro' => 'Miriam', 'tutor' => 'Rosa Vega Uscuvilca', 'celular' => '969587761', 'dni' => '75226447', 'diagnostico' => null],
            ['nombre' => 'Valentina Illesca Villanes', 'edad' => '11 AÑOS', 'fecha_nacimiento' => '2014-01-21', 'especialidad' => 'Piano', 'maestro' => 'Miriam', 'tutor' => 'Gianina Villanes Peña', 'celular' => '964591221', 'dni' => '40010991', 'diagnostico' => null],
            ['nombre' => 'Ava Ochoa Huari', 'edad' => '03 AÑOS', 'fecha_nacimiento' => '2022-04-29', 'especialidad' => 'Piano', 'maestro' => 'Miriam', 'tutor' => 'Roselyn Huari', 'celular' => '966 247 920', 'dni' => null, 'diagnostico' => null],
            ['nombre' => 'Karla Sotomayor Vergara', 'edad' => '11 AÑOS', 'fecha_nacimiento' => '2014-04-27', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'tutor' => 'Cinthia Vergara', 'celular' => '968688630', 'dni' => '20099328', 'diagnostico' => null],
            ['nombre' => 'Eduardo Miguel Palomino Ingaroca', 'edad' => '9 AÑOS', 'fecha_nacimiento' => '2016-09-11', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'tutor' => 'Ketty Ingaroca Huaman', 'celular' => '949400194', 'dni' => '40919989', 'diagnostico' => null],
            ['nombre' => 'Joaquin Cuyutupa Rodriguez', 'edad' => '05 AÑOS', 'fecha_nacimiento' => '2020-10-04', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'tutor' => 'Gabriela Rodriguez Iparraguirre', 'celular' => '964739110', 'dni' => '45039695', 'diagnostico' => null],
            ['nombre' => 'Almendra Fabiana Salome Caballero', 'edad' => '11 AÑOS', 'fecha_nacimiento' => '2014-03-16', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'tutor' => 'Vanesa Caballero', 'celular' => '964914895', 'dni' => '42389674', 'diagnostico' => null],
            ['nombre' => 'Kira Laureano Luis', 'edad' => '12 AÑOS', 'fecha_nacimiento' => '2013-09-21', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'tutor' => 'Aussy Luis Marticorena', 'celular' => '999601060', 'dni' => '20073695', 'diagnostico' => null],
            ['nombre' => 'Valentina Yamile Morales Borja', 'edad' => '13 AÑOS', 'fecha_nacimiento' => '2012-09-04', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'tutor' => 'Lizeth Borja Silva', 'celular' => '921859703', 'dni' => '47604544', 'diagnostico' => null],
            ['nombre' => 'Jeison Huaman Asto', 'edad' => '17AÑOS', 'fecha_nacimiento' => '2008-07-26', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'tutor' => 'Jorge Asto', 'celular' => '983787019', 'dni' => '23523597', 'diagnostico' => 'RETARDO'],
            ['nombre' => 'Thiago Condor Alanya', 'edad' => '11 AÑOS', 'fecha_nacimiento' => '2014-09-10', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'tutor' => 'Lisbeth Alanya', 'celular' => '959484883', 'dni' => '45194807', 'diagnostico' => null],
            ['nombre' => 'Omar Diaz Medina', 'edad' => '13 AÑOS', 'fecha_nacimiento' => '2013-01-19', 'especialidad' => 'Guitarra', 'maestro' => 'Javier', 'tutor' => 'Fiorella Medina', 'celular' => '944665898', 'dni' => '41568666', 'diagnostico' => null],
            ['nombre' => 'Sophia Bastidas Villaverde', 'edad' => '12 AÑOS', 'fecha_nacimiento' => '2013-06-07', 'especialidad' => 'Canto', 'maestro' => 'Kris', 'tutor' => 'Lucy Villaverde Villanueva', 'celular' => '969661683', 'dni' => '40144194', 'diagnostico' => null],
        ];

        foreach ($alumnos as $a) {
            $especialidad = $a['especialidad'] ? Especialidad::where('nombre', $a['especialidad'])->first() : null;
            $maestro = $a['maestro'] ? Maestro::where('nombre', $a['maestro'])->first() : null;

            Alumno::updateOrCreate(
                ['nombre' => $a['nombre']],
                [
                    'fecha_nacimiento' => $a['fecha_nacimiento'],
                    'especialidad_id' => $especialidad?->id,
                    'maestro_id' => $maestro?->id,
                    'tutor' => $a['tutor'],
                    'celular' => $a['celular'],
                    'dni' => $a['dni'],
                    'diagnostico' => $a['diagnostico'],
                    'activo' => true,
                ]
            );
        }
    }
}
