<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            [
                'name' => 'years_experience',
                'type' => Attribute::TYPE_NUMBER,
                'options' => null
            ],
            [
                'name' => 'education_level',
                'type' => Attribute::TYPE_SELECT,
                'options' => ['High School', 'Associate', 'Bachelor', 'Master', 'PhD']
            ],
            [
                'name' => 'security_clearance',
                'type' => Attribute::TYPE_BOOLEAN,
                'options' => null
            ],
            [
                'name' => 'application_deadline',
                'type' => Attribute::TYPE_DATE,
                'options' => null
            ],
            [
                'name' => 'benefits',
                'type' => Attribute::TYPE_TEXT,
                'options' => null
            ],
            [
                'name' => 'seniority_level',
                'type' => Attribute::TYPE_SELECT,
                'options' => ['Junior', 'Mid-level', 'Senior', 'Lead', 'Manager']
            ],
        ];

        foreach ($attributes as $attribute) {
            Attribute::create($attribute);
        }
    }
}
