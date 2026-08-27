<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Postula para formar parte del equipo de IFE Educabol y agenda tu entrevista.">
    <title>Trabaja con nosotros | IFE Educabol</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icono-ife-educabol.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/brand-theme.css') }}" rel="stylesheet">
    <link href="{{ asset('css/welcome.css') }}" rel="stylesheet">
</head>
<body>
    <header class="site-header">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="{{ route('welcome') }}" aria-label="IFE Educabol - Inicio">
                <img class="brand-logo" src="{{ asset('images/logo-ife-educabol-ofical-instituto-de-formacion-educabol.png') }}" alt="IFE Educabol">
            </a>
            <nav class="header-links" aria-label="Navegación principal">
                <a class="header-link" href="#buscar">Buscar registro</a>
                <a class="header-link" href="#ubicacion">Ubicación</a>
                @auth
                    <a class="header-link admin-link" href="{{ route('applicants.index') }}">Panel</a>
                @else
                    <a class="header-link admin-link" href="{{ route('login') }}">Acceso administrativo</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <section class="hero" aria-labelledby="hero-title">
            <div class="container">
                <div class="hero-copy">
                    <p class="eyebrow">Convocatoria abierta</p>
                    <h1 class="hero-title" id="hero-title">Trabaja con nosotros.</h1>
                    <p class="hero-text">Forma parte de IFE Educabol. Registra tus datos y elige una fecha disponible para tu entrevista.</p>
                    <div class="hero-actions">
                        <button class="btn-landing" type="button" data-open-apply>Postular ahora</button>
                        <a class="btn-landing btn-landing-outline" href="#buscar">Ya me registré</a>
                    </div>
                    @if($errors->any() && old('form_source'))
                        <div class="alert alert-app mt-4 mb-0">Revisa los campos marcados en el formulario.</div>
                    @endif
                </div>
            </div>
            <img class="hero-person" src="{{ asset('images/david-flores-ife-educabol-instituto-formacion-educabol.png') }}" alt="Representante de IFE Educabol invitando a postular">
            <div class="applicant-count" aria-label="Cantidad de postulantes registrados">
                <strong>{{ number_format($totalApplicants ?? 0) }}</strong>
                postulantes registrados
            </div>
        </section>

        <section class="content-section" id="buscar">
            <div class="container">
                <div class="section-heading">
                    <p class="section-kicker">Seguimiento</p>
                    <h2 class="section-title">Encuentra tu registro</h2>
                    <p class="section-copy">Ingresa el número de teléfono con el que realizaste tu postulación.</p>
                </div>
                <div class="search-panel">
                    <form method="GET" action="{{ route('welcome') }}" class="row g-2">
                        <div class="col-md-9">
                            <label class="visually-hidden" for="search-phone">Número de teléfono</label>
                            <input id="search-phone" type="tel" inputmode="numeric" pattern="[0-9]+" name="search" class="form-control search-control @error('search') is-invalid @enderror" value="{{ $searchTerm ?? '' }}" placeholder="Número de teléfono">
                            @error('search')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn-landing w-100">Buscar registro</button>
                        </div>
                    </form>

                    @if(!empty($searchTerm))
                        <div class="table-responsive results-table">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr><th>Nombre</th><th>Teléfono</th><th>Cargo</th><th>Fecha</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @forelse($searchResults as $result)
                                        <tr>
                                            <td>{{ $result->full_name }}</td>
                                            <td>{{ $result->primary_phone ?: '-' }}</td>
                                            <td>{{ $result->position?->name ?: '-' }}</td>
                                            <td>{{ optional($result->application_date)->format('d/m/Y') ?: '-' }}</td>
                                            <td class="text-end"><a href="{{ $result->public_print_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Imprimir</a></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="py-4 text-center text-body-secondary">No encontramos registros con ese número.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="content-section content-section-soft" id="fixed-apply">
            <div class="container">
                <div class="section-heading">
                    <p class="section-kicker">Postulación</p>
                    <h2 class="section-title">Agenda tu entrevista</h2>
                    <p class="section-copy">Completa tus datos y selecciona uno de los horarios disponibles.</p>
                </div>
                <div class="application-shell">
                    @include('partials.application-form', ['prefix' => 'fixed'])
                </div>
            </div>
        </section>

        <section class="content-section" id="ubicacion">
            <div class="container location-grid">
                <div>
                    <img class="location-mark" src="{{ asset('images/isologo-ife-educabol-ofical-instituto-de-formacion-educabol.png') }}" alt="">
                    <p class="section-kicker">Santa Cruz</p>
                    <h2 class="section-title">Nuestra ubicación</h2>
                    <p class="section-copy">IFE Educabol, oficina central.</p>
                </div>
                <iframe class="map-frame" title="Ubicación de IFE Educabol en Santa Cruz" src="https://www.google.com/maps?q=-17.8020169,-63.136261&z=16&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container d-flex align-items-center justify-content-between gap-3">
            <img class="footer-logo" src="{{ asset('images/logo-ife-educabol-ofical-instituto-de-formacion-educabol.png') }}" alt="IFE Educabol">
            <span>Proceso de contratación</span>
        </div>
    </footer>

    <div class="modal-backdrop-custom" id="applyModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="custom-modal">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                <h2 class="h4 mb-0" id="modal-title">Postula a IFE Educabol</h2>
                <button type="button" class="btn btn-sm btn-outline-primary" data-close-apply>Cerrar</button>
            </div>
            @include('partials.application-form', ['prefix' => 'modal'])
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('applyModal');
            const openButtons = document.querySelectorAll('[data-open-apply]');
            const closeButtons = document.querySelectorAll('[data-close-apply]');
            const hasModalErrors = @json($errors->any() && old('form_source') === 'modal');
            const hasModalSuccess = @json(session('success') && session('form_source') === 'modal');

            function openModal() {
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }
            function closeModal() {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }
            openButtons.forEach((button) => button.addEventListener('click', openModal));
            closeButtons.forEach((button) => button.addEventListener('click', closeModal));
            modal.addEventListener('click', (event) => {
                if (event.target === modal) closeModal();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.classList.contains('open')) closeModal();
            });
            if (hasModalErrors || hasModalSuccess) openModal();
        })();
    </script>
</body>
</html>
