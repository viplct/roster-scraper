# Portfolio Crawl - User Portfolio Management System

A Laravel-based user portfolio management system with semantic search capabilities powered by Typesense and AgentQL integration.

## 🚀 Setup and Run

### Prerequisites
- Docker & Docker Compose
- Git

### Quick Start

1. **Build and start the services**
    ```bash
    docker-compose up --build -d
    ```

2. **Check the app logs:**

    Using this command:
    ```bash
    docker compose logs app
    ```
    You should see this line:
    ```bash
    laravel_app  | [06-Jun-2025 03:25:08] NOTICE: ready to handle connections
    ```
    This means the whole app is completely ready.

3. **Import Sonu's portfolio data**
    ```bash
    docker-compose exec app php artisan portfolio:import sonu https://sonuchoudhary.my.canva.site/portfolio
    ```

4. **Seed the database with test users**
    ```bash
    docker-compose exec app php artisan db:seed --class=UserSeeder
    ```

5. **Import users to search index**
    ```bash
    docker-compose exec app php artisan scout:import "App\Models\User"
    ```

6. **Access the application**
    - **API**: http://localhost:8080/api
    - **API Documentation**: http://localhost:8080/swagger

### Environment Configuration

The application uses the following key environment variables (configured in `docker-entrypoint.sh`):

```bash
TYPESENSE_HOST=typesense
TYPESENSE_PORT=8108
TYPESENSE_PROTOCOL=http
TYPESENSE_API_KEY=your-typesense-api-key
SCOUT_DRIVER=typesense
```

## 🏗️ System Architecture

### High-Level Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│                 │    │                 │    │                 │
│       MySQL     │────│   Laravel App   │────│   Typesense     │
│                 │    │                 │    │   Search Engine │
│                 │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Component Overview

1. **Laravel Application Layer**
   - Artisan commands for portfolio import (using AgentQL)
   - RESTful API endpoints for CRUD operations
   - Request validation using Form Requests
   - Service layer for business logic
   - Eloquent ORM for database interactions

2. **Database Layer (MySQL)**
   - Normalized schema for users, works, clients, and media
   - Foreign key relationships for data integrity
   - Indexed columns for fast queries

3. **Search Layer (Typesense)**
   - Utilizing Typesense to process semantic search with Laravel Scout

4. **Data Management**
   - Portfolio data populated via database seeders
   - Artisan commands for data import and management (Sonu's portfolio)
   - Structured user data with works, clients, and media

## 🤖 Portfolio Import System

### Overview

The system includes a powerful Artisan command that can automatically extract and import talent portfolio data from web sources using AgentQL integration. This allows for seamless onboarding of new talent by simply providing their portfolio URL.

### Command Usage

```bash
php artisan portfolio:import {username} {portfolio_url}
```

**Example:**
```bash
docker-compose exec app php artisan portfolio:import sonu https://sonuchoudhary.my.canva.site/portfolio
```

### How It Works

#### 1. **AgentQL Integration**
The command leverages AgentQL, an AI-powered web scraping service, to intelligently extract structured data from portfolio websites. Unlike traditional web scraping that relies on fixed selectors, AgentQL uses natural language processing to understand the content and structure of web pages.

#### 2. **Data Extraction Process**
- **URL Input**: The command receives the portfolio URL from the user

- **Prompt Engineering**: The system uses the `PortfolioDataExtractor` class to provide AgentQL with a carefully crafted prompt that specifies exactly what information to extract

- **AgentQL Processing**: AgentQL processes the URL using our prompt to extract the requested portfolio information

- **Structured Response**: AgentQL returns the extracted data in a structured format based on our prompt specifications


### AgentQL Prompt Structure

The system uses a comprehensive natural language prompt in the `PortfolioDataExtractor` class to guide AgentQL on what information to extract:

```text
Extract all information from this portfolio page including, all the keys returned should be in snake_case format:

**PORTFOLIO OWNER/TALENT INFORMATION:**
- Name of the portfolio owner/talent
- Job title, profession, or role (e.g., 'Video Editor', 'Graphic Designer')
- About me section, introduction, or bio text
- Areas of expertise, specializations, or what they're good at
- Skills, proficiency, technical abilities, software knowledge, or experience details
- Social media URLs (Instagram, LinkedIn, Twitter, YouTube, etc.)

**PORTFOLIO WORKS:**
- All portfolio work items including videos, images, projects
- YouTube videos, Vimeo videos, embedded videos
- Image galleries and portfolio images
- Project showcases and case studies
For each work item: title/name, URL/link, description/caption

**CLIENTS (if available):**
- Client feedback, reviews, testimonials, or case studies
- Customer quotes, recommendations, or project details
- Client work examples or collaborations

For each client, get:
- name
- job title, position, or company
- feedback, testimonial text, or project description
- photo or company logo URL if available

Focus on actual content, not navigation elements. If client information doesn't exist, that's okay - just extract owner info and works.
```

This prompt ensures AgentQL extracts comprehensive portfolio data in a structured format that can be directly processed by the application.

### AgentQL Response Structure

With the provided prompt above, AgentQL returned a response which should have the below structure:

```javascript
// Example AgentQL query structure
{
  portfolio_owner: {
    name: "Extract the person's full name",
    job_title: "Find the main professional title or role",
    bio: "Extract the biographical information or about section",
    skills: "List all mentioned skills and competencies",
    expertise: "Identify areas of expertise and specialization",
    social_media_urls: "List of social media accounts that the owner included in the page"
  }
  works: [{
    title: "Extract work/project titles",
    description: "Get project descriptions",
    url: "Find project URLs or links"
  }],
  clients: [{
    name: "Extract client names from testimonials",
    job_title: "Get client's job title or position",
    feedback: "Extract testimonial or feedback text",
    photo_url: "Find client photo if available"
  }]
}
```

#### 3. **Database Population**
The extracted data is then processed and stored in the database:
- **User Profile**: Basic information, bio, skills, expertise
- **Work Samples**: Portfolio pieces with titles, descriptions, and URLs
- **Client Testimonials**: Client feedback, photos, and project details
- **Media Assets**: Associated images and videos from the portfolio

### Error Handling & Validation

- **URL Validation**: Ensures the provided URL is accessible and valid
- **Data Validation**: Validates extracted data against database constraints
- **Duplicate Prevention**: Checks for existing users before import
- **Graceful Failures**: Provides clear error messages for debugging
- **Partial Import**: Continues with available data even if some fields fail extraction

### Limitations & Considerations

- **Website Structure Dependency**: Performance may vary based on portfolio website design
- **Rate Limiting**: AgentQL API calls are subject to rate limiting
- **Content Quality**: Extraction accuracy depends on the quality and structure of source content
- **Manual Review**: Imported data may require manual review for accuracy

## 🔗 API Endpoints

### User Management

| Method | Endpoint | Description | Authentication |
|--------|----------|-------------|----------------|
| GET | `/api/users/{username}` | Get user portfolio details | None |
| PATCH | `/api/users/{username}` | Update user with nested resources | None |
| DELETE | `/api/users/{username}` | Delete user account | None |

### Search

| Method | Endpoint | Description | Parameters |
|--------|----------|-------------|------------|
| GET | `/api/users?q={query}` | Semantic search users | `q` (string, min: 6, max: 255) |

### Example API Usage

**Search Users:**
```bash
curl "http://localhost:8080/api/users?q=video%20editor%20with%205%20years%20experience"
```

**Get User Details:**
```bash
curl "http://localhost:8080/api/users/sonu"
```

**Update User (with nested resources):**
```bash
curl -X PATCH "http://localhost:8080/api/users/sonu" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Name",
    "works": [
      {"id": 1, "title": "Updated Work"},
      {"title": "New Work"}
    ],
    "clients": [
      {"id": 1, "_delete": true}
    ]
  }'
```

For complete API documentation with examples, visit: http://localhost:8080/swagger

## 🗄️ Database Structure

### Schema Design for Fast Retrieval

The database is designed with performance and scalability in mind:

#### Core Tables

```sql
-- Users table with indexed search fields
users (
    id PRIMARY KEY,
    name VARCHAR(255) INDEX,
    username VARCHAR(255) UNIQUE INDEX,
    job_title VARCHAR(255) INDEX,
    bio TEXT,
    expertise TEXT,
    skills TEXT,
    social_urls JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)

-- Works with foreign key relationship
works (
    id PRIMARY KEY,
    user_id FOREIGN KEY REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) INDEX,
    url VARCHAR(500),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)

-- Clients with nested media support
clients (
    id PRIMARY KEY,
    user_id FOREIGN KEY REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) INDEX,
    job_title VARCHAR(255) INDEX,
    introduction TEXT,
    feedback TEXT,
    photo_url VARCHAR(500),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)

-- Client media for portfolio showcases
client_media (
    id PRIMARY KEY,
    client_id FOREIGN KEY REFERENCES clients(id) ON DELETE CASCADE,
    type ENUM('image', 'video'),
    url VARCHAR(500),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

#### Performance Optimizations

1. **Strategic Indexing**
   - Primary keys for fast joins
   - Username unique index for user lookups
   - Foreign key indexes for relationship queries

2. **Cascade Deletions**
   - Maintains data integrity
   - Prevents orphaned records
   - Simplifies cleanup operations

3. **JSON Fields**
   - `social_urls` stored as JSON for flexibility
   - Reduces need for additional tables
   - Supports dynamic social media platforms

4. **Relationship Loading**
   - Eager loading with `with()` for N+1 prevention
   - Lazy loading for optional data
   - Selective field loading to reduce memory usage

#### Query Examples

```php
// Optimized user retrieval with relationships
$user = User::with(['works', 'clients.media'])
    ->where('username', $username)
    ->first();

// Efficient bulk operations
User::whereIn('id', $userIds)
    ->with('clients')
    ->chunk(100, function($users) {
        // Process in batches
    });
```

## 📈 Scalability Considerations

### Current Architecture Scalability

1. **Horizontal Database Scaling**
   - MySQL supports read replicas for query distribution
   - Master-slave configuration for write/read separation
   - Database sharding possible by user_id for extreme scale

2. **Search Engine Scaling**
   - Typesense supports multi-node clusters
   - Automatic load balancing across nodes
   - Real-time replication and failover

3. **Application Layer Scaling**
   - Stateless Laravel application
   - Easy horizontal scaling with load balancers
   - Docker containerization for consistent deployments

### Performance Monitoring

- **Database**: Query performance, connection pooling
- **Search**: Index size, query response times
- **API**: Response times, error rates
- **Infrastructure**: CPU, memory, disk I/O

## 🔍 Semantic Search System Design

### Overview

The semantic search system combines Laravel Scout with Typesense to provide intelligent, typo-tolerant, and context-aware search capabilities.

**Why Typesense?**
- **Fast Setup**: Easy integration with Laravel Scout, minimal configuration required
- **Natural Language Processing**: Handles complex queries like "video editor with 5 years experience working with doctors"

### Architecture Components

#### 1. Laravel Scout Integration

```php
// User.php - Searchable model
use Laravel\Scout\Searchable;

class User extends Model
{
    use Searchable;
    
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'job_title' => $this->job_title,
            'bio' => $this->bio,
            'expertise' => $this->expertise,
            'skills' => $this->skills,
            'client_job_titles' => $this->getClientJobTitles(),
        ];
    }
    
    private function getClientJobTitles()
    {
        return $this->clients()
            ->whereNotNull('job_title')
            ->pluck('job_title')
            ->implode(', ');
    }
}
```

#### 2. Typesense Configuration

```php
// config/scout.php - Typesense settings
'typesense' => [
    'client' => [
        'api_key' => env('TYPESENSE_API_KEY'),
        'nodes' => [
            [
                'host' => env('TYPESENSE_HOST', 'localhost'),
                'port' => env('TYPESENSE_PORT', '8108'),
                'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
            ],
        ],
        'connection_timeout_seconds' => 2,
        'healthcheck_interval_seconds' => 30,
        'num_retries' => 3,
        'retry_interval_seconds' => 0.1,
    ],
    
    // Collection schema for optimal search
    'model-settings' => [
        User::class => [
            'collection-schema' => [
                'fields' => [
                    ['name' => 'id', 'type' => 'int32'],
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'username', 'type' => 'string'],
                    ['name' => 'job_title', 'type' => 'string', 'optional' => true],
                    ['name' => 'bio', 'type' => 'string', 'optional' => true],
                    ['name' => 'expertise', 'type' => 'string', 'optional' => true],
                    ['name' => 'skills', 'type' => 'string', 'optional' => true],
                    ['name' => 'client_job_titles', 'type' => 'string', 'optional' => true],
                ],
            ],
            'search-parameters' => [
                'query_by' => 'job_title,bio,expertise,skills,client_job_titles,name',
                'query_by_weights' => '4,3,3,2,2,1',
                'typo_tokens_threshold' => 1,
                'num_typos' => 2,
                'prefix' => true,
                'drop_tokens_threshold' => 1,
                'highlight_full_fields' => 'job_title,bio,expertise,skills',
            ],
        ],
    ],
],
```

#### 3. Search Service Implementation

```php
// UserService.php - Business logic
public function search(string $query): Collection
{
    return User::search($query)
        ->take(50)
        ->get()
        ->load(['works', 'clients.media']);
}
```

### Search Features

#### Weighted Field Matching

The search system uses weighted field matching to prioritize relevance. Higher weight values mean that matches in those fields will score higher and appear first in results.

- **job_title** (weight: 6) - Highest priority for professional role matching
- **expertise** (weight: 5) - Core competency and specialization areas
- **skills** (weight: 5) - Technical abilities and proficiencies  
- **bio** (weight: 4) - Detailed background information
- **client_job_titles** (weight: 4) - Experience with specific client types
- **name** (weight: 3) - Person identification
- **username** (weight: 2) - Account identifier

When searching for "video editor with 5 years experience", a match in the `job_title` field will score 3x higher than a match in the `username` field, ensuring more relevant results appear first.

## 🚧 Future Improvements

### Portfolio Data Extraction

- **AgentQL Dependency**: The system currently relies heavily on AgentQL to extract portfolio information from web sources. While effective, this creates a dependency on external AI services for data parsing.

- **Prompt Engineering**: To improve the accuracy of importing information from diverse talent portfolios, the AgentQL prompts need further refinement and testing across different portfolio formats and layouts.

### Search Optimization

- **Field Evaluation**: Given more time, we should conduct thorough evaluation of which fields are most effective for semantic search, potentially adding new fields or adjusting field weights based on user search patterns and feedback.

### Code Quality & Testing

- **Test Coverage**: Implementation of comprehensive unit testing and feature testing is needed to ensure code correctness, especially for the portfolio import functionality and search features.

- **Integration Testing**: End-to-end tests for the complete workflow from portfolio import to search results would improve system reliability.

### Scalability & Performance

- **Background Processing**: The portfolio import command (`php artisan portfolio:import`) should be converted to a queue-based system to handle multiple import requests concurrently and prevent blocking operations.

- **Scheduled Imports**: Consider implementing cron jobs for periodic portfolio updates, allowing for automatic synchronization of portfolio data.

- **Rate Limiting**: Add rate limiting for the import command to prevent abuse and manage resource usage effectively.

### User Experience

- **Import Status Tracking**: Implement a status tracking system for portfolio imports, allowing users to monitor progress and view import history.

- **Bulk Import**: Support for importing multiple portfolios simultaneously with proper error handling and progress reporting.

### API Design

- **Separated Update Endpoints**: Currently, the Update User API (`PATCH /api/users/{username}`) performs bulk updates for users, works, and clients in a single request. Consider separating this into dedicated endpoints:
  - `PATCH /api/users/{username}` - Update user profile only
  - `POST /api/users/{username}/works` - Add new work
  - `PATCH /api/users/{username}/works/{id}` - Update specific work
  - `DELETE /api/users/{username}/works/{id}` - Delete work
  - Similar endpoints for clients and media
  
  This approach would:
  - Simplify payload requirements for frontend developers
  - Make API endpoints more intuitive and RESTful
  - Reduce complexity in request validation
  - Allow for more granular error handling
  - Improve developer experience and API understanding
