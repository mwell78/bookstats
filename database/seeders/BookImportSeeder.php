<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class BookImportSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = base_path('Bookstats.csv');
        $handle = fopen($csvFile, 'r');
        
        // Skip BOM if exists
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Get headers
        $header = fgetcsv($handle, 0, ';');
        
        $user = User::where('email', 'test@example.com')->first();
        if (!$user) {
            $this->command->error('Test user not found! Please run UserSeeder first.');
            return;
        }

        $count = 0;
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) < 18) continue;

            $data = array_combine($header, $row);

            Book::create([
                'user_id' => $user->id,
                'title' => $data['Titel'] ?? 'Unbekannt',
                'author' => $data['Autor(en)'] ?? null,
                'isbn' => $data['ISBN'] ?? null,
                'pages' => is_numeric($data['Seitenanzahl']) ? (int)$data['Seitenanzahl'] : null,
                'format' => $data['Buchart'] ?? null,
                'status' => $data['Lesestatus'] ?? 'Ungelesen',
                'started_at' => $this->parseDate($data['Lesebeginn'] ?? null),
                'finished_at' => $this->parseDate($data['Leseende'] ?? null),
                'notes' => $data['Notizen'] ?? null,
                'published_year' => $data['Erscheinungsjahr'] ?? null,
                'genre' => $data['Genre'] ?? null,
            ]);
            $count++;
        }

        fclose($handle);
        $this->command->info("Imported $count books.");
    }

    private function parseDate($dateString)
    {
        if (!$dateString || $dateString === 'null' || $dateString === '') return null;
        
        try {
            // German format DD.MM.YYYY
            return Carbon::createFromFormat('d.m.Y', $dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
