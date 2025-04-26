<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Services\JobFilterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class JobController extends Controller
{
    protected JobFilterService $filterService;

    public function __construct(JobFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    /**
     * Get a list of jobs with advanced filtering.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filter = $request->query('filter');
        
        $query = $this->filterService->filter($filter);
        
        // Add eager loading to prevent N+1 problems
        $query->with(['languages', 'locations', 'categories', 'attributeValues.attribute']);
        
        // Add pagination
        $perPage = $request->query('per_page', 15);
        $jobs = $query->paginate($perPage);
        
        // Transform the jobs to include the EAV attributes in a more accessible format
        $transformedJobs = $jobs->getCollection()->map(function ($job) {
            $jobArray = $job->toArray();
            
            // Add attributes as key-value pairs
            $jobArray['attributes'] = [];
            foreach ($job->attributeValues as $attributeValue) {
                $jobArray['attributes'][$attributeValue->attribute->name] = $attributeValue->value;
            }
            
            return $jobArray;
        });
        
        // Replace the collection in the paginator
        $jobs->setCollection($transformedJobs);
        
        return response()->json($jobs);
    }

    /**
     * Get a specific job.
     *
     * @param Job $job
     * @return JsonResponse
     */
    public function show(Job $job): JsonResponse
    {
        $job->load(['languages', 'locations', 'categories', 'attributeValues.attribute']);
        
        $jobArray = $job->toArray();
        
        // Add attributes as key-value pairs
        $jobArray['attributes'] = [];
        foreach ($job->attributeValues as $attributeValue) {
            $jobArray['attributes'][$attributeValue->attribute->name] = $attributeValue->value;
        }
        
        return response()->json($jobArray);
    }
}