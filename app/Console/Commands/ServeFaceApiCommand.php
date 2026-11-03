<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ServeFaceApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'face-api:serve
                            {--host=0.0.0.0 : Host address for FastAPI server}
                            {--port=8001 : Port for FastAPI server}
                            {--reload : Enable auto-reload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the local Python biometric face recognition API server (bisagym-api)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $baseDir = base_path('bisagym-api');

        if (! is_dir($baseDir)) {
            $this->error("Directory [{$baseDir}] not found.");
            return Command::FAILURE;
        }

        $python = $baseDir . DIRECTORY_SEPARATOR . '.venv' . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe';
        if (! file_exists($python)) {
            // Unix fallback
            $python = $baseDir . DIRECTORY_SEPARATOR . '.venv' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'python';
        }

        if (! file_exists($python)) {
            $this->error("Python virtualenv not found at [{$python}].");
            return Command::FAILURE;
        }

        $host = (string) $this->option('host');
        $port = (string) $this->option('port');
        $reload = $this->option('reload') ? '--reload' : '';

        $this->info("=================================================");
        $this->info(" Starting BisaGym Face Recognition API...");
        $this->info(" URL: http://{$host}:{$port}");
        $this->info(" Directory: {$baseDir}");
        $this->info(" Press Ctrl+C to stop.");
        $this->info("=================================================");

        $command = [
            $python,
            '-m',
            'uvicorn',
            'app.main:app',
            '--host',
            $host,
            '--port',
            $port,
        ];

        if ($this->option('reload')) {
            $command[] = '--reload';
        }

        $process = new Process($command, $baseDir, [
            'DATABASE_URL' => 'sqlite:///./gym.db',
        ], null, null);

        $process->setTty(Process::isTtySupported());

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->getExitCode() ?? Command::SUCCESS;
    }
}

