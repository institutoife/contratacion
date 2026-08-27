@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/applicants-index.css') }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $activeFilterCount = collect([
            $filters['search'] ?? ($filters['q'] ?? ''),
            $filters['position_id'] ?? '',
            $filters['status'] ?? '',
            $filters['overall_rating'] ?? '',
            $filters['availability_immediate'] ?? '',
            $filters['date_from'] ?? '',
            $filters['date_to'] ?? '',
        ])->filter(fn ($value) => $value !== null && $value !== '')->count();
        $activePositions = $positions->where('is_active', true)->count();
    @endphp

    <div class="applicants-page">
        <header class="page-head">
            <div>
                <h1 class="page-title">Postulantes</h1>
                <p class="page-subtitle">Seguimiento del proceso de contratación y entrevistas.</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('applicants.export', request()->query()) }}" class="btn btn-outline-secondary">Exportar CSV</a>
                <a href="{{ route('applicants.position-report') }}" class="btn btn-outline-primary" target="_blank" rel="noopener">Reporte por cargos</a>
                <a href="{{ route('applicants.create') }}" class="btn btn-primary">Nuevo postulante</a>
            </div>
        </header>

        <section class="overview-strip" aria-label="Resumen del panel">
            <div class="overview-item">
                <span class="overview-value">{{ number_format($applicants->total()) }}</span>
                <span class="overview-label">Resultados actuales</span>
            </div>
            <div class="overview-item">
                <span class="overview-value">{{ number_format($activePositions) }}</span>
                <span class="overview-label">Cargos activos</span>
            </div>
            <div class="overview-item">
                <span class="overview-value">{{ number_format($interviewSlots->total()) }}</span>
                <span class="overview-label">Horarios registrados</span>
            </div>
        </section>

        <section class="workspace-section" aria-labelledby="filters-title">
            <div class="section-bar">
                <h2 id="filters-title">Buscar y filtrar</h2>
                @if($activeFilterCount > 0)
                    <span class="active-filter-count">{{ $activeFilterCount }} {{ $activeFilterCount === 1 ? 'filtro activo' : 'filtros activos' }}</span>
                @endif
            </div>
            <form class="filter-area" method="GET" action="{{ route('applicants.index') }}">
                <div class="filter-grid">
                    <div class="filter-field">
                        <label for="filter-search">Nombre o teléfono</label>
                        <input id="filter-search" type="search" class="form-control" name="search" value="{{ $filters['search'] ?? ($filters['q'] ?? '') }}" placeholder="Buscar postulante">
                    </div>
                    <div class="filter-field">
                        <label for="filter-position">Cargo</label>
                        <select id="filter-position" class="form-select" name="position_id">
                            <option value="">Todos los cargos</option>
                            @foreach($positions as $position)
                                <option value="{{ $position->id }}" @selected(($filters['position_id'] ?? '') == $position->id)>{{ $position->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-field">
                        <label for="filter-status">Estado</label>
                        <select id="filter-status" class="form-select" name="status">
                            <option value="">Todos los estados</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-field">
                        <label for="filter-rating">Valoración</label>
                        <select id="filter-rating" class="form-select" name="overall_rating">
                            <option value="">Todas</option>
                            @foreach($ratings as $rating)
                                <option value="{{ $rating }}" @selected((string) ($filters['overall_rating'] ?? '') === (string) $rating)>{{ $rating }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-field">
                        <label for="filter-availability">Disponibilidad</label>
                        <select id="filter-availability" class="form-select" name="availability_immediate">
                            <option value="">Todas</option>
                            <option value="1" @selected(($filters['availability_immediate'] ?? '') === '1')>Inmediata</option>
                            <option value="0" @selected(($filters['availability_immediate'] ?? '') === '0')>No inmediata</option>
                        </select>
                    </div>
                </div>
                <div class="date-filter">
                    <div class="filter-field">
                        <label for="filter-date-from">Postulación desde</label>
                        <input id="filter-date-from" type="date" class="form-control" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="filter-field">
                        <label for="filter-date-to">Postulación hasta</label>
                        <input id="filter-date-to" type="date" class="form-control" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="filter-actions">
                        <a href="{{ route('applicants.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                        <button type="submit" class="btn btn-primary">Aplicar filtros</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="workspace-section" aria-labelledby="list-title">
            <div class="section-bar">
                <h2 id="list-title">Listado de postulantes</h2>
                <span class="section-meta">Página {{ $applicants->currentPage() }} de {{ $applicants->lastPage() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover applicants-table">
                    <thead>
                        <tr>
                            <th>Postulante</th>
                            <th>Contacto</th>
                            <th>Cargo</th>
                            <th>Estado y valoración</th>
                            <th>Postulación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applicants as $applicant)
                            <tr>
                                <td>
                                    <span class="applicant-name">{{ $applicant->full_name }}</span>
                                    <span class="cell-secondary">ID {{ $applicant->id }}</span>
                                </td>
                                <td>
                                    <div>{{ $applicant->primary_phone ?: '-' }}</div>
                                    <div class="cell-secondary">{{ $applicant->email ?: 'Sin correo registrado' }}</div>
                                </td>
                                <td>{{ $applicant->position?->name ?: 'Sin cargo' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('applicants.quick-update', $applicant) }}" class="quick-update">
                                        @csrf
                                        @method('PATCH')
                                        <label class="visually-hidden" for="status-{{ $applicant->id }}">Estado</label>
                                        <select id="status-{{ $applicant->id }}" name="status" class="form-select form-select-sm">
                                            @foreach($statuses as $status)
                                                <option value="{{ $status }}" @selected($applicant->status === $status)>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        <label class="visually-hidden" for="rating-{{ $applicant->id }}">Valoración</label>
                                        <select id="rating-{{ $applicant->id }}" name="overall_rating" class="form-select form-select-sm">
                                            <option value="">-</option>
                                            @foreach($ratings as $rating)
                                                <option value="{{ $rating }}" @selected((string) $applicant->overall_rating === (string) $rating)>{{ $rating }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-outline-primary">Guardar</button>
                                    </form>
                                </td>
                                <td>
                                    <div>{{ optional($applicant->application_date)->format('d/m/Y') ?: '-' }}</div>
                                    <div class="cell-secondary">{{ $applicant->availability_immediate ? 'Disponibilidad inmediata' : 'Disponibilidad no inmediata' }}</div>
                                </td>
                                <td>
                                    @php
                                        $waRawPhone = $applicant->whatsapp ?: $applicant->primary_phone;
                                        $waPhone = $waRawPhone ? preg_replace('/\D+/', '', $waRawPhone) : null;
                                        $waMessage = rawurlencode('Hola ' . ($applicant->full_name ?: '') . ', te escribimos de ITE. Nos dejaste tus datos en nuestro sistema de postulaciones y queremos continuar con tu proceso.');
                                        $waUrl = $waPhone ? 'https://wa.me/' . $waPhone . '?text=' . $waMessage : 'https://web.whatsapp.com/';
                                    @endphp
                                    <div class="row-actions">
                                        <a href="{{ route('applicants.show', $applicant) }}" class="btn btn-sm btn-primary">Ver ficha</a>
                                        <a href="{{ route('applicants.edit', $applicant) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                        <a href="{{ route('applicants.print', $applicant) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">PDF</a>
                                        <a href="{{ $waUrl }}" class="btn btn-sm btn-outline-success" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                                        <form method="POST" action="{{ route('applicants.destroy', $applicant) }}" onsubmit="return confirm('¿Eliminar este postulante?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-row">No se encontraron postulantes con los filtros actuales.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($applicants->hasPages())
                <div class="table-footer">{{ $applicants->links() }}</div>
            @endif
        </section>

        <div class="configuration-head">
            <h2>Configuración</h2>
            <p>Administración de cargos y horarios disponibles para entrevistas.</p>
        </div>

        <details class="management-panel" @if($errors->has('name')) open @endif>
            <summary>
                <span>Cargos</span>
                <span class="summary-count">{{ $positions->count() }} registrados</span>
            </summary>
            <div class="management-body">
                <form class="management-form" method="POST" action="{{ route('positions.store') }}">
                    @csrf
                    <div>
                        <label for="new-position">Nuevo cargo</label>
                        <input id="new-position" type="text" name="name" class="form-control" placeholder="Ejemplo: Analista de admisiones" required>
                    </div>
                    <button class="btn btn-primary">Agregar cargo</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm management-table">
                        <thead>
                            <tr><th>Cargo</th><th>Estado</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            @forelse($positions as $position)
                                <tr>
                                    <td>
                                        <form method="POST" action="{{ route('positions.update', $position) }}" class="position-editor">
                                            @csrf
                                            @method('PUT')
                                            <label class="visually-hidden" for="position-{{ $position->id }}">Nombre del cargo</label>
                                            <input id="position-{{ $position->id }}" type="text" name="name" class="form-control form-control-sm" value="{{ $position->name }}" required>
                                            <button class="btn btn-sm btn-outline-primary">Guardar</button>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="badge {{ $position->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $position->is_active ? 'Activo' : 'Inactivo' }}</span>
                                    </td>
                                    <td>
                                        <div class="management-actions">
                                            <form method="POST" action="{{ route('positions.toggle', $position) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm {{ $position->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">{{ $position->is_active ? 'Desactivar' : 'Activar' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('positions.destroy', $position) }}" onsubmit="return confirm('¿Eliminar este cargo?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="empty-row">No hay cargos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </details>

        <details class="management-panel" @if(request()->has('slots_page') || $errors->has('interview_date') || $errors->has('interview_time')) open @endif>
            <summary>
                <span>Horarios de entrevistas</span>
                <span class="summary-count">{{ $interviewSlots->total() }} registrados</span>
            </summary>
            <div class="management-body">
                <form class="management-form slot-form" method="POST" action="{{ route('interview-slots.store') }}">
                    @csrf
                    <div>
                        <label for="new-slot-date">Fecha</label>
                        <input id="new-slot-date" type="date" name="interview_date" class="form-control" required>
                    </div>
                    <div>
                        <label for="new-slot-time">Hora</label>
                        <input id="new-slot-time" type="time" name="interview_time" class="form-control" required>
                    </div>
                    <button class="btn btn-primary">Agregar horario</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm management-table">
                        <thead>
                            <tr><th>Fecha</th><th>Hora</th><th>Estado</th><th>Agendados</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            @forelse($interviewSlots as $slot)
                                <tr>
                                    <td>{{ $slot->interview_date->format('d/m/Y') }}</td>
                                    <td>{{ substr((string) $slot->interview_time, 0, 5) }}</td>
                                    <td>
                                        <span class="badge {{ $slot->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $slot->is_active ? 'Activo' : 'Inactivo' }}</span>
                                    </td>
                                    <td>{{ number_format((int) $slot->bookings_count) }}</td>
                                    <td>
                                        <div class="management-actions">
                                            <a href="{{ route('interview-slots.report', $slot) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">Reporte PDF</a>
                                            <form method="POST" action="{{ route('interview-slots.toggle', $slot) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm {{ $slot->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">{{ $slot->is_active ? 'Desactivar' : 'Activar' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('interview-slots.destroy', $slot) }}" onsubmit="return confirm('¿Eliminar este horario?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="empty-row">Aún no se registraron horarios de entrevista.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($interviewSlots->hasPages())
                    <div class="table-footer">{{ $interviewSlots->links() }}</div>
                @endif
            </div>
        </details>
    </div>
@endsection
