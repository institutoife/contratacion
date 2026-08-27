<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantInterview;
use App\Models\InterviewSlot;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ApplicantIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_load_the_applicant_workspace(): void
    {
        $user = User::factory()->create();
        $position = Position::query()->create([
            'name' => 'Docente',
            'is_active' => true,
        ]);
        $applicant = Applicant::query()->create([
            'position_id' => $position->id,
            'full_name' => 'María Salvatierra',
            'primary_phone' => '70012345',
            'application_date' => '2026-08-27',
            'status' => 'Nuevo',
        ]);
        InterviewSlot::query()->create([
            'interview_date' => '2026-09-15',
            'interview_time' => '09:30:00',
            'is_active' => true,
        ]);
        ApplicantInterview::query()->create([
            'applicant_id' => $applicant->id,
            'interview_date' => '2026-09-15',
            'interview_time' => '09:30:00',
        ]);

        $response = $this->actingAs($user)->get(route('applicants.index'));

        $response
            ->assertOk()
            ->assertSee('Listado de postulantes')
            ->assertSee('María Salvatierra')
            ->assertSee('Configuración')
            ->assertSee(route('applicants.position-report'))
            ->assertViewHas('interviewSlots', function ($slots): bool {
                return (int) $slots->first()->bookings_count === 1;
            });
    }

    public function test_position_report_orders_each_position_by_oldest_registration_first(): void
    {
        $user = User::factory()->create();
        $position = Position::query()->create([
            'name' => 'Docente',
            'is_active' => true,
        ]);

        $second = Applicant::query()->create([
            'position_id' => $position->id,
            'full_name' => 'Segundo registro',
            'status' => 'Nuevo',
        ]);
        $second->forceFill(['created_at' => '2026-08-27 10:00:00'])->saveQuietly();

        $first = Applicant::query()->create([
            'position_id' => $position->id,
            'full_name' => 'Primer registro',
            'status' => 'Nuevo',
        ]);
        $first->forceFill(['created_at' => '2026-08-27 09:00:00'])->saveQuietly();

        ApplicantInterview::query()->create([
            'applicant_id' => $first->id,
            'interview_date' => '2026-09-15',
            'interview_time' => '09:00:00',
        ]);
        ApplicantInterview::query()->create([
            'applicant_id' => $second->id,
            'interview_date' => '2026-09-15',
            'interview_time' => '09:30:00',
        ]);

        $differentDate = Applicant::query()->create([
            'position_id' => $position->id,
            'full_name' => 'Otra fecha',
            'status' => 'Nuevo',
        ]);
        ApplicantInterview::query()->create([
            'applicant_id' => $differentDate->id,
            'interview_date' => '2026-09-16',
            'interview_time' => '09:00:00',
        ]);

        $pdf = Mockery::mock();
        $pdf->shouldReceive('loadView')
            ->once()
            ->with('pdf.applicants-by-position-report', Mockery::on(function (array $payload): bool {
                $names = $payload['groupedByPosition']
                    ->get('Docente')
                    ->pluck('full_name')
                    ->all();

                return $names === ['Primer registro', 'Segundo registro']
                    && $payload['totalApplicants'] === 2
                    && $payload['interviewDate'] === '2026-09-15';
            }))
            ->andReturnSelf();
        $pdf->shouldReceive('setPaper')
            ->once()
            ->with('letter', 'landscape')
            ->andReturnSelf();
        $pdf->shouldReceive('stream')
            ->once()
            ->with(Mockery::pattern('/^reporte-postulantes-por-cargo-2026-09-15-\d{6}\.pdf$/'))
            ->andReturn(response('pdf', 200, ['Content-Type' => 'application/pdf']));
        $this->app->instance('dompdf.wrapper', $pdf);

        $response = $this->actingAs($user)->get(route('applicants.position-report', [
            'interview_date' => '2026-09-15',
        ]));

        $response->assertOk();
    }

    public function test_position_report_requires_an_interview_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('applicants.position-report'));

        $response
            ->assertRedirect()
            ->assertSessionHasErrors('interview_date');
    }
}
