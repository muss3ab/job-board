<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            'PHP', 'JavaScript', 'Python', 'Java', 'C#', 'C++', 
            'Ruby', 'Swift', 'Go', 'Rust', 'TypeScript', 'Kotlin'
        ];

        foreach ($languages as $language) {
            Language::create(['name' => $language]);
        }
    }
}
