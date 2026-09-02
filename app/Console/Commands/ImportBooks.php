<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ImportBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-books {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import books from Bookstats.csv';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = base_path('Bookstats.csv');
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return;
        }

        $email = $this->argument('email');
        $user = $email ? User::where('email', $email)->first() : User::first();

        if (!$user) {
            $this->error('No user found. Please create a user first.');
            return;
        }

        $handle = fopen($file, 'r');
        
        // Handle BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, 0, ';');
        $count = 0;

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            if (count($header) !== count($data)) continue;
            
            $row = array_combine($header, $data);
            
            Book::create([
                'user_id' => $user->id,
                'title' => $row['Titel'],
                'author' => $row['Autor(en)'],
                'isbn' => $row['ISBN'],
                'pages' => (int) $row['Seitenanzahl'],
                'status' => $row['Lesestatus'] ?? 'Ungelesen',
                'started_at' => $this->parseDate($row['Lesebeginn']),
                'finished_at' => $this->parseDate($row['Leseende']),
                'published_year' => $row['Erscheinungsjahr'],
                'genre' => $row['Genre'],
                'notes' => $row['Notizen'],
            ]);
            $count++;
        }

        fclose($handle);
        $this->info("Successfully imported $count books for user {$user->email}.");
    }

    private function parseDate($date)
    {
        if (empty($date) || $date === '0' || $date === '00.00.0000') return null;
        try {
            return Carbon::createFromFormat('d.m.Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
