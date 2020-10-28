<?php

namespace Sibas\Console\Commands;

use Illuminate\Console\Command;

class RemoveProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove_product {prod}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
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
        $generateDB = new Product\GenerateDB();
        $generateDB->removeProduct($this->argument('prod'));
        
        $generateFiles = new Product\GenerateFiles();
        $generateFiles->removeFiles($this->argument('prod'));
    }
}
