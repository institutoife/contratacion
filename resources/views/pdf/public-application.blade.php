<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de postulación</title>
    <style>
        @page { margin: 30px 32px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #1f2937; font-size: 12px; }
        .header { border: 1px solid #1f4b70; background: #eaf4ff; padding: 12px; margin-bottom: 12px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { border: none; vertical-align: middle; }
        .logo-cell { width: 130px; }
        .logo-frame { position: relative; width: 110px; height: 52px; overflow: hidden; }
        .logo { position: absolute; top: -28px; left: -2px; width: 360px; height: auto; }
        .title { font-size: 18px; font-weight: 700; margin: 0 0 4px; }
        .subtitle { color: #315a7c; font-size: 11px; }
        .section { border: 1px solid #b6cfdf; margin-top: 10px; }
        .section-title { background: #1f4b70; color: #fff; font-weight: 700; padding: 8px 10px; font-size: 11px; text-transform: uppercase; }
        .section-body { padding: 10px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 5px 4px; vertical-align: top; border-bottom: 1px solid #d8e6ef; }
        .label { font-weight: 700; width: 35%; color: #143a57; }
        .value { width: 65%; color: #374151; }
        .footer { margin-top: 14px; font-size: 10px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo-ife-educabol-ofical-instituto-de-formacion-educabol.png');
        $logoData = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    @if($logoData)
                        <div class="logo-frame">
                            <img src="{{ $logoData }}" alt="IFE Educabol" class="logo">
                        </div>
                    @else
                        <strong>LOGO</strong>
                    @endif
                </td>
                <td>
                    <div class="title">Comprobante de Registro de Postulación</div>
                    <div class="subtitle">Generado el {{ now()->format('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Horario Programado</div>
        <div class="section-body">
            <table>
                <tr>
                    <td class="label">Fecha de entrevista</td>
                    <td class="value">{{ $scheduledInterview ? optional($scheduledInterview->interview_date)->format('d/m/Y') : 'No definido' }}</td>
                </tr>
                <tr>
                    <td class="label">Hora</td>
                    <td class="value">{{ $scheduledInterview && $scheduledInterview->interview_time ? \Illuminate\Support\Str::of($scheduledInterview->interview_time)->substr(0, 5) : 'No definida' }}</td>
                </tr>
                <tr>
                    <td class="label">Estado</td>
                    <td class="value">{{ $applicant->status ?: '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Datos Registrados</div>
        <div class="section-body">
            <table>
                <tr>
                    <td class="label">Nombre completo</td>
                    <td class="value">{{ $applicant->full_name }}</td>
                </tr>
                <tr>
                    <td class="label">Teléfono</td>
                    <td class="value">{{ $applicant->primary_phone ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Cargo postulado</td>
                    <td class="value">{{ $applicant->position?->name ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Dirección</td>
                    <td class="value">{{ $applicant->address ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Teléfono de referencia</td>
                    <td class="value">{{ $applicant->reference_phone ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Motivación</td>
                    <td class="value">{{ $applicant->motivation_text ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Fecha de registro</td>
                    <td class="value">{{ optional($applicant->application_date)->format('d/m/Y') ?: '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        Documento de uso informativo. Preséntalo el día de tu entrevista.
    </div>
</body>
</html>
