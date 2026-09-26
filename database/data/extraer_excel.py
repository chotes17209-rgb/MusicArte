"""Uso: python3 database/data/extraer_excel.py ADMINISTRACION_2026.xlsx database/data/administracion_2026.json

Convierte la hoja PAGOS de ADMINISTRACION_2026.xlsx en un JSON limpio
(database/data/administracion_2026.json) que importa la migracion."""
import json, re, sys, unicodedata, datetime, collections
import openpyxl

SRC, OUT = sys.argv[1], sys.argv[2]

MESES = {'ENERO': 1, 'FEBRERO': 2, 'MARZO': 3, 'ABRIL': 4, 'MAYO': 5, 'JUNIO': 6,
         'JULIO': 7, 'AGOSTO': 8, 'SETIEMBRE': 9, 'SEPTIEMBRE': 9}

ESPECIALIDADES = {'PIANO': 'Piano', 'PIANON': 'Piano', 'BATERIA': 'Bateria', 'BATERIAJE': 'Bateria',
                  'CANTO': 'Canto', 'FLAUTA': 'Flauta', 'GUITARRA': 'Guitarra', 'SAXOFON': 'Saxofon',
                  'VIOLIN': 'Violin', 'INICIACION MUSICAL': 'Iniciacion Musical'}

MAESTROS = {'ARIAN': 'Ariam', 'ARIAM': 'Ariam'}  # el resto: Titulo Case tal cual

# Mismo alumno escrito distinto entre meses -> nombre canonico.
ALIAS = {
    'SAMI SALOME NIETO': 'SAMI DANIELA SALOME NIETO',
    'VALERIA RETAMOZO CASAS': 'VALERIA RETAMOZO CACERES',
    'GHIA GIMENES GALVAN': 'GHIA JIMENEZ GALVAN',
    'VALENTINA ILLESCAS': 'VALENTINA ILLESCA VILLANES',
    'ARLET': 'ARLETH LEON ACOSTA',
}

# Filas a las que les falta especialidad/maestro/horario en el Excel y se
# completan con lo que dice el propio Excel en otras filas/hojas.
COMPLETAR = {
    # (fila): {campo: valor}
    98: {'especialidad': 'PIANO'},    # ALISE VELASCO VEGA, Ariam (maestro de piano)
    208: {'especialidad': 'PIANO'},   # idem febrero
    141: {'especialidad': 'PIANO', 'maestro': 'JEANPIER'},  # ALESSANDRO SERVA MELGAR (marzo: piano Jeanpier)
    783: {'especialidad': 'PIANO', 'maestro': 'JERRY', 'horario': 'LUNES MIERCOLES Y VIERNES 6 PM - 30 MIN'},  # JUAN (hoja HORARIOS)
    9: {'total': 315},                # ARELIZ enero: TOTAL="cor", pago 315 por yape
}


def sin_acentos(s):
    s = unicodedata.normalize('NFD', str(s))
    return ''.join(c for c in s if unicodedata.category(c) != 'Mn')


def clave(s):
    return re.sub(r'\s+', ' ', sin_acentos(s)).strip().upper()


def limpio(v):
    if v is None:
        return None
    s = re.sub(r'\s+', ' ', str(v)).strip()
    return s or None


def titulo(s):
    return ' '.join(w.capitalize() for w in re.sub(r'\s+', ' ', s).strip().lower().split(' '))


def numero(v):
    if v is None or v == '':
        return None
    if isinstance(v, (int, float)):
        return round(float(v), 2)
    try:
        return round(float(str(v).replace(',', '.')), 2)
    except ValueError:
        return None


# ------------------------------------------------------------------ horarios
DIAS = [
    (r'LUNES|LUN', 1), (r'MARTES|MA', 2), (r'MIERCOLES|MI', 3), (r'JUEVES|JUVES|JU', 4),
    (r'VIERNNES|VIERNES|VI', 5), (r'SABADOS|SABADO|SAB|S', 6),
]
DIA_RE = '|'.join(f'(?:{p})' for p, _ in DIAS)
def a_minutos(h, m, suf):
    h, m = int(h), int(m or 0)
    if suf in ('PM', 'P.M.') and h < 12:
        h += 12
    if suf == 'MD':
        h = 12
    if suf in ('AM', 'A.M.') and h == 12:  # "12 AM" en una academia es mediodia
        h = 12
    return h * 60 + m


def parsear_horario(texto, manana):
    """'LUNES MIERCOLES Y VIERNES 4PM - 30 MIN' -> [(1, '16:00', '16:30'), ...]"""
    if not texto:
        return []
    t = sin_acentos(texto).upper()
    t = t.replace('(', ' ').replace(')', ' ').replace(',', ' ')
    t = re.sub(r'(\d)(AM|PM|MD)', r'\1 \2', t)
    t = re.sub(r'\bAMY\b', 'AM Y', t)
    dur = 60
    if re.search(r'1\s*1/2', t):
        dur = 90
        t = re.sub(r'1\s*1/2', ' ', t)
    if re.search(r'30\s*MI(N)?\b', t):
        dur = 30
        t = re.sub(r'-?\s*30\s*MI(N)?\b', ' ', t)
    t = re.sub(r'\bDE\b', ' ', t)

    patron_dia = re.compile(rf'\b(?:{DIA_RE})\b')
    patron_rango = re.compile(rf'(\d{{1,2}})(?::(\d{{2}}))?\s*(AM|PM|MD)?\s*(?:-|\bA\b)\s*(\d{{1,2}})(?::(\d{{2}}))?\s*(AM|PM|MD)?')
    patron_hora = re.compile(r'(\d{1,2})(?::(\d{2}))?\s*(AM|PM|MD)?')

    slots, pendientes, ultimo = [], [], None
    i = 0
    while i < len(t):
        m = patron_dia.match(t, i)
        if m and (i == 0 or not t[i - 1].isalnum()):
            palabra = m.group(0)
            for p, d in DIAS:
                if re.fullmatch(p, palabra):
                    pendientes.append(d)
                    break
            i = m.end()
            continue
        m = patron_rango.match(t, i)
        if m and t[i].isdigit():
            h1, m1, s1, h2, m2, s2 = m.groups()
            s2 = s2 or s1 or ('AM' if manana else 'PM')
            fin = a_minutos(h2, m2, s2)
            ini = a_minutos(h1, m1, s1 or s2)
            if ini >= fin and not s1:  # "11 A 1 PM" -> 11 AM a 1 PM
                ini = a_minutos(h1, m1, 'AM')
            ultimo = (ini, fin)
            for d in pendientes:
                slots.append((d, ini, fin))
            pendientes = []
            i = m.end()
            continue
        m = patron_hora.match(t, i)
        if m and t[i].isdigit():
            h, mm, s = m.groups()
            if not s:
                if int(h) == 12:
                    s = 'MD'
                elif manana:
                    s = 'AM' if int(h) >= 7 else 'PM'
                else:
                    s = 'PM' if int(h) <= 8 else 'AM'
            if manana and s == 'PM' and 8 <= int(h) <= 11:  # "10 PM" en el turno manana = 10 AM
                s = 'AM'
            ini = a_minutos(h, mm, s)
            ultimo = (ini, ini + dur)
            for d in pendientes:
                slots.append((d, ini, ini + dur))
            pendientes = []
            i = m.end()
            continue
        i += 1
    # Dias sueltos al final sin hora propia ("MARTES Y JUEVES 4 PM Y SABADOS"): misma hora.
    if pendientes and ultimo:
        for d in pendientes:
            slots.append((d, *ultimo))
    fmt = lambda x: f'{x // 60:02d}:{x % 60:02d}'
    vistos, out = set(), []
    for d, a, b in slots:
        if (d, a) in vistos:
            continue
        vistos.add((d, a))
        out.append({'dia_semana': d, 'hora_inicio': fmt(a), 'hora_fin': fmt(b)})
    return sorted(out, key=lambda x: (x['dia_semana'], x['hora_inicio']))


# ------------------------------------------------------------------ lectura
wb = openpyxl.load_workbook(SRC, data_only=True)
ws = wb['PAGOS']

alumnos = collections.OrderedDict()   # clave canonica -> datos personales
filas = []
mes, manana = None, False
for r in range(1, ws.max_row + 1):
    c = lambda col: ws.cell(r, col).value
    nombre = limpio(c(3))
    if nombre:
        cab = clave(re.sub(r'[-–].*$', '', nombre))
        if cab in MESES:
            mes = MESES[cab]
            manana = 'MANANA' in clave(nombre)
            continue
    if limpio(c(2)) in ('Nº', 'N°') or not nombre or mes is None:
        continue

    fix = COMPLETAR.get(r, {})
    k = clave(nombre)
    k = ALIAS.get(k, k)

    esp_txt = fix.get('especialidad') or limpio(c(5))
    esp = ESPECIALIDADES.get(clave(esp_txt)) if esp_txt else None
    mae_txt = fix.get('maestro') or limpio(c(6))
    maestro = (MAESTROS.get(clave(mae_txt)) or titulo(sin_acentos(mae_txt))) if mae_txt else None
    horario_txt = fix.get('horario') or limpio(c(7))

    total = fix.get('total', numero(c(13)))
    yape = numero(c(15)) or 0.0
    efectivo = numero(c(16)) or 0.0
    saldo_excel = numero(c(17))
    recibo = limpio(c(18))
    obs = limpio(c(19))

    fnac = c(9)
    fnac = fnac.date().isoformat() if isinstance(fnac, datetime.datetime) else None
    dni = limpio(c(12))
    if dni and not re.fullmatch(r'\d{7,9}', dni):
        dni = None  # "S/BOLETA", etc.

    a = alumnos.setdefault(k, {'clave': k, 'nombre': titulo(nombre if clave(nombre) == k else k),
                               'alias': [], 'edad': None, 'fecha_nacimiento': None, 'tutor': None,
                               'celular': None, 'dni': None, 'diagnostico': None, 'meses': set()})
    if clave(nombre) != k and clave(nombre) not in a['alias']:
        a['alias'].append(clave(nombre))
    # Datos personales: gana el dato mas reciente que no este vacio.
    for campo, valor in (('edad', limpio(c(4))), ('fecha_nacimiento', fnac), ('tutor', limpio(c(10))),
                         ('celular', limpio(c(11))), ('dni', dni), ('diagnostico', limpio(c(8)))):
        if valor:
            a[campo] = titulo(valor) if campo == 'tutor' else valor
    a['meses'].add(mes)

    filas.append({
        'fila': r, 'mes': mes, 'alumno': k, 'especialidad': esp, 'maestro': maestro,
        'horario_texto': horario_txt, 'horarios': parsear_horario(horario_txt, manana),
        'total': total or 0.0, 'yape': yape, 'efectivo': efectivo, 'saldo_excel': saldo_excel,
        'recibo': recibo, 'observacion': obs,
    })

# Primer nombre visto con tildes/enie gana como nombre a mostrar.
for r in range(1, ws.max_row + 1):
    n = limpio(ws.cell(r, 3).value)
    if n and clave(n) in alumnos and clave(n) == alumnos[clave(n)]['clave']:
        a = alumnos[clave(n)]
        if a['nombre'] == titulo(a['clave']) and n.upper() != sin_acentos(n).upper():
            a['nombre'] = titulo(n)

for a in alumnos.values():
    a['meses'] = sorted(a['meses'])
    a['activo'] = 9 in a['meses']

json.dump({'anio': 2026, 'alumnos': list(alumnos.values()), 'filas': filas},
          open(OUT, 'w'), ensure_ascii=False, indent=1)
print(len(alumnos), 'alumnos,', len(filas), 'filas')
