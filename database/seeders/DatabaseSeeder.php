<?php

namespace Database\Seeders;

use App\Contracts\DemandForecastServiceInterface;
use App\Models\BibliographicResource;
use App\Models\PdaRequest;
use App\Models\TenantLibrary;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $pk = TenantLibrary::create([
            'code' => 'BGPK',
            'name' => 'Biblioteka Główna PK',
            'budget' => 80000,
            'auto_pda_threshold' => 70,
        ]);

        $agh = TenantLibrary::create([
            'code' => 'BAGH',
            'name' => 'Biblioteka AGH',
            'budget' => 65000,
            'auto_pda_threshold' => 50,
        ]);

        $algorithms = $this->addBook($pk, [
            'isbn' => '9780262046305',
            'title' => 'Wprowadzenie do algorytmów',
            'author' => 'Thomas H. Cormen',
            'price' => 189.00,
            'available_copies' => 2,
            'search_hits' => 12,
            'year' => '2022',
        ]);

        $cleanCode = $this->addBook($pk, [
            'isbn' => '9780132350884',
            'title' => 'Czysty kod. Podręcznik dobrego programisty',
            'author' => 'Robert C. Martin',
            'price' => 129.00,
            'available_copies' => 1,
            'search_hits' => 9,
            'year' => '2010',
        ]);

        $software = $this->addBook($pk, [
            'isbn' => '9780133943030',
            'title' => 'Inżynieria oprogramowania',
            'author' => 'Ian Sommerville',
            'price' => 310.00,
            'available_copies' => 1,
            'search_hits' => 5,
            'year' => '2016',
        ]);

        $networks = $this->addBook($agh, [
            'isbn' => '9780132126953',
            'title' => 'Sieci komputerowe',
            'author' => 'Andrew S. Tanenbaum',
            'price' => 149.90,
            'available_copies' => 3,
            'search_hits' => 18,
            'year' => '2012',
        ]);

        $this->addBook($agh, [
            'isbn' => '9788328336386',
            'title' => 'Systemy baz danych',
            'author' => 'Paul Beynon-Davies',
            'price' => 89.00,
            'available_copies' => 4,
            'search_hits' => 7,
            'year' => '2018',
        ]);

        $this->addPda($pk, $algorithms, 'anna.kowalska@student.pk.edu.pl', 'EAIIB');
        $this->addPda($pk, $algorithms, 'jan.nowak@student.pk.edu.pl', 'INF');
        $this->addPda($pk, $cleanCode, 'ola.wisniewska@student.pk.edu.pl', 'IO');
        $this->addPda($pk, $software, 'piotr.lewandowski@student.pk.edu.pl', 'SIK');
        $this->addPda($agh, $networks, 'maria.zielinska@student.agh.edu.pl', 'TK');
        $this->addPda($agh, $networks, 'adam.wojcik@student.agh.edu.pl', 'SKIN');
        $this->addPda($agh, $networks, 'kasia.mazur@student.agh.edu.pl', 'TIN');

        $forecasts = app(DemandForecastServiceInterface::class);

        foreach ([$algorithms, $cleanCode, $software, $networks] as $book) {
            $book->refresh();
            $tenant = TenantLibrary::find($book->tenant_id);
            $forecast = $forecasts->evaluateAndForecast($book, $tenant);

            if ($forecast->recommendation === 'AUTO_BUY') {
                PdaRequest::where('resource_id', $book->id)->update(['status' => 'AUTO_BUY']);
            }
        }
    }

    private function addBook(TenantLibrary $tenant, array $data): BibliographicResource
    {
        return BibliographicResource::create([
            'tenant_id' => $tenant->id,
            'isbn' => $data['isbn'],
            'title' => $data['title'],
            'author' => $data['author'],
            'metadata_format' => 'MARC21',
            'marc21_json' => [
                '020a' => $data['isbn'],
                '100a' => $data['author'],
                '245a' => $data['title'],
                '260c' => $data['year'],
            ],
            'price' => $data['price'],
            'available_copies' => $data['available_copies'],
            'search_hits' => $data['search_hits'],
        ]);
    }

    private function addPda(TenantLibrary $tenant, BibliographicResource $book, string $email, string $course): void
    {
        PdaRequest::create([
            'tenant_id' => $tenant->id,
            'resource_id' => $book->id,
            'user_email' => $email,
            'course_code' => $course,
            'status' => 'NEW',
        ]);
    }
}
