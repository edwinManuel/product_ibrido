<?php

namespace Sibas\Console\Commands\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GenerateDB 
{
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->retailer = DB::table('ad_retailers')->where('active', true)->first();
        $this->company = DB::table('ad_companies')->where('active', true)->first();
        $this->table_path = app_path() . '/Console/Commands/Product/Helpers/Tables.php';
    }

    public function createProduct($code, $name)
    {
        $product            = $this->newProduct($code, $name);
        $companyProd        = $this->newCompanyProduct($product);
        $retailer_product   = $this->newRetailerProduct($companyProd->id);
        $certificate        = $this->newCertificate($retailer_product, $code);
        $content            = $this->newContent($retailer_product, $code, $name);
        $plan               = $this->newPlan($retailer_product);
        $policy             = $this->newPolicy($retailer_product,$code);
        $productParameter   = $this->newProductParameter($retailer_product);
        $productQuestion    = $this->newProductQuestion($retailer_product);
        $productActivity    = $this->newProductActivity($retailer_product);
        
        $this->createTable($code, 'op_'.$code.'_headers', 'header');
        $this->createTable($code, 'op_'.$code.'_details', 'detail');
        $this->createTable($code, 'op_'.$code.'_responses', 'response');
        $this->createTable($code, 'op_'.$code.'_collections', 'collection');
        $this->createTable($code, 'op_'.$code.'_cancellations', 'cancellation');
        $this->createTable($code, 'op_'.$code.'_beneficiaries', 'beneficiary');
    }

    public function removeProduct($code)
    {
        $product = DB::table('ad_products')->where('code', $code)->first();
        if($product){
            $companyProd        = $this->newCompanyProduct($product);
            $retailer_product   = $this->newRetailerProduct($companyProd->id);

            $this->dropTable('op_'.$code.'_beneficiaries');
            $this->dropTable('op_'.$code.'_cancellations');
            $this->dropTable('op_'.$code.'_collections');
            $this->dropTable('op_'.$code.'_responses');
            $this->dropTable('op_'.$code.'_details');
            $this->dropTable('op_'.$code.'_headers');
            
            DB::table('ad_retailer_product_activities') ->where('ad_retailer_product_id', $retailer_product->id)->delete();
            DB::table('ad_retailer_product_questions')  ->where('ad_retailer_product_id', $retailer_product->id)->delete();
            DB::table('ad_product_parameters')          ->where('ad_retailer_product_id', $retailer_product->id)->delete();
            DB::table('ad_policies')                    ->where('ad_retailer_product_id', $retailer_product->id)->delete();
            DB::table('ad_plans')                       ->where('ad_retailer_product_id', $retailer_product->id)->delete();
            DB::table('ad_contents')                    ->where('ad_retailer_product_id', $retailer_product->id)->delete();
            DB::table('ad_certificates')                ->where('ad_retailer_product_id', $retailer_product->id)->delete();
            DB::table('ad_retailer_products')           ->where('ad_company_product_id', $companyProd->id)->delete();
            DB::table('ad_company_products')            ->where('ad_company_id', $this->company->id)->where('ad_product_id', $product->id)->delete();
            DB::table('ad_retailer_user_products')      ->where('ad_product_id', $product->id)->delete();
            DB::table('ad_products')                    ->where('code', $code)->delete();
        }
    }
    
    public function newProductActivity($retailer_product)
    {
        $model = DB::table('ad_retailer_product_activities')
                ->where('ad_retailer_product_id', $retailer_product->id)
                ->get();
        if(!$model) {
            $arr = [
                'id' => $this->maxId('ad_retailer_product_activities')+1,
                'ad_retailer_product_id' => $retailer_product->id,
                'ad_activity_id' => $this->maxId('ad_activities'),
                'active' => true,
            ];
            DB::table('ad_retailer_product_activities')->insert($arr);
            return DB::table('ad_retailer_product_activities')->where('id', $arr['id'])->first();
        }else
            return $model[0];
    }
    
    public function newProductQuestion($retailer_product)
    {
        $model = DB::table('ad_retailer_product_questions')
                ->where('ad_retailer_product_id', $retailer_product->id)
                ->where('active', true)
                ->get();
        if(!$model) {
            $arr = [
                'id' => $this->maxId('ad_retailer_product_questions') + 1,
                'ad_retailer_product_id' => $retailer_product->id,
                'ad_question_id' => $this->maxId('ad_questions'),
                'order' => 1,
                'response' => false,
                'active' => true,
            ];
            DB::table('ad_retailer_product_questions')->insert($arr);
            return DB::table('ad_retailer_product_questions')->where('id', $arr['id'])->first();
        }else
            return $model[0];
    }
    
    public function newProductParameter($retailer_product)
    {
        $model = DB::table('ad_product_parameters')
                ->where('ad_retailer_product_id', $retailer_product->id)
                ->where('name', 'General')
                ->get();
        if(!$model) {
            $arr = [
                'id' => $this->maxId('ad_product_parameters') + 1,
                'ad_retailer_product_id' => $retailer_product->id,
                'name' => 'General',
                'slug' => 'GE',
                'age_min' => 18,
                'age_max' => 70,
                'amount_min' => 1,
                'amount_max' => 2000000,
                'expiration' => 30,
                'detail' => 1,
            ];
            DB::table('ad_product_parameters')->insert($arr);
            return DB::table('ad_product_parameters')->where('id', $arr['id'])->first();
        }else
            return $model[0];
    }
    
    public function newPolicy($retailer_product,$code)
    {
        $model = DB::table('ad_policies')
                ->where('ad_retailer_product_id', $retailer_product->id)
                ->where('active', true)
                ->get();
        if(!$model) {
            $arr = [
                'id' => $this->maxId('ad_policies') + 1,
                'ad_retailer_product_id' => $retailer_product->id,
                'number' => strtoupper($code).'-BS',
                'end_policy' => '0',
                'date_begin' => date('Y-m-d'),
                'date_end' => date("Y-m-d",strtotime(date('Y-m-d')."+ 1 year")),
                'currency' => 'BS',
                'modality' => strtoupper($code),
                'active' => true,
            ];
            DB::table('ad_policies')->insert($arr);
            return DB::table('ad_policies')->where('id', $arr['id'])->first();
        }else
            return $model[0];
    }
    
    public function newPlan($retailer_product)
    {
        $model = DB::table('ad_plans')
                ->where('ad_retailer_product_id', $retailer_product->id)
                ->get();
        if(!$model) {
            $arr = [
                'id' => $this->maxId('ad_plans') + 1,
                'ad_retailer_product_id' => $retailer_product->id,
                'name' => 'plan A',
                'plan' => 'A',
            ];
            DB::table('ad_plans')->insert($arr);
            return DB::table('ad_plans')->where('id', $arr['id'])->first();
        }else
            return $model[0];
    }
    
    public function newContent($retailer_product, $code, $name)
    {
        $model = DB::table('ad_contents')
                ->where('ad_retailer_product_id', $retailer_product->id)
                ->get();
        if(!$model) {
            $arr = [
                'id' => $this->maxId('ad_contents') + 1,
                'ad_retailer_product_id' => $retailer_product->id,
                'title' => strtoupper(str_replace('-', ' ', $name)),
                'content' => strtoupper(str_replace('-', ' ', $name)),
            ];
            DB::table('ad_contents')->insert($arr);
            return DB::table('ad_contents')->where('id', $arr['id'])->first();
        }else
            return $model[0];
    }
    
    public function newCertificate($retailer_product, $code)
    {
        $model = DB::table('ad_certificates')
                ->where('ad_retailer_product_id', $retailer_product->id)
                ->where('active', true)
                ->get();
        if(!$model) {
            $arr = [
                'id' => $this->maxId('ad_certificates') + 1,
                'ad_retailer_product_id' => $retailer_product->id,
                'version' => 1,
                'template' => 'certificate',
                'css' => 'estilo',
                'active' => true,
            ];
            DB::table('ad_certificates')->insert($arr);
            return DB::table('ad_certificates')->where('id', $arr['id'])->first();
        }else
            return $model[0];
    }
    
    public function newRetailerProduct($ad_company_product_id)
    {
        $model = DB::table('ad_retailer_products')
                ->where('ad_retailer_id', $this->retailer->id)
                ->where('ad_company_product_id', $ad_company_product_id)
                ->where('active', true)->get();
        if(!$model) {
            $arr = [
                'id' => date('U'),
                'ad_retailer_id' => $this->retailer->id,
                'ad_company_product_id' => $ad_company_product_id,
                'type' => 'MP',
                'warranty' => true,
                'billing' => true,
                'active' => true,
            ];
            DB::table('ad_retailer_products')->insert($arr);
            return DB::table('ad_retailer_products')->where('id', $arr['id'])->first();
        }else
            return $model[0];
    }
    
    public function newCompanyProduct($product)
    {
        $model = DB::table('ad_company_products')
                ->where('ad_company_id', $this->company->id)
                ->where('ad_product_id', $product->id)
                ->where('active', true)->get();
        if(!$model) {
            $arr = [
                'id' => $this->maxId('ad_company_products') + 1,
                'ad_company_id' => $this->company->id,
                'ad_product_id' => $product->id,
                'active' => true,
            ];
            DB::table('ad_company_products')->insert($arr);
            return DB::table('ad_company_products')->where('id', $arr['id'])->first();
        }else
            return $model[0];
    }
    
    public function newProduct($code, $name)
    {
        if(!DB::table('ad_products')->where('code', $code)->get()) {
            $Product = [
                'id' => $this->maxId('ad_products') + 1,
                'type' => 'PH',
                'name' => str_replace('-', ' ', ucfirst($name)),
                'code' => $code,
                'slug' => $name,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            DB::table('ad_products')->insert($Product);
            return DB::table('ad_products')->where('code', $code)->first();
        }else
            return DB::table('ad_products')->where('code', $code)->first();
    }
    
    function maxId($table, $field=false)
    {
        $fieldT = $field?$field:'id';
        return DB::table($table)->max($fieldT);
    }
    
    function createTable($code, $table, $action)
    {
        require_once $this->table_path;
        $function = 'new'. ucfirst($action).'Table';
        if(!Schema::hastable($table))
            DB::unprepared($function($code));
    }
    
    function dropTable($table)
    {
        if(Schema::hastable($table))
            DB::unprepared('DROP TABLE '.$table);
    }
}
