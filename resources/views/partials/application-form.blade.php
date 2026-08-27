@php
    $prefix = $prefix ?? 'apply';
    $showFieldErrors = old('form_source') === $prefix;
    $showSuccess = session('success') && session('form_source') === $prefix;
@endphp

@if($showSuccess)
    <div class="alert alert-success mb-3">
        <div class="fw-semibold">Guardado correctamente. Puedes imprimir tu registro desde aquí.</div>
        <div class="small mt-1">Puedes imprimir las veces que quieras desde esta página en la sección "Buscar mi registro".</div>
        @if(session('print_url'))
            <div class="mt-3">
                <a href="{{ session('print_url') }}" target="_blank" class="btn btn-success btn-lg fw-bold px-4">
                    Imprimir mi registro ahora
                </a>
            </div>
        @endif
    </div>
@endif

<form method="POST" action="{{ route('applications.store') }}" class="row g-3" novalidate>
    @csrf
    <input type="hidden" name="form_source" value="{{ $prefix }}">
    <div class="col-md-6">
        <label class="form-label" for="{{ $prefix }}_full_name">Nombre completo</label>
        <input type="text" id="{{ $prefix }}_full_name" name="full_name" class="form-control @if($showFieldErrors && $errors->has('full_name')) is-invalid @endif" value="{{ old('full_name') }}">
        @if($showFieldErrors)
            @error('full_name')
                <div class="invalid-feedback d-block text-danger">{{ $message }}</div>
            @enderror
        @endif
    </div>
    <div class="col-md-6">
        <label class="form-label" for="{{ $prefix }}_gender">Género</label>
        <select id="{{ $prefix }}_gender" name="gender" class="form-select @if($showFieldErrors && $errors->has('gender')) is-invalid @endif">
            <option value="">Seleccionar</option>
            <option value="hombre" @selected(old('gender') === 'hombre')>Hombre</option>
            <option value="mujer" @selected(old('gender') === 'mujer')>Mujer</option>
        </select>
        @if($showFieldErrors)
            @error('gender')
                <div class="invalid-feedback d-block text-danger">{{ $message }}</div>
            @enderror
        @endif
    </div>
    <div class="col-12">
        <label class="form-label" for="{{ $prefix }}_position_id">Cargo que deseas ocupar</label>
        <select id="{{ $prefix }}_position_id" name="position_id" class="form-select @if($showFieldErrors && $errors->has('position_id')) is-invalid @endif">
            <option value="">Seleccionar cargo</option>
            @forelse($positions as $position)
                <option value="{{ $position->id }}" @selected((string) old('position_id') === (string) $position->id)>
                    {{ $position->name }}
                </option>
            @empty
                <option value="" disabled>No hay cargos activos disponibles</option>
            @endforelse
        </select>
        @if($showFieldErrors)
            @error('position_id')
                <div class="invalid-feedback d-block text-danger">{{ $message }}</div>
            @enderror
        @endif
    </div>
    <div class="col-12">
        <label class="form-label" for="{{ $prefix }}_address">Dirección</label>
        <input type="text" id="{{ $prefix }}_address" name="address" class="form-control @if($showFieldErrors && $errors->has('address')) is-invalid @endif" value="{{ old('address') }}">
        @if($showFieldErrors)
            @error('address')
                <div class="invalid-feedback d-block text-danger">{{ $message }}</div>
            @enderror
        @endif
    </div>
    <div class="col-md-6">
        <label class="form-label" for="{{ $prefix }}_primary_phone">Teléfono</label>
        <input type="text" id="{{ $prefix }}_primary_phone" name="primary_phone" class="form-control @if($showFieldErrors && $errors->has('primary_phone')) is-invalid @endif" value="{{ old('primary_phone') }}">
        @if($showFieldErrors)
            @error('primary_phone')
                <div class="invalid-feedback d-block text-danger">{{ $message }}</div>
            @enderror
        @endif
    </div>
    <div class="col-md-6">
        <label class="form-label" for="{{ $prefix }}_reference_phone">Teléfono de referencia</label>
        <input type="text" id="{{ $prefix }}_reference_phone" name="reference_phone" class="form-control @if($showFieldErrors && $errors->has('reference_phone')) is-invalid @endif" value="{{ old('reference_phone') }}">
        @if($showFieldErrors)
            @error('reference_phone')
                <div class="invalid-feedback d-block text-danger">{{ $message }}</div>
            @enderror
        @endif
    </div>
    <div class="col-12">
        <label class="form-label" for="{{ $prefix }}_motivation_text">¿Por qué te gustaría trabajar con nosotros?</label>
        <textarea id="{{ $prefix }}_motivation_text" name="motivation_text" class="form-control @if($showFieldErrors && $errors->has('motivation_text')) is-invalid @endif" rows="4">{{ old('motivation_text') }}</textarea>
        @if($showFieldErrors)
            @error('motivation_text')
                <div class="invalid-feedback d-block text-danger">{{ $message }}</div>
            @enderror
        @endif
    </div>
    <div class="col-12">
        <label class="form-label" for="{{ $prefix }}_interview_slot">Fecha y hora de entrevista</label>
        <select id="{{ $prefix }}_interview_slot" name="interview_slot_id" class="form-select @if($showFieldErrors && $errors->has('interview_slot_id')) is-invalid @endif">
            <option value="">Seleccionar fecha y hora</option>
            @forelse($slots as $slot)
                <option value="{{ $slot->id }}" @selected((string) old('interview_slot_id') === (string) $slot->id)>
                    {{ $slot->interview_date->format('d/m/Y') }} - {{ \Illuminate\Support\Str::of($slot->interview_time)->substr(0, 5) }}
                </option>
            @empty
                <option value="" disabled>No hay horarios disponibles por ahora</option>
            @endforelse
        </select>
        @if($showFieldErrors)
            @error('interview_slot_id')
                <div class="invalid-feedback d-block text-danger">{{ $message }}</div>
            @enderror
        @endif
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary w-100">Enviar registro</button>
    </div>
</form>
