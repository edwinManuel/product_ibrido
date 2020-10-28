<?php

namespace Sibas\Console\Commands\Product;

class GenerateFiles 
{
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->entity_path = app_path() . '/Console/Commands/Product/Helpers/Entities.php';
        $this->controller_path = app_path() . '/Console/Commands/Product/Helpers/Controllers.php';
        $this->request_path = app_path() . '/Console/Commands/Product/Helpers/Requests.php';
        $this->repository_path = app_path() . '/Console/Commands/Product/Helpers/Repositories.php';
        $this->blade_path = app_path() . '/Console/Commands/Product/Helpers/Blades.php';
        $this->route_path = app_path() . '/Console/Commands/Product/Helpers/Routes.php';
    }

    public function createFiles($code)
    {
        if(!file_exists(app_path('Entities/'.ucfirst($code)))){
            if(mkdir(app_path('Entities/'.ucfirst($code)))){
                $this->newEntities($code, 'Header');
                $this->newEntities($code, 'Beneficiary');
                $this->newEntities($code, 'Cancellation');
                $this->newEntities($code, 'Collection');
                $this->newEntities($code, 'Detail');
                $this->newEntities($code, 'Response');
            }
        }
        if(!file_exists(app_path('Http/Controllers/'.ucfirst($code)))){
            if(mkdir(app_path('Http/Controllers/'.ucfirst($code)))){
                $this->newController($code, 'Header');
                $this->newController($code, 'Beneficiary');
                $this->newController($code, 'Cancellation');
                $this->newController($code, 'Detail');
                $this->newController($code, 'Issue');
                $this->newController($code, 'PreApproved');
            }
        }
        if(!file_exists(app_path('Http/Requests/'.ucfirst($code)))){
            if(mkdir(app_path('Http/Requests/'.ucfirst($code)))){
                $this->newRequest($code, 'Header');
                $this->newRequest($code, 'Beneficiary');
                $this->newRequest($code, 'ClientComplement');
                $this->newRequest($code, 'ClientCreate');
            }
        }
        if(!file_exists(app_path('Repositories/'.ucfirst($code)))){
            if(mkdir(app_path('Repositories/'.ucfirst($code)))){
                $this->newRepositories($code, 'Header');
                $this->newRepositories($code, 'Beneficiary');
                $this->newRepositories($code, 'Cancellation');
                $this->newRepositories($code, 'Collection');
                $this->newRepositories($code, 'Detail');
                $this->newRepositories($code, 'Issue');
                $this->newRepositories($code, 'PreApproved');
            }
        }
        
        if(!file_exists(base_path('resources/views/'.$code))){
            $pathView = 'resources/views/'.$code;
            if(mkdir(base_path($pathView))){
                $this->newBlade($code, 'create', $pathView);
                $this->newBlade($code, 'edit', $pathView);
                $this->newBlade($code, 'issuance', $pathView);
                $this->newBlade($code, 'list', $pathView);
                $this->newBlade($code, 'result', $pathView);
            }
            $pathCancel = $pathView.'/cancellation';
            if(mkdir(base_path($pathCancel))){
                $this->newBlade($code, 'create', $pathCancel,'cancel');
                $this->newBlade($code, 'list', $pathCancel,'cancel');
            }
            $pathPartials = $pathView.'/partials';
            if(mkdir(base_path($pathPartials))){
                $this->newBlade($code, 'block-beneficiaries', $pathPartials, 'partial');
                $this->newBlade($code, 'block-generals', $pathPartials, 'partial');
                $this->newBlade($code, 'block-titular', $pathPartials, 'partial');
                $this->newBlade($code, 'block-tomador', $pathPartials, 'partial');
            }
            $pathPreaproved = $pathView.'/pre-approved';
            if(mkdir(base_path($pathPreaproved))){
                $this->newBlade($code, 'list', $pathPreaproved, 'preaproved');
            }
            $pathQuote = $pathView.'/quote';
            if(mkdir(base_path($pathQuote))){
                $this->newBlade($code, 'list', $pathQuote, 'quote');
            }
        }
        
        $pathCli = 'resources/views/client/'.$code;
        if(!file_exists(base_path($pathCli))){
            if(mkdir(base_path($pathCli))){
                $this->newBlade($code, 'edit-issue', $pathCli, 'client');
                $this->newBlade($code, 'edit', $pathCli, 'client');
                
                if(!file_exists(base_path($pathCli.'/partials')) && mkdir(base_path($pathCli.'/partials')))
                    $this->newBlade($code, 'inputs-quote', $pathCli.'/partials', 'client');
            }
        }
        
        $pathCert = 'resources/views/cert/'.$code;
        if(!file_exists(base_path($pathCert))){
            if(mkdir(base_path($pathCert))){
                $this->newBlade($code, 'certificate', $pathCert);
            }
        }
        
        if(!file_exists(app_path('Http/Routes/'.$code.'.issuance.php'))){
            $this->newRoute($code);
            
            $fichero = app_path('Http/routes.php');
            $actual = file_get_contents($fichero);
            $actual .= "require 'routes/".$code.".issuance.php';\n";
            file_put_contents($fichero, $actual);
        }
        
        echo '<br />--Archivos creados con exito-->'; 
    }
    
    public function newRoute($code)
    {
        require_once $this->route_path;
        $myfile = fopen(app_path('Http/Routes/'.$code.'.issuance.php'), "w") or die("Unable to open file!");
        fwrite($myfile, newRoutes($code));
        fclose($myfile);
    }
    
    public function newBlade($code, $type, $route, $adition=false)
    {
        require_once $this->blade_path;
        $myfile = fopen(base_path($route)."/".$type.".blade.php", "w") or die("Unable to open file!");
        $adition = $adition?ucfirst($adition):'';
        $func = 'new'. str_replace('-', '', ucfirst($type)). $adition.'Blade';
        fwrite($myfile, $func($code));
        fclose($myfile);
    }
    
    public function newRepositories($code, $type)
    {
        require_once $this->repository_path;
        $myfile = fopen(app_path('Repositories/'.ucfirst($code))."/".$type."Repository.php", "w") or die("Unable to open file!");
        $func = 'new'.$type.'Repository';
        fwrite($myfile, $func($code));
        fclose($myfile);
    }
    
    public function newRequest($code, $type)
    {
        require_once $this->request_path;
        $myfile = fopen(app_path('Http/Requests/'.ucfirst($code))."/".$type."FormRequest.php", "w") or die("Unable to open file!");
        $func = 'new'.$type.'FormRequest';
        fwrite($myfile, $func($code));
        fclose($myfile);
    }
    
    public function newController($code, $type)
    {
        require_once $this->controller_path;
        $myfile = fopen(app_path('Http/Controllers/'.ucfirst($code))."/".$type."Controller.php", "w") or die("Unable to open file!");
        $func = 'new'.$type.'Controller';
        fwrite($myfile, $func($code));
        fclose($myfile);
    }
    
    public function newEntities($code, $type)
    {
        require_once $this->entity_path;
        $myfile = fopen(app_path('Entities/'.ucfirst($code))."/$type.php", "w") or die("Unable to open file!");
        $func = 'new'.$type;
        fwrite($myfile, $func($code));
        fclose($myfile);
    }
    
    public function removeFiles($code)
    {
        if (file_exists(app_path('Entities/' . ucfirst($code))))
            system("rm -rf ".app_path('Entities/' . ucfirst($code)));

        if (file_exists(app_path('Http/Controllers/' . ucfirst($code))))
            system("rm -rf ".app_path('Http/Controllers/' . ucfirst($code)));

        if (file_exists(app_path('Http/Requests/' . ucfirst($code))))
            system("rm -rf ".app_path('Http/Requests/' . ucfirst($code)));

        if (file_exists(app_path('Repositories/' . ucfirst($code))))
            system("rm -rf ".app_path('Repositories/' . ucfirst($code)));

        if (file_exists(base_path('resources/views/' . $code)))
            system("rm -rf ".base_path('resources/views/' . $code));
        
        if (file_exists(base_path('resources/views/client/' . $code)))
            system("rm -rf ".base_path('resources/views/client/' . $code));
        
        if (file_exists(base_path('resources/views/cert/'.$code)))
            system("rm -rf ".base_path('resources/views/cert/'.$code));
        
        if (file_exists(app_path('Http/Routes/'.$code.'.issuance.php'))){
            system("rm -rf ".app_path('Http/Routes/'.$code.'.issuance.php'));
            $fichero = app_path('Http/routes.php');
            $actual = file_get_contents($fichero);
            $actual = str_replace("require 'routes/".$code.".issuance.php';\n", '', $actual);
            file_put_contents($fichero, $actual);
        }
    }
}
