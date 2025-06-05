<?php

namespace App\Services\AgentQL;

use App\Clients\AgentQLHttpClient;
use App\DTOs\ClientData;
use App\DTOs\PortfolioExtractionResult;
use App\DTOs\PortfolioOwnerData;
use App\DTOs\PortfolioWorkData;

class PortfolioDataExtractor
{
    public function __construct(
        private readonly AgentQLHttpClient $client
    ) {}

    /**
     * Extract all portfolio data from a URL using a single comprehensive prompt
     */
    public function extractPortfolioData(string $url): PortfolioExtractionResult
    {
        $prompt = $this->getComprehensivePortfolioPrompt();
        $params = $this->getDefaultParams();

        $data = $this->client->queryDataWithPrompt($url, $prompt, $params);

        // Debug logging (remove in production)
        if (config('app.debug')) {
            \Log::info('AgentQL Portfolio Response', ['data' => $data]);
        }

        return $this->transformPortfolioData($data);
    }

    /**
     * Comprehensive natural language prompt for extracting all portfolio data
     */
    private function getComprehensivePortfolioPrompt(): string
    {
        return "Extract all information from this portfolio page including:

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

        **CLIENT INFORMATION (if available):**
        - Client feedback, reviews, testimonials, or case studies
        - Customer quotes, recommendations, or project details
        - Client work examples or collaborations

        For each client, get:
        - Client name
        - Client job title, position, or company
        - Client feedback, testimonial text, or project description
        - Client photo or company logo URL if available

        Focus on actual content, not navigation elements. If client information doesn't exist, that's okay - just extract owner info and works.";
    }

    /**
     * Get default parameters for AgentQL requests
     */
    private function getDefaultParams(): array
    {
        return [
            'wait_for' => config('agentql.wait_time', 3),
            'is_scroll_to_bottom_enabled' => config('agentql.scroll_enabled', true),
            'mode' => config('agentql.default_mode', 'fast'),
            'is_screenshot_enabled' => false,
        ];
    }

    /**
     * Transform raw portfolio data into structured DTOs
     */
    private function transformPortfolioData(array $rawData): PortfolioExtractionResult
    {
        $owner = $this->transformOwnerData($rawData);
        $works = $this->transformWorksData($rawData);
        $clients = $this->transformClientsData($rawData);

        return new PortfolioExtractionResult($owner, $works, $clients);
    }

    /**
     * Transform raw owner data into DTO
     */
    private function transformOwnerData(array $rawData): PortfolioOwnerData
    {
        // Look for owner/talent information in various keys
        $ownerData = $this->extractObjectFromResponse($rawData, [
            'portfolio_owner', 'owner', 'talent', 'profile', 'person', 'author', 'creator'
        ]) ?: $rawData;

        $name = $this->extractValue($ownerData, [
            'name', 'full_name', 'talent_name', 'owner_name', 'author_name'
        ]);

        $jobTitle = $this->extractValue($ownerData, [
            'job_title', 'title', 'profession', 'role', 'position', 'occupation'
        ]);

        $introduction = $this->extractValue($ownerData, [
            'introduction', 'about', 'bio', 'description', 'about_me', 'summary'
        ]);

        $expertiseRaw = $this->extractArrayOrString($ownerData, [
            'expertise', 'areas_of_expertise', 'specializations', 'specialties', 'areas', 'focus_areas'
        ]);

        $skillsRaw = $this->extractArrayOrString($ownerData, [
            'skills', 'proficiency', 'technical_skills', 'abilities', 'experience', 'software', 'tools'
        ]);

        $socialUrls = $this->extractSocialUrls($ownerData);

        // Handle expertise - could be array or string
        $expertise = [];
        if ($expertiseRaw) {
            if (is_array($expertiseRaw)) {
                // Already an array, just format each item
                foreach ($expertiseRaw as $item) {
                    $item = trim($item);
                    if (!empty($item)) {
                        $expertise[] = strtolower($item);
                    }
                }
            } else {
                // String, split it into array
                $expertiseItems = preg_split('/[,;]/', $expertiseRaw);
                foreach ($expertiseItems as $item) {
                    $item = trim($item);
                    if (!empty($item)) {
                        $expertise[] = strtolower($item);
                    }
                }
            }
        }

        // Handle skills - could be array or string
        $skills = [];
        if ($skillsRaw) {
            if (is_array($skillsRaw)) {
                // Already an array, just format each item
                foreach ($skillsRaw as $item) {
                    $item = trim($item);
                    if (!empty($item)) {
                        $skills[] = strtolower($item);
                    }
                }
            } else {
                // String, split it into array
                $skillsItems = preg_split('/[,;]/', $skillsRaw);
                foreach ($skillsItems as $item) {
                    $item = trim($item);
                    if (!empty($item)) {
                        $skills[] = strtolower($item);
                    }
                }
            }
        }

        return new PortfolioOwnerData(
            name: $name ? ucwords(strtolower(trim($name))) : null,
            jobTitle: $jobTitle ? ucwords(strtolower(trim($jobTitle))) : null,
            introduction: $introduction ? ucfirst(trim($introduction)) : null,
            expertise: $expertise,
            skills: $skills,
            socialUrls: $socialUrls
        );
    }

    /**
     * Transform raw works data into DTOs
     */
    private function transformWorksData(array $rawData): array
    {
        $works = $this->extractArrayFromResponse($rawData, [
            'portfolio_works', 'works'
        ]);

        return array_map(function ($work) {
            $title = $this->extractValue($work, ['title', 'name']) ?: 'Untitled Work';
            $url = $this->extractValue($work, ['url', 'link', 'href', 'src']);
            $description = $this->extractValue($work, ['description', 'caption', 'text', 'summary']);

            return new PortfolioWorkData(
                title: $title,
                url: $url ?: '',
                description: $description
            );
        }, $works);
    }

    /**
     * Transform raw client data into DTOs
     */
    private function transformClientsData(array $rawData): array
    {
        $clients = $this->extractArrayFromResponse($rawData, [
            'clients', 'reviews', 'feedback', 'customers', 'collaborations'
        ]);

        $validClients = array_map(function ($client) {
            $name = $this->extractValue($client, ['name', 'client_name', 'customer_name', 'author', 'reviewer']);
            $feedback = $this->extractValue($client, [
                'feedback', 'testimonial', 'review', 'quote', 'text', 'content', 'description'
            ]);

            // Skip clients without essential data
            if (empty($name) || empty($feedback)) {
                return null;
            }

            return new ClientData(
                name: $name,
                feedback: $feedback,
                jobTitle: $this->extractValue($client, [
                    'job_title', 'title', 'position', 'company', 'role', 'organization'
                ]),
                introduction: $this->extractValue($client, [
                    'introduction', 'about'
                ]),
                photoUrl: $this->extractValue($client, [
                    'photo_url', 'image', 'image_url', 'photo', 'avatar', 'picture', 'logo'
                ])
            );
        }, $clients);

        // Filter out null values
        return array_filter($validClients, fn($client) => $client !== null);
    }

    /**
     * Extract social URLs from owner data
     */
    private function extractSocialUrls(array $data): array
    {
        $socialUrls = [];

        // Look for social URLs in various formats
        $socialKeys = [
            'social_media_urls', 'social_urls', 'social_links', 'social_media', 'links', 'urls'
        ];

        foreach ($socialKeys as $key) {
            if (isset($data[$key])) {
                if (is_array($data[$key])) {
                    $socialUrls = array_merge($socialUrls, array_filter($data[$key]));
                } elseif (is_string($data[$key]) && filter_var($data[$key], FILTER_VALIDATE_URL)) {
                    $socialUrls[] = $data[$key];
                }
            }
        }

        return array_unique($socialUrls);
    }

    /**
     * Extract array from response data with multiple possible keys
     */
    private function extractArrayFromResponse(array $data, array $possibleKeys): array
    {
        foreach ($possibleKeys as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                return $data[$key];
            }
        }

        // If no specific key found, return the data itself if it's an array of objects
        if (is_array($data) && !empty($data) && is_array(current($data))) {
            return $data;
        }

        return [];
    }

    /**
     * Extract object from response data with multiple possible keys
     */
    private function extractObjectFromResponse(array $data, array $possibleKeys): ?array
    {
        foreach ($possibleKeys as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                return $data[$key];
            }
        }

        return null;
    }

    /**
     * Extract value from data with multiple possible keys
     */
    private function extractValue(array $data, array $possibleKeys): ?string
    {
        foreach ($possibleKeys as $key) {
            if (!empty($data[$key])) {
                return is_string($data[$key]) ? trim($data[$key]) : (string) $data[$key];
            }
        }

        return null;
    }

    /**
     * Extract value that could be either array or string from data with multiple possible keys
     */
    private function extractArrayOrString(array $data, array $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            if (!empty($data[$key])) {
                return $data[$key]; // Return as-is, could be array or string
            }
        }

        return null;
    }
}
