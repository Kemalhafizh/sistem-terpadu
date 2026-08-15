<?php
namespace App\Services\DataEngineering;
use App\Models\NlqQueryLog;
use App\Models\NlqSchemaMap;
class NlqService {
    public function parseNaturalLanguage(string $query) { return "SELECT * FROM dummy LIMIT 10"; }
    public function executeAnalyticsQuery(string $sql) { return []; }
    public function getSuggestions() { return ['Tampilkan siswa terbaik bulan ini', 'Berapa rata-rata nilai matematika?']; }
}
