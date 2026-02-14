<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class BuildApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile frontend assets using npm run build';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running npm run build...');

        $process = new Process(['npm', 'run', 'build']);
        $process->setWorkingDirectory(base_path());
        $process->setTimeout(300); // 5 minutes

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            $this->error('Build failed!');
            return 1;
        }

        $this->info('Build completed successfully.');
        return 0;
    }
}
