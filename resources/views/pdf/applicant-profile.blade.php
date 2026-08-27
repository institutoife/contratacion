<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de postulante</title>
    <style>
        @page { margin: 26px 30px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; font-size: 11px; }
        .top { border: 1px solid #1f4b70; background: #eaf4ff; padding: 10px; margin-bottom: 10px; }
        .top-table { width: 100%; border-collapse: collapse; }
        .top-table td { border: none; vertical-align: middle; }
        .logo-cell { width: 130px; }
        .logo-frame { position: relative; width: 110px; height: 52px; overflow: hidden; }
        .logo { position: absolute; top: -28px; left: -2px; width: 360px; height: auto; }
        .title { font-size: 19px; font-weight: 700; }
        .muted { color: #315a7c; font-size: 10px; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .grid td { border: 1px solid #b6cfdf; padding: 6px; vertical-align: top; background: #f7fbff; }
        .section-title { background: #1f4b70; color: #ffffff; font-weight: 700; text-transform: uppercase; font-size: 10px; padding: 6px 8px; border: 1px solid #1f4b70; margin-top: 8px; }
        .kv { width: 100%; border-collapse: collapse; }
        .kv td { border: 1px solid #d8e6ef; padding: 6px; vertical-align: top; }
        .k { width: 28%; font-weight: 700; background: #eef6fc; color: #143a57; }
        .v { width: 72%; }
        .wa-wrap { margin: 8px 0 10px; }
        .wa-btn {
            display: inline-block;
            text-decoration: none;
            background: #25d366;
            color: #ffffff;
            border: 1px solid #1aa84f;
            border-radius: 6px;
            padding: 7px 10px;
            font-size: 10px;
            font-weight: 700;
        }
        .wa-icon {
            width: 10px;
            height: 10px;
            vertical-align: -1px;
            margin-right: 6px;
            fill: #ffffff;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo-ife-educabol-ofical-instituto-de-formacion-educabol.png');
        $logoData = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $waContactName = trim((string) ($applicant->full_name ?? ''));
        $waMessageText = $waContactName !== ''
            ? "Hola {$waContactName}, te escribimos de ITE. Vimos que nos dejaste tus datos en nuestro sistema de postulaciones y queremos continuar con tu proceso."
            : 'Hola, te escribimos de ITE. Vimos que nos dejaste tus datos en nuestro sistema de postulaciones y queremos continuar con tu proceso.';
        $waMessage = rawurlencode($waMessageText);
        $waRawPhone = $applicant->whatsapp ?: $applicant->primary_phone;
        $waPhone = $waRawPhone ? preg_replace('/\D+/', '', $waRawPhone) : null;
        $waApplicantUrl = $waPhone ? 'https://wa.me/' . $waPhone . '?text=' . $waMessage : null;
        $waAnyUrl = 'https://web.whatsapp.com/';
    @endphp

    <div class="top">
        <table class="top-table">
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
                    <div class="title">Ficha de Postulante</div>
                    <div class="muted">Generado el {{ now()->format('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="grid">
        <tr>
            <td>
                <strong>Nombre</strong><br>{{ $applicant->full_name }}
            </td>
            <td>
                <strong>Cargo</strong><br>{{ $applicant->position?->name ?: '-' }}
            </td>
            <td>
                <strong>Estado</strong><br>{{ $applicant->status ?: '-' }}
            </td>
            <td>
                <strong>Valoración</strong><br>{{ $applicant->overall_rating ?: '-' }}
            </td>
        </tr>
    </table>

    <div class="wa-wrap">
        <a href="{{ $waApplicantUrl ?: $waAnyUrl }}" class="wa-btn" target="_blank">
            <svg class="wa-icon" viewBox="0 0 32 32" aria-hidden="true">
                <path d="M19.11 17.21c-.26-.13-1.53-.75-1.77-.84-.24-.09-.41-.13-.59.13-.18.26-.67.84-.82 1.01-.15.18-.31.2-.57.07-.26-.13-1.09-.4-2.07-1.27-.77-.69-1.28-1.54-1.43-1.8-.15-.26-.02-.4.11-.53.12-.12.26-.31.39-.46.13-.15.18-.26.26-.44.09-.18.04-.33-.02-.46-.07-.13-.59-1.42-.81-1.95-.22-.52-.44-.45-.59-.45-.15-.01-.33-.01-.5-.01-.18 0-.46.07-.7.33-.24.26-.92.9-.92 2.2s.94 2.56 1.07 2.74c.13.18 1.85 2.82 4.49 3.95.63.27 1.12.43 1.5.55.63.2 1.2.17 1.65.1.5-.07 1.53-.63 1.75-1.24.22-.61.22-1.13.15-1.24-.06-.11-.24-.17-.5-.3M16 3C8.83 3 3 8.83 3 16c0 2.28.6 4.51 1.74 6.48L3 29l6.69-1.75A12.96 12.96 0 0 0 16 29c7.17 0 13-5.83 13-13S23.17 3 16 3m0 23.66c-2.07 0-4.1-.56-5.87-1.63l-.42-.25-3.97 1.04 1.06-3.87-.27-.4a10.63 10.63 0 0 1-1.66-5.57c0-5.88 4.79-10.66 10.67-10.66 2.85 0 5.53 1.11 7.55 3.12 2.01 2.02 3.12 4.7 3.12 7.55 0 5.88-4.78 10.67-10.66 10.67"/>
            </svg>
            {{ $waApplicantUrl ? 'Enviar WhatsApp al postulante' : 'Abrir WhatsApp (enviar a cualquier contacto)' }}
        </a>
    </div>

    <div class="section-title">Horario de entrevista</div>
    <table class="kv">
        <tr>
            <td class="k">Fecha</td>
            <td class="v">{{ $scheduledInterview ? optional($scheduledInterview->interview_date)->format('d/m/Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="k">Hora</td>
            <td class="v">{{ $scheduledInterview && $scheduledInterview->interview_time ? \Illuminate\Support\Str::of($scheduledInterview->interview_time)->substr(0, 5) : '-' }}</td>
        </tr>
        <tr>
            <td class="k">Entrevistador</td>
            <td class="v">{{ $scheduledInterview?->interviewer_name ?: '-' }}</td>
        </tr>
    </table>

    <div class="section-title">Datos personales y contacto</div>
    <table class="kv">
        <tr><td class="k">Cédula</td><td class="v">{{ $applicant->identity_number ?: '-' }}</td></tr>
        <tr><td class="k">Teléfono</td><td class="v">{{ $applicant->primary_phone ?: '-' }}</td></tr>
        <tr><td class="k">WhatsApp</td><td class="v">{{ $applicant->whatsapp ?: '-' }}</td></tr>
        <tr><td class="k">Correo</td><td class="v">{{ $applicant->email ?: '-' }}</td></tr>
        <tr><td class="k">Dirección</td><td class="v">{{ $applicant->address ?: '-' }}</td></tr>
        <tr><td class="k">Zona/Ciudad</td><td class="v">{{ $applicant->city_zone ?: '-' }}</td></tr>
    </table>

    <div class="section-title">Perfil profesional</div>
    <table class="kv">
        <tr><td class="k">Modalidad</td><td class="v">{{ $applicant->work_modality ?: '-' }}</td></tr>
        <tr><td class="k">Disponibilidad</td><td class="v">{{ $applicant->availability_schedule ?: '-' }}</td></tr>
        <tr><td class="k">Pretensión salarial</td><td class="v">{{ $applicant->salary_expectation ?: '-' }}</td></tr>
        <tr><td class="k">Nivel de estudios</td><td class="v">{{ $applicant->education_level ?: '-' }}</td></tr>
        <tr><td class="k">Experiencia</td><td class="v">{{ $applicant->experience_summary ?: '-' }}</td></tr>
        <tr><td class="k">Habilidades</td><td class="v">{{ $applicant->main_skills ?: '-' }}</td></tr>
        <tr><td class="k">Observaciones</td><td class="v">{{ $applicant->internal_notes ?: '-' }}</td></tr>
    </table>
</body>
</html>
