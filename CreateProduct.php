<?php

namespace Sibas\Console\Commands;

use Illuminate\Console\Command;

class CreateProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create_product {prod} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando que crea nuevo producto';

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
        $generateDB->createProduct($this->argument('prod'),$this->argument('name'));
        
        $generateFiles = new Product\GenerateFiles();
        if($this->argument('prod'))
            $generateFiles->createFiles($this->argument('prod'));
        
    }
}
