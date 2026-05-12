<?php

namespace App\Imports;

use App\Mail\AgentWelcomeMail;
use App\Models\Agent;
use App\Models\CashWalletPoints;
use App\Models\PackageWalletPoints;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Bulk import of agents from an Excel/CSV sheet.
 *
 * Expected headers (Arabic-friendly aliases handled internally):
 *   full_name | email | phone | external_agent_id | business_name |
 *   license_number | country | city | current_tier
 *
 * Each row is validated independently; valid rows are imported, invalid
 * ones are collected into $errors with a row number + reason.
 *
 * The caller (AgentController::import) is responsible for showing
 * results back to the admin and triggering welcome emails.
 */
class AgentsImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public int $created = 0;

    /** @var array<int, array{row:int, errors:array<string>, data:array}> */
    public array $errors = [];

    public function __construct(private AuditService $audit) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // header is row 1

            $data = $this->normalize($row->toArray());

            $validator = Validator::make($data, [
                'full_name'         => ['required', 'string', 'max:255'],
                'email'             => ['required', 'email', 'unique:users,email'],
                'phone'             => ['nullable', 'string', 'max:30'],
                'external_agent_id' => ['required', 'string', 'max:50', 'unique:agents,external_agent_id'],
                'business_name'     => ['required', 'string', 'max:255'],
                'license_number'    => ['required', 'string', 'max:100', 'unique:agents,license_number'],
                'country'           => ['required', 'string', 'max:100'],
                'city'              => ['nullable', 'string', 'max:100'],
                'current_tier'      => ['nullable', 'in:bronze,silver,gold,diamond'],
            ]);

            if ($validator->fails()) {
                $this->errors[] = [
                    'row'    => $rowNumber,
                    'errors' => $validator->errors()->all(),
                    'data'   => $data,
                ];
                continue;
            }

            try {
                $agent = $this->createAgent($validator->validated());
                $this->sendWelcomeMail($agent);
                $this->created++;
            } catch (\Throwable $e) {
                $this->errors[] = [
                    'row'    => $rowNumber,
                    'errors' => [$e->getMessage()],
                    'data'   => $data,
                ];
            }
        }
    }

    private function createAgent(array $data): Agent
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'role'      => 'agent',
                'email'     => $data['email'],
                'password'  => Hash::make(Str::random(40)),
                'full_name' => $data['full_name'],
                'phone'     => $data['phone'] ?? null,
                'status'    => 'active',
            ]);

            $agent = Agent::create([
                'user_id'           => $user->id,
                'external_agent_id' => $data['external_agent_id'],
                'business_name'     => $data['business_name'],
                'license_number'    => $data['license_number'],
                'country'           => $data['country'],
                'city'              => $data['city'] ?? null,
                'current_tier'      => $data['current_tier'] ?? 'bronze',
                'tier_valid_until'  => now()->addDays(30),
            ]);

            CashWalletPoints::create(['agent_id' => $agent->id]);
            PackageWalletPoints::create(['agent_id' => $agent->id]);

            $this->audit->log(
                action: 'agent_created',
                entityType: Agent::class,
                entityId: (string) $agent->id,
                newValues: ['via' => 'excel_import'] + $agent->getAttributes(),
            );

            return $agent;
        });
    }

    private function sendWelcomeMail(Agent $agent): void
    {
        try {
            $token = Password::broker()->createToken($agent->user);
            $setupUrl = route('password.reset', [
                'token' => $token,
                'email' => $agent->user->email,
            ]);
            Mail::to($agent->user->email)->send(new AgentWelcomeMail($agent, $setupUrl));
        } catch (\Throwable $e) {
            report($e); // don't fail the row if mail bounces
        }
    }

    /**
     * Map common Arabic header aliases → expected English keys, and trim.
     */
    private function normalize(array $row): array
    {
        $aliases = [
            'الاسم'                  => 'full_name',
            'الاسم_الكامل'           => 'full_name',
            'البريد'                 => 'email',
            'البريد_الالكتروني'      => 'email',
            'الجوال'                 => 'phone',
            'الهاتف'                 => 'phone',
            'id_الموقع'              => 'external_agent_id',
            'معرف_الوكيل'            => 'external_agent_id',
            'الاسم_التجاري'          => 'business_name',
            'رقم_الترخيص'            => 'license_number',
            'الدولة'                 => 'country',
            'المدينة'                => 'city',
            'التصنيف'                => 'current_tier',
        ];

        $normalized = [];
        foreach ($row as $key => $value) {
            $key = strtolower(trim((string) $key));
            $key = $aliases[$key] ?? $key;
            // Cast scalar values to string — Excel/CSV readers may decode
            // numeric-looking fields (phone, license) as int/float.
            if (is_scalar($value)) {
                $value = trim((string) $value);
            }
            $normalized[$key] = $value === '' ? null : $value;
        }

        // Normalize tier values (Arabic → English).
        if (isset($normalized['current_tier'])) {
            $tier = mb_strtolower($normalized['current_tier']);
            $normalized['current_tier'] = match ($tier) {
                'برونزي', 'bronze'    => 'bronze',
                'فضي', 'silver'       => 'silver',
                'ذهبي', 'gold'        => 'gold',
                'ماسي', 'diamond'     => 'diamond',
                default               => $normalized['current_tier'],
            };
        }

        return $normalized;
    }
}
