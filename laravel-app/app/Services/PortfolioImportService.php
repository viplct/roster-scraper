<?php

namespace App\Services;

use App\Models\User;
use App\Models\Work;
use App\Models\Client;
use App\Models\ClientMedia;
use App\DTOs\PortfolioOwnerData;
use App\Services\AgentQL\PortfolioDataExtractor;
use App\Exceptions\AgentQL\AgentQLException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PortfolioImportService
{
    public function __construct(
        private readonly PortfolioDataExtractor $portfolioDataExtractor
    ) {}

    /**
     * Import portfolio data for a user from a given URL
     */
    public function importPortfolio(string $username, string $url): array
    {
        try {
            return DB::transaction(function () use ($username, $url) {
                // Find or create user
                $user = $this->findOrCreateUser($username);

                // Extract portfolio data using AgentQL
                $extractionResult = $this->portfolioDataExtractor->extractPortfolioData($url);

                // Update user profile with extracted portfolio information
                $this->updateUserProfile($user, $extractionResult->owner);

                // Save works to database
                $savedWorks = $this->saveWorks($user, $extractionResult->works);

                // Save clients to database
                $savedClients = $this->saveClients($user, $extractionResult->clients);

                Log::info('Portfolio imported successfully', [
                    'user_id' => $user->id,
                    'username' => $username,
                    'url' => $url,
                    'works_count' => count($savedWorks),
                    'clients_count' => count($savedClients),
                    'owner_data_found' => !empty($extractionResult->owner->name),
                ]);

                return [
                    'user' => $user,
                    'works' => $savedWorks,
                    'clients' => $savedClients,
                    'summary' => [
                        'total_works' => count($savedWorks),
                        'total_clients' => count($savedClients),
                        'social_urls_found' => count($extractionResult->owner->socialUrls),
                    ],
                ];
            });

        } catch (AgentQLException $e) {
            Log::error('AgentQL extraction failed', [
                'username' => $username,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to extract portfolio data: ' . $e->getMessage());

        } catch (\Exception $e) {
            Log::error('Portfolio import failed', [
                'username' => $username,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to import portfolio: ' . $e->getMessage());
        }
    }

    /**
     * Find existing user or create new one
     */
    private function findOrCreateUser(string $username): User
    {
        return User::firstOrCreate(
            ['username' => $username],
            ['name' => $username]
        );
    }

    /**
     * Update user profile with extracted portfolio data
     */
    private function updateUserProfile(User $user, PortfolioOwnerData $portfolioData): void
    {
        $updateData = [];

        // Update name if we found it and it's different from username
        if ($portfolioData->name && $portfolioData->name !== $user->username) {
            $updateData['name'] = $portfolioData->name;
        }

        // Update job title if available
        if ($portfolioData->jobTitle) {
            $updateData['job_title'] = $portfolioData->jobTitle;
        }

        // Update bio/introduction if available
        if ($portfolioData->introduction) {
            $updateData['bio'] = $portfolioData->introduction;
        }

        // Update expertise if available - convert array to comma-separated string
        if (!empty($portfolioData->expertise)) {
            $updateData['expertise'] = implode(', ', $portfolioData->expertise);
        }

        // Update skills if available - convert array to comma-separated string
        if (!empty($portfolioData->skills)) {
            $updateData['skills'] = implode(', ', $portfolioData->skills);
        }

        // Store social URLs as JSON if available
        if (!empty($portfolioData->socialUrls)) {
            $updateData['social_urls'] = $portfolioData->socialUrls;
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }
    }

    /**
     * Save portfolio works to database
     */
    private function saveWorks(User $user, array $worksData): array
    {
        $savedWorks = [];

        foreach ($worksData as $workData) {
            $work = Work::create([
                'user_id' => $user->id,
                'title' => $workData->title,
                'description' => $workData->description,
                'url' => $workData->url,
            ]);

            $savedWorks[] = $work;
        }

        return $savedWorks;
    }

    /**
     * Save client data to database
     */
    private function saveClients(User $user, array $clientsData): array
    {
        $savedClients = [];

        foreach ($clientsData as $clientData) {
            if (empty($clientData->name) || empty($clientData->feedback)) {
                continue; // Skip incomplete client data
            }

            $client = Client::create([
                'user_id' => $user->id,
                'name' => $clientData->name,
                'introduction' => $clientData->introduction,
                'job_title' => $clientData->jobTitle,
                'feedback' => $clientData->feedback,
            ]);

            // Save client image if available
            if ($clientData->imageUrl) {
                ClientMedia::create([
                    'client_id' => $client->id,
                    'url' => $clientData->imageUrl,
                ]);

                // Load the media relationship for the response
                $client->load('media');
            }

            $savedClients[] = $client;
        }

        return $savedClients;
    }
}
