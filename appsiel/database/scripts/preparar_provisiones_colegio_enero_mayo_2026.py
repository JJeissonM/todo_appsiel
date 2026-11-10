#!/usr/bin/env python3
"""Prepara el soporte de la regularización autorizada el 10-11-2026.

No conecta a bases de datos. Lee snapshots privados de producción y maestros
históricos de biaBLE. Enero-abril: bases de nómina y factores de mayo;
mayo: valor_consolidado_mes existente, sin recalcular ni alterar el consolidado.
"""
import calendar
import csv
import hashlib
import json
import sys
from collections import defaultdict
from datetime import date
from decimal import Decimal, ROUND_HALF_UP
from pathlib import Path

D = Decimal


def pesos(value):
    return int(D(value).quantize(D('1'), rounding=ROUND_HALF_UP))


def days360(start, end):
    def serial(value):
        year, month, day = map(int, value.split('-'))
        if day == calendar.monthrange(year, month)[1] or day == 31:
            day = 30
        return year * 360 + (month - 1) * 30 + day
    return max(0, serial(end) - serial(start) + 1)


def prepare(folder):
    folder = Path(folder)
    source = json.loads((folder / 'fuente.json').read_text())
    historical = json.loads((folder / 'mapa_contratos.json').read_text())
    concepts = {r['id']: r for r in source['conceptos']}
    documents = {r['id']: r for r in source['documentos']}
    contracts = {r['id']: r for r in source['contratos']}
    parameters = {r['concepto_prestacion']: r for r in source['parametros']}
    accounts = {r['id']: r for r in source['cuentas']}
    group19 = {r['nom_concepto_id'] for r in source['agrupaciones']
               if r['nom_agrupacion_id'] == '19'}
    # Sustitutos del sueldo, con el valor efectivamente registrado en nómina.
    replacements = {'050', '051', '052', '053', '054', '060', '061'}
    vacation_salary = {'029', '034', '035', '135'}
    grouped = defaultdict(list)
    for r in source['registros']:
        assert r['core_empresa_id'] == '1'
        assert r['nom_contrato_id'] in historical
        assert r['core_tercero_id'] == contracts[r['nom_contrato_id']]['core_tercero_id']
        grouped[(r['fecha'][:7], r['nom_contrato_id'])].append(r)
    rows = []
    zero_vacations = []
    accumulated_ces = defaultdict(int)
    accumulated_interest = defaultdict(int)
    for month in range(1, 5):
        period = f'2026-{month:02d}'
        closing = f'{period}-{calendar.monthrange(2026, month)[1]}'
        for (row_period, cid), records in sorted(grouped.items()):
            if row_period != period:
                continue
            ct = contracts[cid]
            hc = historical[cid]
            assert ct['salario_integral'] == '0' and ct['es_pasante_sena'] == '0'
            assert ct['tipo_cotizante'] != '51'
            teacher = hc['GRUPO_EMPLEADOS'] == '02'
            # Factores observados en DIFERIDOS_BIABLE.XLS: docentes 10% / 5%;
            # otros empleados 1/12 / 1/24. Se conservan las bases históricas.
            ces_divisor = D(10 if teacher else 12)
            vacation_divisor = D(20 if teacher else 24)
            salary = D(0)
            extras = D(0)
            salary_records = [r for r in records
                              if concepts[r['nom_concepto_id']]['abreviatura'] == '001'
                              and D(r['valor_devengo']) > 0
                              and D(r['cantidad_horas']) > 0]
            for r in records:
                c = concepts[r['nom_concepto_id']]
                code = c['abreviatura']
                doc = documents[r['nom_doc_encabezado_id']]
                # Compensación de vacaciones por retiro no es salario del mes.
                is_vacation_salary = (code in vacation_salary and
                                     doc['tipo_liquidacion'] != 'liquidacion_contrato')
                net = D(r['valor_devengo']) - D(r['valor_deduccion'])
                if code == '001' or code in replacements or is_vacation_salary:
                    salary += net
                elif r['nom_concepto_id'] in group19:
                    extras += net
                if code == '035' and is_vacation_salary and net == 0 and D(r['cantidad_horas']) > 0:
                    # En enero hay vacaciones disfrutadas pagadas previamente:
                    # su cantidad de horas conserva el tiempo salarial aunque
                    # el devengo esté en cero. La tarifa es la del sueldo del
                    # mismo documento (ambos usan la misma unidad histórica).
                    matched = [s for s in salary_records
                               if s['nom_doc_encabezado_id'] == r['nom_doc_encabezado_id']]
                    assert len(matched) == 1, ('Tarifa histórica ambigua', r['id'])
                    wage = matched[0]
                    amount = (D(wage['valor_devengo']) / D(wage['cantidad_horas']) *
                              D(r['cantidad_horas']))
                    salary += amount
                    zero_vacations.append({'registro_id': int(r['id']),
                                           'registro_sueldo_id': int(wage['id']),
                                           'periodo': period, 'contrato_id': int(cid),
                                           'valor_base_reconstruido': str(amount)})
            base = salary + extras
            if salary == 0 and extras == 0:
                continue  # Por ejemplo, consignaciones de cesantías del año anterior.
            assert salary >= 0 and base >= 0, ('Base negativa', period, cid)
            ces = pesos(base / ces_divisor)
            prima = pesos(base / ces_divisor)
            vacation = pesos(salary / vacation_divisor)
            accumulated_ces[cid] += ces
            entry = hc['FECHA_INGRESO']
            start = max('2026-01-01', f'{entry[:4]}-{entry[4:6]}-{entry[6:8]}')
            retirement = hc['FECHA_RETIRO']
            end = closing
            if retirement and retirement != '00000000':
                end = min(end, f'{retirement[:4]}-{retirement[4:6]}-{retirement[6:8]}')
            days = days360(start, end)
            assert days > 0, ('Nómina salarial fuera del contrato histórico', period, cid)
            # Incremento mensual de intereses causados sobre cesantías de 2026;
            # no confundir intereses pagados en enero por el año anterior.
            target_interest = pesos(D(accumulated_ces[cid]) * D('0.12') * D(days) / D(360))
            interest = target_interest - accumulated_interest[cid]
            accumulated_interest[cid] = target_interest
            values = {'cesantias': ces, 'prima_legal': prima,
                      'vacaciones': vacation, 'intereses_cesantias': interest}
            for benefit, value in values.items():
                rows.append({'periodo': period, 'fecha': closing,
                             'nom_contrato_id': int(cid), 'core_tercero_id': int(ct['core_tercero_id']),
                             'prestacion': benefit, 'valor': value,
                             'base_salarial': str(salary), 'base_adicional': str(extras),
                             'divisor': str(vacation_divisor if benefit == 'vacaciones' else ces_divisor),
                             'dias_acumulados_2026': days, 'doc_nomina_ids': sorted({int(r['nom_doc_encabezado_id']) for r in records}),
                             'origen': 'Nomina historica; factores mayo 2026'})
    for r in source['consolidados']:
        assert r['fecha_fin_mes'] == '2026-05-30' and r['estado'] == 'Saldo inicial'
        ct = contracts[r['nom_contrato_id']]
        assert ('2026-05', r['nom_contrato_id']) in grouped
        rows.append({'periodo': '2026-05', 'fecha': r['fecha_fin_mes'],
                     'nom_contrato_id': int(r['nom_contrato_id']),
                     'core_tercero_id': int(ct['core_tercero_id']),
                     'prestacion': r['tipo_prestacion'], 'valor': pesos(r['valor_consolidado_mes']),
                     'consolidado_id': int(r['id']), 'origen': 'Consolidado mayo existente, sin recalcular'})
    seen = set()
    for r in rows:
        key = (r['periodo'], r['nom_contrato_id'], r['prestacion'])
        assert key not in seen, ('Prestación duplicada', key)
        seen.add(key)
        param = parameters[r['prestacion']]
        r['cuenta_db_id'] = int(param['contab_cuenta_db_id'])
        r['cuenta_cr_id'] = int(param['contab_cuenta_cr_id'])
        r['cuenta_db'] = accounts[param['contab_cuenta_db_id']]['codigo']
        r['cuenta_cr'] = accounts[param['contab_cuenta_cr_id']]['codigo']
        assert r['cuenta_db'].startswith('51053') and r['cuenta_cr'].startswith('2610')
    # No se autoriza reemplazar asientos existentes de provisión.
    expense_ids = {r['id'] for r in source['cuentas'] if r['codigo'].startswith(('51053', '616005'))}
    assert not [r for r in source['previos'] if r['contab_cuenta_id'] in expense_ids
                and '2026-01-01' <= r['fecha'] <= '2026-05-31']
    assert not source['procesos']
    totals = defaultdict(lambda: defaultdict(int))
    for r in rows:
        totals[r['periodo']][r['prestacion']] += r['valor']
    plan = {'database': 'u883985631_colegiobilingu',
            'marca': 'Regularizacion provisiones nomina enero-mayo 2026',
            'source_sha256': hashlib.sha256((folder / 'fuente.json').read_bytes()).hexdigest(),
            'totales': dict(totals), 'filas_soporte': len(rows),
            'movimientos': sum(2 for r in rows if r['valor']),
            'vacaciones_cero_reconstruidas': zero_vacations, 'filas': rows}
    (folder / 'plan.json').write_text(json.dumps(plan, ensure_ascii=False, indent=2) + '\n')
    fields = sorted({k for r in rows for k in r})
    with (folder / 'soporte.csv').open('w', newline='') as f:
        writer = csv.DictWriter(f, fields)
        writer.writeheader()
        writer.writerows(rows)
    print(json.dumps({k: v for k, v in plan.items() if k not in ['filas', 'vacaciones_cero_reconstruidas']}, indent=2))
    print('Vacaciones disfrutadas en cero reconstruidas:', len(zero_vacations))


if __name__ == '__main__':
    prepare(sys.argv[1])
