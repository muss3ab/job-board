# Job Board API with Advanced Filtering

This Laravel application provides a RESTful API for managing job listings with complex filtering capabilities similar to Airtable. The application handles different job types with varying attributes using Entity-Attribute-Value (EAV) design patterns alongside traditional relational database models.

## Setup Instructions

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Configure your database in `.env`
4. Run migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```
5. Start the development server:
   ```bash
   php artisan serve
   ```

## Database Structure

The application uses the following database structure:

### Core Tables
- `jobs`: Stores the main job information
- `languages`: Programming languages required for jobs
- `locations`: Possible job locations
- `categories`: Job categories/departments

### Many-to-Many Pivot Tables
- `job_language`: Links jobs to required languages
- `job_location`: Links jobs to possible locations
- `category_job`: Links jobs to categories

### EAV Tables
- `attributes`: Defines possible attributes with name, type, and options
- `job_attribute_values`: Stores attribute values for specific jobs

## API Documentation

### Get Jobs with Filtering

**Endpoint**: `GET /api/jobs`

This endpoint allows complex filtering of job listings using query parameters.

#### Query Parameters

- `filter`: The filter expression to apply
- `per_page`: Number of results per page (default: 15)

#### Filter Syntax

The filter parameter accepts a string with conditions and logical operators:

```
filter=(condition1 AND condition2) OR condition3
```

#### Condition Types

1. **Basic Field Filtering**:
   - `field=value`: Exact match
   - `field!=value`: Not equal
   - `field>value`: Greater than
   - `field<value`: Less than
   - `field>=value`: Greater than or equal
   - `field<=value`: Less than or equal
   - `field LIKE value`: Contains value

2. **Relationship Filtering**:
   - `languages=PHP`: Jobs requiring PHP
   - `languages HAS_ANY (PHP,JavaScript)`: Jobs requiring either PHP OR JavaScript
   - `languages IS_ANY (PHP,JavaScript)`: Jobs where any of the languages is PHP or JavaScript
   - `languages EXISTS`: Jobs that have languages specified

3. **EAV Attribute Filtering**:
   - `attribute:years_experience>=3`: Jobs requiring 3+ years of experience
   - `attribute:education_level=Bachelor`: Jobs requiring a Bachelor's degree
   - `attribute:security_clearance=true`: Jobs requiring security clearance

#### Logical Operators

- `AND`: All conditions must be true
- `OR`: At least one condition must be true
- Parentheses `()` for grouping conditions

### Examples

1. Get full-time jobs that require PHP or JavaScript:
   ```
   /api/jobs?filter=job_type=full-time AND (languages HAS_ANY (PHP,JavaScript))
   ```

2. Get jobs in New York or Remote locations with at least 3 years of experience:
   ```
   /api/jobs?filter=(locations IS_ANY (New York,Remote)) AND attribute:years_experience>=3
   ```

3. Get published senior-level jobs with salary above 100,000:
   ```
   /api/jobs?filter=status=published AND salary_min>=100000 AND attribute:seniority_level=Senior
   ```

### Get a Specific Job

**Endpoint**: `GET /api/jobs/{job_id}`

This endpoint returns a specific job with all its details, including attributes, languages, locations, and categories.

## Postman Collection

A Postman collection for testing the API is included in the repository.

## Design Decisions and Trade-offs

### EAV Implementation

The EAV pattern was chosen to allow for flexibility in job attributes while maintaining good query performance. This approach allows:

- Adding new attribute types without schema changes
- Efficient storage of sparse attributes
- Type-specific validation and filtering

The trade-off is increased query complexity when filtering by EAV attributes.

### Filter Parser Implementation

The filter parser uses a simplified syntax that balances expressiveness with parsing complexity. A more robust implementation could use a proper grammar parser, but the current approach provides a good balance of features and maintainability.

### Database Indexing Strategy

Indexes have been added to frequently queried columns and relationship tables to optimize filter performance. Specifically:

- Composite indexes on location (city, country)
- Indexes on the job_attribute_values table (job_id, attribute_id)
- Standard indexes on primary and foreign keys

### Pagination

Results are paginated to ensure good performance even with large datasets.