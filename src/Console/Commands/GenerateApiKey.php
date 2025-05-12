<?php

namespace Givebutter\LaravelKeyable\Console\Commands;

use Givebutter\LaravelKeyable\Models\ApiKey;
use Illuminate\Console\Command;

class GenerateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-key:generate
                            {--id= : ID of the model you want to bind to this API key}
                            {--type= : The class name of the model you want to bind to this API key}
                            {--name= : The name you want to give to this API key}
                            {--expires_at= : Expiration date of the API key in format YYYY-MM-DD or YYYY-MM-DD H:i (default: null}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate API key';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $expiresAt = $this->option('expires_at') ?? null;

        if ($expiresAt) {
            try {
                $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $expiresAt) ?: \DateTime::createFromFormat('Y-m-d', $expiresAt);
                if (!$dateTime) {
                    throw new \Exception('Invalid date format');
                }
                $expiresAt = $dateTime->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                $this->error('Invalid expiration date format. Use YYYY-MM-DD or YYYY-MM-DD H:i.');
                return;
            }
        }

        $apiKey = (new ApiKey)->create([
            'keyable_id' => $this->option('id'),
            'keyable_type' => $this->option('type'),
            'name' => $this->option('name'),
            'expires_at' => $expiresAt,
        ]);

        $this->info('The following API key was created: ' . "{$apiKey->getKey()}|{$apiKey->plainTextApiKey}");
    }
}
