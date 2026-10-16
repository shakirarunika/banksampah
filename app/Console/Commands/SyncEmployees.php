<?php

namespace App\Console\Commands;

use App\Helpers\ActivityLogger;
use App\Models\Division;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Sinkron data karyawan dari tarikan HR (sheet "AKTIF": NIK, Nama Pegawai, Departemen).
 * - NIK + nama cocok, departemen beda  -> pindah departemen
 * - NIK cocok, nama cuma beda typo     -> nama ikut Excel
 * - NIK belum terdaftar sama sekali    -> daftarkan (password = NIK, wajib ganti)
 * Tanpa --apply cuma preview, gak ada yang disimpan.
 */
class SyncEmployees extends Command
{
    protected $signature = 'karyawan:sync {file : Path file Excel tarikan HR} {--apply : Simpan perubahan (tanpa ini cuma preview)}';

    protected $description = 'Update departemen & daftarkan karyawan baru dari Excel tarikan HR';

    public function handle(): int
    {
        $rows = $this->readActiveSheet($this->argument('file'));
        if ($rows === null) {
            return self::FAILURE;
        }

        $norm = fn ($s) => mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $s)));
        $divisions = Division::all()->keyBy(fn ($d) => $norm($d->name));
        $users = User::withTrashed()->with('division')->get()->keyBy(fn ($u) => trim((string) $u->employee_code));

        $emails = User::withTrashed()->pluck('email')->map(fn ($e) => mb_strtolower($e))->flip();

        $plan = ['move' => [], 'rename' => [], 'create' => [], 'skip' => []];
        $newDivisions = [];
        $seen = [];

        foreach ($rows as $i => [$nik, $name, $dept]) {
            $line = $i + 1; // key = index baris Excel mulai 0
            if ($nik === '' || $name === '' || $dept === '') {
                $plan['skip'][] = "Baris {$line}: NIK/nama/departemen kosong";

                continue;
            }
            if (isset($seen[$nik])) {
                $plan['skip'][] = "Baris {$line}: NIK {$nik} dobel di Excel (sudah dipakai baris {$seen[$nik]})";

                continue;
            }
            $seen[$nik] = $line;
            if (! $divisions->has($norm($dept))) {
                $newDivisions[$norm($dept)] = $dept;
            }

            $user = $users->get($nik);

            if (! $user) {
                if ($emails->has(mb_strtolower($nik.'@bank.sampah'))) {
                    $plan['skip'][] = "{$nik} {$name}: email {$nik}@bank.sampah sudah dipakai akun lain — cek manual";

                    continue;
                }
                $plan['create'][] = compact('nik', 'name', 'dept');

                continue;
            }
            if ($user->trashed()) {
                $plan['skip'][] = "{$nik} {$user->name}: akun sudah dihapus, tidak dipulihkan otomatis";

                continue;
            }
            if ($norm($user->name) !== $norm($name)) {
                if (! $this->isTypo($norm($user->name), $norm($name))) {
                    $plan['skip'][] = "{$nik}: nama beda jauh (sistem '{$user->name}', Excel '{$name}') — cek manual";

                    continue;
                }
                $plan['rename'][] = ['user' => $user, 'name' => $name];
            }
            if ($norm($user->division?->name) !== $norm($dept)) {
                $plan['move'][] = ['user' => $user, 'dept' => $dept];
            }
        }

        $this->report($plan, $newDivisions);

        if (! $this->option('apply')) {
            $this->warn('PREVIEW saja — belum ada yang disimpan. Jalankan ulang dengan --apply untuk menyimpan.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($plan, $newDivisions, $divisions, $norm) {
            foreach ($newDivisions as $key => $name) {
                $divisions[$key] = Division::create(['name' => $name]);
            }
            foreach ($plan['rename'] as ['user' => $user, 'name' => $name]) {
                $user->update(['name' => $name]);
            }
            foreach ($plan['move'] as ['user' => $user, 'dept' => $dept]) {
                $user->update(['division_id' => $divisions[$norm($dept)]->id]);
            }
            foreach ($plan['create'] as ['nik' => $nik, 'name' => $name, 'dept' => $dept]) {
                $user = new User;
                $user->fill([
                    'name' => $name,
                    'employee_code' => $nik,
                    'email' => $nik.'@bank.sampah',
                    'password' => Hash::make($nik),
                    'division_id' => $divisions[$norm($dept)]->id,
                ]);
                $user->role = 'karyawan';
                $user->is_active = true;
                $user->must_change_password = true;
                $user->save();
            }

            ActivityLogger::log('sync_employees', sprintf(
                'Sinkron data karyawan dari %s: %d pindah departemen, %d nama dikoreksi, %d karyawan baru, %d divisi baru',
                basename($this->argument('file')), count($plan['move']), count($plan['rename']), count($plan['create']), count($newDivisions)
            ));
        });

        $this->info('Tersimpan.');

        return self::SUCCESS;
    }

    /** @return array<int, array{string,string,string}>|null  key = index baris di sheet (0 = header) */
    private function readActiveSheet(string $file): ?array
    {
        if (! is_file($file)) {
            $this->error("File tidak ditemukan: {$file}");

            return null;
        }

        $book = IOFactory::load($file);
        $sheet = collect($book->getWorksheetIterator())
            ->first(fn ($ws) => str_starts_with(mb_strtoupper(trim($ws->getTitle())), 'AKTIF'));

        if (! $sheet) {
            $this->error('Sheet berawalan "AKTIF" tidak ditemukan di file.');

            return null;
        }

        $data = $sheet->toArray(null, true, false, false);
        $header = array_map(fn ($h) => mb_strtolower(trim((string) $h)), $data[0] ?? []);
        if (array_slice($header, 0, 3) !== ['nik', 'nama pegawai', 'departemen']) {
            $this->error('Header sheet AKTIF harus: NIK | Nama Pegawai | Departemen');

            return null;
        }

        return collect(array_slice($data, 1, null, true))
            ->map(fn ($r) => [trim((string) $r[0]), trim(preg_replace('/\s+/u', ' ', (string) $r[1])), trim(preg_replace('/\s+/u', ' ', (string) $r[2]))])
            ->reject(fn ($r) => $r === ['', '', ''])
            ->all();
    }

    /** Beda maksimal 2 huruf dan tetap ≥ 85% mirip — anggap typo, bukan orang lain. */
    private function isTypo(string $a, string $b): bool
    {
        $distance = levenshtein($a, $b);

        return $distance <= 2 && $distance / max(mb_strlen($a), mb_strlen($b), 1) <= 0.15;
    }

    private function report(array $plan, array $newDivisions): void
    {
        $this->info('Pindah departemen: '.count($plan['move']));
        foreach ($plan['move'] as ['user' => $u, 'dept' => $dept]) {
            $this->line("  {$u->employee_code} {$u->name}: ".($u->division?->name ?? '-')." -> {$dept}");
        }

        $this->info('Nama dikoreksi: '.count($plan['rename']));
        foreach ($plan['rename'] as ['user' => $u, 'name' => $name]) {
            $this->line("  {$u->employee_code}: {$u->name} -> {$name}");
        }

        $this->info('Karyawan baru: '.count($plan['create']));
        foreach ($plan['create'] as $r) {
            $this->line("  {$r['nik']} {$r['name']} ({$r['dept']})");
        }

        $this->info('Divisi baru: '.(count($newDivisions) ? implode(', ', $newDivisions) : '-'));

        if ($plan['skip']) {
            $this->warn('Dilewati: '.count($plan['skip']));
            foreach ($plan['skip'] as $s) {
                $this->line("  {$s}");
            }
        }
    }
}
