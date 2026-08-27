<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ApplicantAttachment;
use App\Models\ApplicantInterview;
use App\Models\InterviewSlot;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ApplicantController extends Controller
{
    public function index(Request $request)
    {
        $query = Applicant::query()->with('position')->latest();
        $this->applyFilters($query, $request);

        $interviewSlots = InterviewSlot::query()
            ->addSelect([
                'bookings_count' => ApplicantInterview::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('interview_date', 'interview_slots.interview_date')
                    ->whereColumn('interview_time', 'interview_slots.interview_time'),
            ])
            ->orderByDesc('interview_date')
            ->orderByDesc('interview_time')
            ->paginate(10, ['*'], 'slots_page')
            ->withQueryString();

        return view('applicants.index', [
            'applicants' => $query->paginate(15)->withQueryString(),
            'positions' => Position::query()->orderBy('name')->get(),
            'interviewSlots' => $interviewSlots,
            'statuses' => Applicant::STATUSES,
            'ratings' => [1, 2, 3, 4, 5],
            'filters' => $request->all(),
        ]);
    }

    public function create()
    {
        return view('applicants.create', [
            'applicant' => new Applicant(),
            'positions' => Position::query()->orderBy('name')->get(),
            'statuses' => Applicant::STATUSES,
            'modalities' => Applicant::WORK_MODALITIES,
            'ratings' => [1, 2, 3, 4, 5],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateApplicant($request);
        $applicant = Applicant::query()->create($data);

        $this->handleDocumentUploads($request, $applicant);
        $this->recordHistory($applicant, 'Creacion', null, 'Registro de postulante creado');

        return redirect()
            ->route('applicants.show', $applicant)
            ->with('success', 'Postulante registrado correctamente.');
    }

    public function show(Applicant $applicant)
    {
        $applicant->load([
            'position',
            'attachments',
            'interviews' => fn ($q) => $q->latest('interview_date')->latest('interview_time'),
            'histories' => fn ($q) => $q->latest(),
        ]);

        return view('applicants.show', [
            'applicant' => $applicant,
            'statuses' => Applicant::STATUSES,
            'ratings' => [1, 2, 3, 4, 5],
        ]);
    }

    public function printProfile(Applicant $applicant): HttpResponse
    {
        $applicant->load(['position']);

        $scheduledInterview = $applicant->interviews()
            ->whereDate('interview_date', '>=', now()->toDateString())
            ->orderBy('interview_date')
            ->orderBy('interview_time')
            ->first();

        if (!$scheduledInterview) {
            $scheduledInterview = $applicant->interviews()
                ->orderByDesc('interview_date')
                ->orderByDesc('interview_time')
                ->first();
        }

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.applicant-profile', [
                'applicant' => $applicant,
                'scheduledInterview' => $scheduledInterview,
            ])->setPaper('letter');

            $filename = 'postulante-' . $applicant->id . '.pdf';

            return $pdf->stream($filename);
        }

        return response()->view('applicants.print', [
            'applicant' => $applicant,
            'scheduledInterview' => $scheduledInterview,
        ]);
    }

    public function interviewSlotReportPdf(InterviewSlot $interviewSlot): HttpResponse
    {
        $applicants = Applicant::query()
            ->with('position')
            ->whereHas('interviews', function ($query) use ($interviewSlot): void {
                $query->whereDate('interview_date', $interviewSlot->interview_date)
                    ->where('interview_time', $interviewSlot->interview_time);
            })
            ->orderByDesc('created_at')
            ->get();

        $groupedByPosition = $applicants
            ->groupBy(fn (Applicant $applicant) => $applicant->position?->name ?: 'Sin cargo')
            ->map(function ($group) {
                return $group
                    ->unique(function (Applicant $applicant) {
                        $phone = preg_replace('/\D+/', '', (string) ($applicant->primary_phone ?? ''));
                        $name = mb_strtolower(trim((string) $applicant->full_name));

                        // Unique per position: same phone+name appears once in that position.
                        return ($phone !== '' ? $phone : 'no-phone') . '|' . $name;
                    })
                    ->values();
            });

        $totalUniqueApplicants = $groupedByPosition->sum(fn ($group) => $group->count());

        $payload = [
            'interviewSlot' => $interviewSlot,
            'applicants' => $applicants,
            'groupedByPosition' => $groupedByPosition,
            'totalUniqueApplicants' => $totalUniqueApplicants,
        ];

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.interview-slot-report', $payload)
                ->setPaper('letter', 'landscape');

            $filename = 'reporte-horario-' . $interviewSlot->id . '.pdf';

            return $pdf->stream($filename);
        }

        return response()->view('pdf.interview-slot-report', $payload);
    }

    public function positionReportPdf(): HttpResponse
    {
        $applicants = Applicant::query()
            ->with([
                'position',
                'interviews' => fn ($query) => $query
                    ->orderBy('interview_date')
                    ->orderBy('interview_time'),
            ])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $groupedByPosition = $applicants
            ->groupBy(fn (Applicant $applicant) => $applicant->position?->name ?: 'Sin cargo asignado')
            ->sortKeys(SORT_NATURAL | SORT_FLAG_CASE);

        $payload = [
            'applicants' => $applicants,
            'groupedByPosition' => $groupedByPosition,
            'totalApplicants' => $applicants->count(),
            'totalPositions' => $groupedByPosition->count(),
        ];

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.applicants-by-position-report', $payload)
                ->setPaper('letter', 'landscape');

            $filename = 'reporte-postulantes-por-cargo-' . now()->format('Ymd-His') . '.pdf';

            return $pdf->stream($filename);
        }

        return response()->view('pdf.applicants-by-position-report', $payload);
    }

    public function edit(Applicant $applicant)
    {
        $applicant->load('attachments');

        return view('applicants.edit', [
            'applicant' => $applicant,
            'positions' => Position::query()->orderBy('name')->get(),
            'statuses' => Applicant::STATUSES,
            'modalities' => Applicant::WORK_MODALITIES,
            'ratings' => [1, 2, 3, 4, 5],
        ]);
    }

    public function update(Request $request, Applicant $applicant)
    {
        $data = $this->validateApplicant($request);
        $original = $applicant->getOriginal();

        $applicant->fill($data);
        $dirty = $applicant->getDirty();
        $applicant->save();

        $this->handleDocumentUploads($request, $applicant);

        if (!empty($dirty)) {
            $changes = [];
            foreach ($dirty as $field => $newValue) {
                $changes[$field] = [
                    'old' => $original[$field] ?? null,
                    'new' => $newValue,
                ];
            }

            $this->recordHistory($applicant, 'Actualizacion', $changes, 'Datos actualizados');
        }

        return redirect()
            ->route('applicants.show', $applicant)
            ->with('success', 'Postulante actualizado correctamente.');
    }

    public function destroy(Applicant $applicant)
    {
        foreach ($applicant->attachments as $attachment) {
            Storage::disk('local')->delete($attachment->stored_path);
        }

        $applicant->delete();

        return redirect()
            ->route('applicants.index')
            ->with('success', 'Postulante eliminado correctamente.');
    }

    public function quickUpdate(Request $request, Applicant $applicant)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:' . implode(',', Applicant::STATUSES)],
            'overall_rating' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $originalStatus = $applicant->status;
        $originalRating = $applicant->overall_rating;

        $applicant->update($validated);

        $this->recordHistory($applicant, 'Actualizacion rapida', [
            'status' => ['old' => $originalStatus, 'new' => $applicant->status],
            'overall_rating' => ['old' => $originalRating, 'new' => $applicant->overall_rating],
        ], 'Actualizacion rapida desde panel');

        return back()->with('success', 'Cambios rapidos aplicados.');
    }

    public function storeInterview(Request $request, Applicant $applicant)
    {
        $validated = $request->validate([
            'interview_date' => ['required', 'date'],
            'interview_time' => ['nullable', 'date_format:H:i'],
            'interviewer_name' => ['nullable', 'string', 'max:255'],
            'result' => ['nullable', 'string'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'strengths' => ['nullable', 'string'],
            'weaknesses' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'recommended' => ['nullable', 'boolean'],
            'status_after_interview' => ['nullable', 'in:' . implode(',', Applicant::STATUSES)],
        ]);

        $interview = $applicant->interviews()->create($validated);

        $updates = [
            'last_interview_at' => $interview->interview_date . ' ' . ($interview->interview_time ?: '00:00:00'),
        ];

        if (!is_null($interview->rating)) {
            $updates['overall_rating'] = $interview->rating;
        }
        if (!is_null($interview->recommended)) {
            $updates['recommended'] = $interview->recommended;
        }
        if ($interview->status_after_interview) {
            $updates['status'] = $interview->status_after_interview;
        }

        $applicant->update($updates);

        $this->recordHistory($applicant, 'Entrevista', $validated, 'Se registro una nueva entrevista');

        return back()->with('success', 'Entrevista registrada correctamente.');
    }

    public function export(Request $request)
    {
        $query = Applicant::query()->with('position')->latest();
        $this->applyFilters($query, $request);
        $rows = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="postulantes.csv"',
        ];

        $callback = function () use ($rows): void {
            $file = fopen('php://output', 'wb');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, [
                'Nombre',
                'Cedula',
                'Telefono',
                'WhatsApp',
                'Correo',
                'Cargo',
                'Estado',
                'Valoracion',
                'Disponibilidad inmediata',
                'Fecha de postulacion',
                'Pretension salarial',
            ]);

            foreach ($rows as $applicant) {
                fputcsv($file, [
                    $applicant->full_name,
                    $applicant->identity_number,
                    $applicant->primary_phone,
                    $applicant->whatsapp,
                    $applicant->email,
                    $applicant->position?->name,
                    $applicant->status,
                    $applicant->overall_rating,
                    $applicant->availability_immediate ? 'Si' : 'No',
                    optional($applicant->application_date)->format('Y-m-d'),
                    $applicant->salary_expectation,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function downloadAttachment(ApplicantAttachment $attachment)
    {
        abort_unless(Storage::disk('local')->exists($attachment->stored_path), 404);

        return Storage::disk('local')->download($attachment->stored_path, $attachment->original_name);
    }

    public function destroyAttachment(ApplicantAttachment $attachment)
    {
        $applicant = $attachment->applicant;
        Storage::disk('local')->delete($attachment->stored_path);
        $attachment->delete();

        $this->recordHistory($applicant, 'Adjunto eliminado', [
            'file' => $attachment->original_name,
            'type' => $attachment->type,
        ], 'Se elimino un adjunto');

        return back()->with('success', 'Adjunto eliminado correctamente.');
    }

    public function togglePosition(Position $position)
    {
        $position->update([
            'is_active' => !$position->is_active,
        ]);

        return back()->with('success', 'Estado del cargo actualizado.');
    }

    public function storePosition(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:positions,name'],
        ]);

        Position::query()->create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Cargo creado correctamente.');
    }

    public function updatePosition(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('positions', 'name')->ignore($position->id),
            ],
        ]);

        $position->update([
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Cargo actualizado correctamente.');
    }

    public function destroyPosition(Position $position)
    {
        $position->delete();

        return back()->with('success', 'Cargo eliminado correctamente.');
    }

    private function validateApplicant(Request $request): array
    {
        return $request->validate([
            'position_id' => ['nullable', 'exists:positions,id'],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'in:hombre,mujer'],
            'motivation_text' => ['nullable', 'string'],
            'identity_number' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'age' => ['nullable', 'integer', 'between:14,100'],
            'address' => ['nullable', 'string'],
            'city_zone' => ['nullable', 'string', 'max:255'],
            'primary_phone' => ['nullable', 'string', 'max:100'],
            'whatsapp' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'reference_name' => ['nullable', 'string', 'max:255'],
            'reference_phone' => ['nullable', 'string', 'max:100'],
            'reference_relationship' => ['nullable', 'string', 'max:100'],
            'application_date' => ['nullable', 'date'],
            'work_modality' => ['nullable', 'in:' . implode(',', Applicant::WORK_MODALITIES)],
            'availability_schedule' => ['nullable', 'string', 'max:255'],
            'availability_immediate' => ['nullable', 'boolean'],
            'salary_expectation' => ['nullable', 'numeric', 'min:0'],
            'vacancy_source' => ['nullable', 'string', 'max:255'],
            'has_experience' => ['nullable', 'boolean'],
            'experience_years' => ['nullable', 'numeric', 'min:0', 'max:80'],
            'experience_summary' => ['nullable', 'string'],
            'education_level' => ['nullable', 'string', 'max:255'],
            'educational_institution' => ['nullable', 'string', 'max:255'],
            'courses_certifications' => ['nullable', 'string'],
            'main_skills' => ['nullable', 'string'],
            'portfolio_link' => ['nullable', 'url', 'max:255'],
            'status' => ['nullable', 'in:' . implode(',', Applicant::STATUSES)],
            'overall_rating' => ['nullable', 'integer', 'between:1,5'],
            'strengths' => ['nullable', 'string'],
            'weaknesses' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'recommended' => ['nullable', 'boolean'],
            'cv_file' => ['nullable', 'file', 'max:10240'],
            'photo_file' => ['nullable', 'image', 'max:10240'],
            'certificates.*' => ['nullable', 'file', 'max:10240'],
            'other_files.*' => ['nullable', 'file', 'max:10240'],
        ]);
    }

    private function handleDocumentUploads(Request $request, Applicant $applicant): void
    {
        $uploadMap = [
            'cv_file' => 'CV',
            'photo_file' => 'Foto',
        ];

        foreach ($uploadMap as $input => $type) {
            if (!$request->hasFile($input)) {
                continue;
            }
            $this->storeAttachment($applicant, $request->file($input), $type);
        }

        foreach ($request->file('certificates', []) as $file) {
            if ($file) {
                $this->storeAttachment($applicant, $file, 'Certificado');
            }
        }

        foreach ($request->file('other_files', []) as $file) {
            if ($file) {
                $this->storeAttachment($applicant, $file, 'Otro');
            }
        }
    }

    private function storeAttachment(Applicant $applicant, UploadedFile $file, string $type): void
    {
        $path = $file->store('applicants/' . $applicant->id, 'local');

        $attachment = $applicant->attachments()->create([
            'type' => $type,
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        $this->recordHistory($applicant, 'Adjunto', [
            'type' => $attachment->type,
            'file' => $attachment->original_name,
        ], 'Se adjunto un archivo');
    }

    private function recordHistory(Applicant $applicant, string $action, $changes = null, ?string $note = null): void
    {
        $applicant->histories()->create([
            'action' => $action,
            'performed_by' => auth()->user()->name ?? 'Sistema interno',
            'note' => $note,
            'changes' => $changes,
        ]);
    }

    private function applyFilters($query, Request $request): void
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));
        if ($search !== '') {
            $term = $search;
            $query->where(function ($q) use ($term) {
                $q->where('full_name', 'like', "%{$term}%")
                    ->orWhere('primary_phone', 'like', "%{$term}%")
                    ->orWhere('whatsapp', 'like', "%{$term}%");
            });
        }

        if ($request->filled('position_id')) {
            $query->where('position_id', $request->integer('position_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', (string) $request->string('status'));
        }
        if ($request->filled('overall_rating')) {
            $query->where('overall_rating', $request->integer('overall_rating'));
        }
        if ($request->filled('availability_immediate')) {
            $query->where('availability_immediate', $request->boolean('availability_immediate'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('application_date', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('application_date', '<=', $request->date('date_to'));
        }
    }
}
