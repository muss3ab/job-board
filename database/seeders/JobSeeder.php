<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Job;
use App\Models\Language;
use App\Models\Location;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\JobAttributeValue;
use Carbon\Carbon;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample jobs
        $jobs = [
            [
                'title' => 'Senior PHP Developer',
                'description' => 'We need an experienced PHP developer to join our team.',
                'company_name' => 'Tech Solutions Inc.',
                'salary_min' => 80000,
                'salary_max' => 120000,
                'is_remote' => true,
                'job_type' => Job::JOB_TYPE_FULL_TIME,
                'status' => Job::STATUS_PUBLISHED,
                'published_at' => Carbon::now()->subDays(5),
                'languages' => ['PHP', 'JavaScript'],
                'locations' => ['New York', 'Remote'],
                'categories' => ['Web Development'],
                'attributes' => [
                    'years_experience' => 5,
                    'education_level' => 'Bachelor',
                    'security_clearance' => false,
                    'seniority_level' => 'Senior'
                ]
            ],
            [
                'title' => 'Frontend Developer',
                'description' => 'Creating beautiful and responsive web interfaces.',
                'company_name' => 'Creative Designs',
                'salary_min' => 60000,
                'salary_max' => 90000,
                'is_remote' => false,
                'job_type' => Job::JOB_TYPE_FULL_TIME,
                'status' => Job::STATUS_PUBLISHED,
                'published_at' => Carbon::now()->subDays(3),
                'languages' => ['JavaScript', 'TypeScript'],
                'locations' => ['San Francisco'],
                'categories' => ['Web Development', 'UI/UX Design'],
                'attributes' => [
                    'years_experience' => 3,
                    'education_level' => 'Bachelor',
                    'security_clearance' => false,
                    'seniority_level' => 'Mid-level'
                ]
            ],
            [
                'title' => 'DevOps Engineer',
                'description' => 'Maintaining and improving our cloud infrastructure.',
                'company_name' => 'Cloud Services Ltd',
                'salary_min' => 90000,
                'salary_max' => 140000,
                'is_remote' => true,
                'job_type' => Job::JOB_TYPE_FULL_TIME,
                'status' => Job::STATUS_PUBLISHED,
                'published_at' => Carbon::now()->subDays(7),
                'languages' => ['Python', 'Go'],
                'locations' => ['Remote'],
                'categories' => ['DevOps'],
                'attributes' => [
                    'years_experience' => 4,
                    'education_level' => 'Bachelor',
                    'security_clearance' => true,
                    'seniority_level' => 'Senior'
                ]
            ],
            [
                'title' => 'Mobile App Developer',
                'description' => 'Developing iOS and Android applications.',
                'company_name' => 'Mobile Apps Inc.',
                'salary_min' => 70000,
                'salary_max' => 110000,
                'is_remote' => false,
                'job_type' => Job::JOB_TYPE_CONTRACT,
                'status' => Job::STATUS_PUBLISHED,
                'published_at' => Carbon::now()->subDays(2),
                'languages' => ['Swift', 'Kotlin'],
                'locations' => ['London'],
                'categories' => ['Mobile Development'],
                'attributes' => [
                    'years_experience' => 2,
                    'education_level' => 'Bachelor',
                    'application_deadline' => Carbon::now()->addDays(30)->format('Y-m-d'),
                    'seniority_level' => 'Mid-level'
                ]
            ],
            [
                'title' => 'Data Scientist',
                'description' => 'Analyzing large datasets and implementing machine learning models.',
                'company_name' => 'Data Insights',
                'salary_min' => 85000,
                'salary_max' => 130000,
                'is_remote' => true,
                'job_type' => Job::JOB_TYPE_FULL_TIME,
                'status' => Job::STATUS_PUBLISHED,
                'published_at' => Carbon::now()->subDays(10),
                'languages' => ['Python', 'R'],
                'locations' => ['Berlin', 'Remote'],
                'categories' => ['Data Science'],
                'attributes' => [
                    'years_experience' => 3,
                    'education_level' => 'Master',
                    'benefits' => 'Health insurance, 401k, gym membership',
                    'seniority_level' => 'Senior'
                ]
            ],
        ];

        foreach ($jobs as $jobData) {
            // Extract relationship data
            $languageNames = $jobData['languages'] ?? [];
            $locationNames = $jobData['locations'] ?? [];
            $categoryNames = $jobData['categories'] ?? [];
            $attributeValues = $jobData['attributes'] ?? [];

            unset($jobData['languages'], $jobData['locations'], $jobData['categories'], $jobData['attributes']);

            // Create the job
            $job = Job::create($jobData);

            // Attach languages
            $languageIds = Language::whereIn('name', $languageNames)->pluck('id');
            $job->languages()->attach($languageIds);

            // Attach locations
            $locationIds = Location::whereIn('city', $locationNames)->pluck('id');
            $job->locations()->attach($locationIds);

            // Attach categories
            $categoryIds = Category::whereIn('name', $categoryNames)->pluck('id');
            $job->categories()->attach($categoryIds);

            // Add attribute values
            foreach ($attributeValues as $attrName => $value) {
                $attribute = Attribute::where('name', $attrName)->first();
                if ($attribute) {
                    
                    if ($attribute->type === Attribute::TYPE_BOOLEAN) {
                        $value = $value ? '1' : '0';
                    }

                    JobAttributeValue::create([
                        'job_id' => $job->id,
                        'attribute_id' => $attribute->id,
                        'value' => $value
                    ]);
                }
            }
        }
    }
}
