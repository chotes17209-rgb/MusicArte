"""Uso: python3 database/data/extraer_asistencia.py ASISTENCIA_Y_HORARIOS_2026.xlsx database/data/administracion_2026.json salida.json

Lee la hoja ASISTENCIA (verde = asistio, amarillo/rojo = falta; el comentario
de la celda dice si se reprogramo) y la cruza con los talleres de la hoja PAGOS.
Luego se filtran las celdas sin marcar para generar asistencia_2026.json."""
import json, re, sys, collections, difflib, openpyxl
sys.path.insert(0, '.')
src = open(__file__.replace("extraer_asistencia.py", "extraer_excel.py")).read()
# reusar helpers del extractor de pagos sin ejecutar su lectura
ns = {}
exec(src.split('# ------------------------------------------------------------------ lectura')[0].replace('SRC, OUT = sys.argv[1], sys.argv[2]', ''), ns)
clave, limpio, ESP, MAE, ALIAS, titulo, sin_acentos = ns['clave'], ns['limpio'], ns['ESPECIALIDADES'], ns['MAESTROS'], ns['ALIAS'], ns['titulo'], ns['sin_acentos']

data = json.load(open(sys.argv[2]))
filas_pago = data['filas']
talleres_mes = collections.defaultdict(set)  # mes -> {(alumno, esp, maestro)}
for f in filas_pago:
    talleres_mes[f['mes']].add((f['alumno'], f['especialidad'], f['maestro']))
alumnos = {a['clave'] for a in data['alumnos']}

MESES = {'ENERO': 1, 'FEBRERO': 2, 'MARZO': 3, 'ABRIL': 4, 'MAYO': 5, 'JUNIO': 6, 'JULIO': 7, 'AGOSTO': 8, 'SETIEMBRE': 9, 'SEPTIEMBRE': 9}
# La letra se repite entre meses (A = abril/agosto, M = marzo/mayo): se usa la
# del bloque del mes, o la del mes vecino si la fecha es del mes anterior/siguiente.
LETRA = {'E': [1], 'F': [2], 'M': [3, 5], 'A': [4, 8], 'J': [6], 'JL': [7], 'S': [9], 'O': [10]}
VERDE = {'FF00B050', 'FF92D050'}
FALTA = {'FFFFFF00', 'FFF9FF01', 'FFFF0000'}

wb = openpyxl.load_workbook(sys.argv[1]); ws = wb['ASISTENCIA']
out, sin_match, mes = [], collections.Counter(), None
alias_extra = {}
for r in range(1, ws.max_row + 1):
    nombre = limpio(ws.cell(r, 3).value)
    if nombre:
        cab = clave(re.sub(r'[-–].*$', '', nombre))
        if cab in MESES:
            mes = MESES[cab]; continue
    if not nombre or mes is None or limpio(ws.cell(r, 2).value) in ('Nº', 'N°'):
        continue
    k = clave(nombre); k = ALIAS.get(k, k)
    esp_t = limpio(ws.cell(r, 5).value); mae_t = limpio(ws.cell(r, 6).value)
    esp = ESP.get(clave(esp_t)) if esp_t else None
    mae = (MAE.get(clave(mae_t)) or titulo(sin_acentos(mae_t))) if mae_t else None
    cands = talleres_mes[mes]
    if k not in alumnos or not any(c[0] == k for c in cands):
        # nombre escrito distinto: el mas parecido entre los alumnos de ese mes con ese taller
        pool = sorted({c[0] for c in cands if (esp is None or c[1] == esp)})
        m = difflib.get_close_matches(k, pool, n=1, cutoff=0.72)
        if not m:
            # Solo el nombre de pila ("PAMELA"): el unico alumno de ese mes que empieza asi
            # (y si hay varios, el que coincide en taller y maestro).
            pref = [c for c in cands if c[0].startswith(k + ' ') and (esp is None or c[1] == esp)]
            if len({c[0] for c in pref}) > 1:
                pref = [c for c in pref if c[2] == mae]
            if len({c[0] for c in pref}) == 1:
                m = [pref[0][0]]
        if m:
            alias_extra[k] = m[0]; k = m[0]
        else:
            sin_match[(mes, k)] += 1; continue
    # taller: exacto, o mismo alumno+especialidad, o unico taller del alumno ese mes
    t = [c for c in cands if c[0] == k and c[1] == esp and c[2] == mae] or \
        [c for c in cands if c[0] == k and c[1] == esp] or \
        [c for c in cands if c[0] == k]
    if len(t) != 1 and not (t and esp):
        sin_match[(mes, k, 'taller')] += 1; continue
    alumno, esp_ok, mae_ok = t[0]
    for col in range(8, 20):
        cell = ws.cell(r, col); v = limpio(cell.value)
        m = re.match(r'^(\d{1,2})\s*([A-Z]{1,2})\.?$', v or '')
        if not m: continue
        dia = int(m.group(1)); opciones = LETRA.get(m.group(2), [mes])
        if mes in opciones:
            mes_fecha = mes
        elif mes - 1 in opciones and dia >= 20:      # ultimos dias del mes anterior (el periodo empezo antes)
            mes_fecha = mes - 1
        elif mes + 1 in opciones and dia <= 14:      # primeros dias del mes siguiente
            mes_fecha = mes + 1
        else:
            mes_fecha = mes                           # letra mal escrita: se asume el mes del bloque
        fill = cell.fill.fgColor.rgb if cell.fill.fill_type and cell.fill.fgColor.type == 'rgb' else None
        com = re.sub(r'\s+', ' ', cell.comment.text.replace('USUARIO:', '').replace('USUARIO', '')).strip() if cell.comment else None
        if fill in VERDE:
            estado = 'asistio'
        elif fill in FALTA:
            estado = 'falto' if (not com or re.search(r'no avis|no asist|no vino|no dispone|no contesta', com, re.I)) else 'justificado'
        else:
            estado = None  # sin marcar en el Excel
        try:
            import datetime; fecha = datetime.date(2026, mes_fecha, dia).isoformat()
        except ValueError:
            continue
        out.append({'fila': r, 'mes': mes, 'alumno': alumno, 'especialidad': esp_ok, 'maestro': mae_ok,
                    'fecha': fecha, 'estado': estado, 'observacion': com or None})

print(len(out), 'celdas;', collections.Counter(o['estado'] for o in out))
print('alias detectados:', alias_extra)
print('sin match:', sin_match)
json.dump(out, open(sys.argv[3], 'w'), ensure_ascii=False)
