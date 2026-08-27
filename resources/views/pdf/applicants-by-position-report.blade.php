<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de postulantes por cargo</title>
    <style>
        @page { margin: 26px 30px 42px; }
        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: #253238;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 8.5px;
            line-height: 1.35;
        }

        .report-header {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
            border-bottom: 2px solid #25b8a3;
        }

        .report-header td {
            padding: 0 0 12px;
            border: 0;
            vertical-align: middle;
        }

        .brand-cell { width: 150px; }
        .logo-frame { position: relative; width: 126px; height: 60px; overflow: hidden; }
        .logo { position: absolute; top: -32px; left: -2px; width: 410px; height: auto; }
        .header-copy { padding-left: 14px !important; }

        .document-type {
            margin-bottom: 3px;
            color: #37647d;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: .7px;
            text-transform: uppercase;
        }

        .title {
            margin: 0 0 3px;
            color: #17252b;
            font-size: 19px;
            font-weight: 700;
            line-height: 1.15;
        }

        .subtitle { color: #65747b; font-size: 9px; }

        .report-meta {
            width: 155px;
            color: #65747b;
            font-size: 8px;
            line-height: 1.55;
            text-align: right;
        }

        .report-meta strong { color: #253238; }

        .summary-table {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }

        .summary-table td {
            width: 33.333%;
            padding: 9px 11px;
            border: 1px solid #d9e2e5;
            background: #f7f9f9;
            vertical-align: middle;
        }

        .summary-table td:first-child { border-left: 4px solid #25b8a3; }

        .summary-number {
            display: inline-block;
            margin-right: 7px;
            color: #37647d;
            font-size: 17px;
            font-weight: 700;
            vertical-align: middle;
        }

        .summary-label {
            display: inline-block;
            color: #526168;
            font-size: 7.5px;
            line-height: 1.25;
            vertical-align: middle;
        }

        .priority-note {
            margin-bottom: 12px;
            padding: 7px 9px;
            border-left: 4px solid #37647d;
            background: #edf2f4;
            color: #40535c;
            font-size: 8px;
        }

        .position-section { margin-top: 12px; }

        .position-heading {
            width: 100%;
            padding: 7px 9px;
            border-left: 4px solid #37647d;
            background: #edf2f4;
            color: #1d3039;
            font-size: 10px;
            font-weight: 700;
            page-break-after: avoid;
        }

        .position-count {
            float: right;
            color: #64757d;
            font-size: 8px;
            font-weight: 400;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }

        .data-table th {
            padding: 6px 5px;
            border: 1px solid #ccd8dc;
            background: #37647d;
            color: #fff;
            font-size: 7px;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
        }

        .data-table td {
            min-height: 28px;
            padding: 7px 5px;
            border: 1px solid #d9e2e5;
            vertical-align: middle;
        }

        .data-table tbody tr:nth-child(even) td { background: #f8fafb; }
        .col-number { width: 4%; text-align: center !important; }
        .col-registered { width: 13%; }
        .col-name { width: 22%; }
        .col-phone { width: 12%; }
        .col-interview { width: 15%; }
        .col-status { width: 14%; }
        .col-attendance { width: 9%; }
        .col-notes { width: 11%; }

        .queue-number {
            display: inline-block;
            min-width: 18px;
            padding: 2px 3px;
            background: #25b8a3;
            color: #fff;
            font-weight: 700;
            text-align: center;
        }

        .status {
            display: inline-block;
            padding: 2px 5px;
            border: 1px solid #cbd7db;
            color: #40525a;
            font-size: 7px;
            font-weight: 700;
        }

        .blank-line { display: block; height: 13px; border-bottom: 1px solid #9aabb2; }

        .empty-state {
            padding: 24px;
            border: 1px solid #d9e2e5;
            color: #65747b;
            text-align: center;
        }

        .footer {
            position: fixed;
            right: 0;
            bottom: -27px;
            left: 0;
            padding-top: 7px;
            border-top: 1px solid #d9e2e5;
            color: #7a888e;
            font-size: 7px;
        }

        .footer-page { float: right; }
        .page-number::after { content: counter(page); }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo-ife-educabol-ofical-instituto-de-formacion-educabol.png');
        $logoData = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;
        $reportCode = 'IFE-RC-' . now()->format('Ymd-His');
    @endphp

    <div class="footer">
        Documento interno - IFE Educabol
        <span class="footer-page">Página <span class="page-number"></span></span>
    </div>

    <table class="report-header">
        <tr>
            <td class="brand-cell">
                @if($logoData)
                    <div class="logo-frame">
                        <img src="{{ $logoData }}" alt="IFE Educabol" class="logo">
                    </div>
                @else
                    <strong>IFE EDUCABOL</strong>
                @endif
            </td>
            <td class="header-copy">
                <div class="document-type">Gestión de talento humano</div>
                <h1 class="title">Postulantes por cargo</h1>
                <div class="subtitle">Orden de atención para entrevistas</div>
            </td>
            <td class="report-meta">
                <strong>{{ $reportCode }}</strong><br>
                Generado: {{ now()->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

    <table class="summary-table">
        <tr>
            <td>
                <span class="summary-number">{{ number_format($totalApplicants) }}</span>
                <span class="summary-label">POSTULANTES<br>REGISTRADOS</span>
            </td>
            <td>
                <span class="summary-number">{{ number_format($totalPositions) }}</span>
                <span class="summary-label">CARGOS CON<br>POSTULANTES</span>
            </td>
            <td>
                <span class="summary-number">1</span>
                <span class="summary-label">PRIORIDAD MÁS<br>ALTA POR CARGO</span>
            </td>
        </tr>
    </table>

    <div class="priority-note">
        Criterio de prioridad: dentro de cada cargo, el primer postulante registrado es el primero en ser entrevistado.
    </div>

    @forelse($groupedByPosition as $positionName => $items)
        <div class="position-section">
            <div class="position-heading">
                {{ $positionName }}
                <span class="position-count">{{ $items->count() }} {{ $items->count() === 1 ? 'postulante' : 'postulantes' }}</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-number">N.º</th>
                        <th class="col-registered">Registro</th>
                        <th class="col-name">Postulante</th>
                        <th class="col-phone">Teléfono</th>
                        <th class="col-interview">Entrevista programada</th>
                        <th class="col-status">Estado</th>
                        <th class="col-attendance">Asistencia</th>
                        <th class="col-notes">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $applicant)
                        @php $scheduledInterview = $applicant->interviews->first(); @endphp
                        <tr>
                            <td class="col-number"><span class="queue-number">{{ $index + 1 }}</span></td>
                            <td class="col-registered">{{ $applicant->created_at->format('d/m/Y H:i') }}</td>
                            <td class="col-name"><strong>{{ $applicant->full_name }}</strong></td>
                            <td class="col-phone">{{ $applicant->primary_phone ?: '-' }}</td>
                            <td class="col-interview">
                                {{ $scheduledInterview ? optional($scheduledInterview->interview_date)->format('d/m/Y') : '-' }}
                                @if($scheduledInterview?->interview_time)
                                    {{ substr((string) $scheduledInterview->interview_time, 0, 5) }}
                                @endif
                            </td>
                            <td class="col-status"><span class="status">{{ $applicant->status ?: '-' }}</span></td>
                            <td class="col-attendance"><span class="blank-line"></span></td>
                            <td class="col-notes"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="empty-state">No hay postulantes registrados.</div>
    @endforelse
</body>
</html>
