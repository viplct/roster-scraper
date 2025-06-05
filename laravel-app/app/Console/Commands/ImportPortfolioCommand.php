<?php

namespace App\Console\Commands;

use App\Services\PortfolioImportService;
use Illuminate\Console\Command;

class ImportPortfolioCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portfolio:import 
                            {username : The username to import portfolio for}
                            {url : The URL to import portfolio from}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import portfolio data from a given URL for a specific user';

    public function __construct(
        private readonly PortfolioImportService $portfolioImportService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $username = $this->argument('username');
        $url = $this->argument('url');

        // Validate arguments
        if (empty($username)) {
            $this->error('❌ Username is required');
            return Command::FAILURE;
        }

        if (empty($url)) {
            $this->error('❌ URL is required');
            return Command::FAILURE;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error('❌ URL must be a valid URL');
            return Command::FAILURE;
        }

        $this->info("Starting portfolio import for user: {$username}");
        $this->info("Import URL: {$url}");

        try {
            // Show progress
            $this->info('Importing portfolio data...');
            
            $result = $this->portfolioImportService->importPortfolio($username, $url);

            // Display success message with details
            $this->newLine();
            $this->info('✅ Portfolio imported successfully!');
            
            if (isset($result['user'])) {
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Username', $result['user']['username'] ?? 'N/A'],
                        ['Name', $result['user']['name'] ?? 'N/A'],
                        ['Works Count', isset($result['works']) ? count($result['works']) : 0],
                        ['Clients Count', isset($result['clients']) ? count($result['clients']) : 0],
                    ]
                );
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Failed to import portfolio');
            $this->error("Error: {$e->getMessage()}");
            
            if ($this->output->isVerbose()) {
                $this->error("Stack trace:");
                $this->error($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }
} 