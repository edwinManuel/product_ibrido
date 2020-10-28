<?php

function newHeaderRepository($code)
{
    return "<?php
namespace Sibas\Repositories\\".ucfirst($code).";

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Sibas\Entities\\".ucfirst($code)."\Header;
use Sibas\Entities\RetailerProduct;
use Sibas\Repositories\BaseRepository;

class HeaderRepository extends BaseRepository
{
    public function getHeaderById(\$header_id)
    {
        \$this->model = Header::with([
            'details.client',
            'details.beneficiaries',
            'details.response',
            'user.city', 
        ])->where('id', '=', \$header_id)->get();
        
        if (\$this->model->count() === 1) {
            \$this->model = \$this->model->first();

            return true;
        }

        return false;
    }
    
    public function storeHeader(\$request)
    {
        \$this->data = \$request->all();

        \$this->model  = new Header();
        \$quote_number = \$this->getNumber('Q');

        \$date = \$this->carbon->createFromTimestamp(strtotime(str_replace('/', '-', date('Y-m-d'))));
        
        \$this->model->id             = date('U');
        \$this->model->ad_user_id     = \$request->user()->id;
        \$this->model->type           = 'Q';
        \$this->model->quote_number   = \$quote_number;
        \$this->model->warranty       = true;
        \$this->model->validity_start = \$date->format('Y-m-d');
        \$this->model->validity_end   = \$date->addYear(1)->format('Y-m-d');
        \$this->model->ad_plan_id     = \$this->data['ad_plan_id'];
        \$this->model->payment_method = \$this->data['payment_method'];
        \$this->model->currency       = 'BS';
        
        if ( ! \$this->checkNumber('Q', \$quote_number)) {
            return \$this->saveModel();
        }

        return false;
    }
    
    public function updateHeader(Request \$request, \$retailerProduct)
    {
        if (\$this->getCertificate(\$retailerProduct)) {
            \$this->data = \$request->all();
            
            try {
                \$issue_number = \$this->getNumber('I');
                
                if ( ! \$this->checkNumber('I', \$issue_number)) {
                    
                    \$this->model->update([
                        'type'              => 'I',
                        'issue_number'      => \$issue_number,
                        'policy_number'     => \$this->data['policy_number'],
                        'prefix'            => strtoupper(\$retailerProduct->companyProduct->product->code),
                        'bill_name'         => \$this->data['bill_name'],
                        'bill_dni'          => \$this->data['bill_dni'],
                        'ad_certificate_id' => \$this->certificate->id,
                        'term'              => \$this->data['term'],
                        'type_term'         => 'M',
                        'bill_name'         => \$this->data['bill_name'],
                        'bill_dni'          => \$this->data['bill_dni'],
                        'premium'           => \$this->model->plan->monthly_premium,
                        'annual_premium'    => \$this->model->plan->annual_premium,
                        'payment_method'    => \$this->data['payment_method'],
                        'total_premium'     => round(\$this->data['term'] * \$this->model->plan->monthly_premium),
                    ]);
                    
                    return true;
                }
            } catch (QueryException \$e) {
                \$this->errors = \$e->getMessage();
                
            }
        }

        return false;
    }
    
    public function setRetailerProduct(\$retailerProduct)
    {
        try {   
            \$this->model->update([
                'ad_retailer_product_id' => \$retailerProduct->id,
            ]);

            return true;

        } catch (QueryException \$e) {
            \$this->errors = \$e->getMessage();

        }

        return false;
    }
    
    public function issuanceHeader()
    {
        try {
            \$this->model->update([
                'issued'     => true,
                'date_issue' => date('Y-m-d H:i:s'),
                'ad_retailer_city_agency_id' => \$this->model->user->agency->retailerCityAgencies->first()->id,
                //'approved'   => true,
            ]);

            return true;
        } catch (QueryException \$e) {
            \$this->errors = \$e->getMessage();
        }

        return false;
    }
}
";
}

function newBeneficiaryRepository($code)
{
    return "<?php

namespace Sibas\Repositories\\".ucfirst($code).";

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Sibas\Entities\\".ucfirst($code)."\Beneficiary;
use Sibas\Repositories\BaseRepository;

class BeneficiaryRepository extends BaseRepository
{
    public function storeBeneficiary(\$request)
    {
        \$this->data  = \$request->all();
        \$this->model = \$this->data['detail'];

        \$this->data['beneficiary_id'] = date('U');

        try {
            \$this->model->beneficiary()->create(\$this->setData());
        } catch (QueryException \$e) {
            \$this->errors = \$e->getMessage();
        }

        return \$this->saveModel();
    }

    public function updateBeneficiary(\$request)
    {
        \$this->data  = \$request->all();
        try {
            \$this->model->update(\$this->setData());
        } catch (QueryException \$e) {
            \$this->errors = \$e->getMessage();
        }

        return \$this->saveModel();
    }

    public function getBeneficiaryById(\$beneficiary_id)
    {
        \$this->model = Beneficiary::where('id', \$beneficiary_id)->first();

        if ( ! is_null(\$this->model)) {
            return true;
        }

        return false;
    }

    private function setData()
    {
        \$dni        = empty(\$this->data['dni']) ? '' : mb_strtoupper(\$this->data['dni']);
        \$complement = empty(\$this->data['complement']) ? '' : '-' . mb_strtoupper(\$this->data['complement']);
        \$extension  = empty(\$this->data['extension']) ? '' : mb_strtoupper(\$this->data['extension']);

        return [
            'id'               => \$this->data['beneficiary_id'],
            'coverage'         => \$this->data['type'],
            'percentage'       => (\$this->data['percentage']==0)?100:\$this->data['percentage'],
            'first_name'       => mb_strtoupper(\$this->data['first_name']),
            'last_name'        => mb_strtoupper(\$this->data['last_name']),
            'mother_last_name' => mb_strtoupper(\$this->data['mother_last_name']),
            'dni'              => \$dni . \$complement,
            'extension'        => \$extension,
            'relationship'     => mb_strtoupper(\$this->data['relationship']),
        ];
    }
    
    public function removeBeneficiary() 
    {
        try {
            \$this->model->delete();

            return true;
        } catch (QueryException \$e) {
            \$this->errors = \$e->getMessage();
        }
        return false;
    }
}
";
}

function newCancellationRepository($code)
{
    return "<?php

namespace Sibas\Repositories\\".ucfirst($code).";

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sibas\Entities\\".ucfirst($code)."\Header;
use Sibas\Entities\RetailerProduct;
use Sibas\Http\Controllers\ReportTrait;
use Sibas\Repositories\BaseRepository;

class CancellationRepository extends BaseRepository
{
    use ReportTrait;

    public function getHeaderList(Request \$request)
    {
        \$headers = Header::with('details.client', 'user.city', 'user.agency')
            ->where('type', 'I')
            ->where('issued', true)
            ->where('canceled', false);
        
        \$editPolicy = \$request->user()->permissions->contains(function (\$key, \$item) {
                    return \$item->slug === 'EDPO';
                });
                
        \$date = \$this->carbon->createFromFormat('Y-m-d', date('Y-m-d'));
        
        if(!\$editPolicy)
            \$headers->whereDate('date_issue', '=', \$this->carbon->now()->format('Y-m-d'))->count();
            
        \$this->filtersByHeader(\$request, \$headers);

        return \$headers->paginate(50);
    }


    public function data()
    {
        \$select = [
            'id'   => '',
            'name' => 'Seleccione...',
        ];

        \$users = DB::table('ad_users')
            ->select([
                'ad_users.username as id',
                'ad_users.full_name as name',
            ])
            ->join('ad_user_types', 'ad_users.ad_user_type_id', '=', 'ad_user_types.id')
            ->whereNotIn('username', ['admin', 'guest'])
            ->where('ad_user_types.code', 'UST')
            ->get();

        \$cities = DB::table('ad_cities')
            ->select([
                'ad_cities.slug as id',
                'ad_cities.name',
            ])
            ->where('type_de', true)
            ->get();

        \$agencies = DB::table('ad_agencies')
            ->select([
                'ad_agencies.slug as id',
                'ad_agencies.name',
                'ad_retailer_cities.ad_city_id as city',
            ])
            ->join('ad_retailer_city_agencies', 'ad_agencies.id', '=', 'ad_retailer_city_agencies.ad_agency_id')
            ->join('ad_retailer_cities', 'ad_retailer_city_agencies.ad_retailer_city_id', '=', 'ad_retailer_cities.id')
            ->get();

        \$products = \$this->getProducts();

        array_unshift(\$users, \$select);
        array_unshift(\$cities, \$select);
        array_unshift(\$agencies, \$select);

        \$type_terms = config('base.term_types');

        return compact('users', 'agencies', 'cities', 'type_terms', 'products');
    }


    public function getProducts()
    {
        return RetailerProduct::with('companyProduct.product')
            ->where('type', 'MP')
            ->where('active', true)
            ->get()
            ->pluck('companyProduct.product.name', 'idc')
            ->toArray();
    }

    public function storeCancellation(Request \$request, \$header)
    {
        \$user        = \$request->user();
        \$this->data  = \$request->all();
        \$this->model = \$header;

        \$this->model->cancellation()->create([
            'id'                => date('U'),
            'op_vp_header_id'   => \$header->id,
            'ad_annulleds_id'   => \$this->data['ad_annulleds_id'],
            'ad_user_id'        => \$user->id,
            'reason'            => \$this->data[ 'reason' ],
        ]);

        \$this->model->canceled = true;

        return \$this->saveModel();
    }
}";
}

function newCollectionRepository($code)
{
    return "<?php

namespace Sibas\Repositories\\".ucfirst($code).";

use Illuminate\Http\Request;
use Sibas\Entities\\".ucfirst($code)."\Collection;
use Sibas\Repositories\BaseRepository;

class CollectionRepository extends BaseRepository
{
    public function storeCollections(\$header, \$cobro = 0)
    {
        \$id = date('U');

        switch (\$header->period){
            case 'Y':
                \$m = 1;
                \$add = 0;
                \$premium = (\$header->premium * \$header->term);
                break;
            case 'S':
                \$m = round(\$header->term / 6);
                \$add = 6;
                \$premium = (\$header->total_premium / \$m);
                break;
            case 'M':
                \$m = 12;
                \$add = 1;
                \$premium = \$header->premium;
                break;
        }

        \$date = date('Y-m-d');

        for (\$x = 1; \$x <= \$m; \$x++) {
            \$this->model = new Collection();
            \$this->model->id = \$id;
            \$this->model->op_".$code."_header_id = \$header->id;
            \$this->model->fee_number = \$x;
            \$this->model->fee_date = \$date;
            \$this->model->fee_amount = \$premium;

            if (\$cobro > 0){
                \$this->model->charged = true;
                \$this->model->transaction_number = \$cobro;
                \$this->model->transaction_date = \$date;
                \$this->model->transaction_amount = \$premium;
            }
            
            \$date = date('Y-m-d',strtotime(\$date.'+'.\$add.' month'));

            \$this->saveModel();
            \$id++;
        }
    }
}";
}

function newDetailRepository($code)
{
    return "<?php

namespace Sibas\Repositories\\".ucfirst($code).";

use Carbon\Carbon;
use Illuminate\Http\Request;
use Sibas\Entities\\".ucfirst($code)."\Detail;
use Sibas\Repositories\BaseRepository;

class DetailRepository extends BaseRepository
{
    public function createDetail(\$request)
    {
        \$this->data = \$request->all();
        \$header     = \$this->data['header'];
        \$client     = \$this->data['client'];

        \$this->model = new Detail();

        \$this->model->id                = date('U');
        \$this->model->op_".$code."_header_id   = \$header->id;
        \$this->model->op_client_id      = \$client->id;
        \$this->model->holder            = true;
        
        return \$this->saveModel();
    }
    
    public function getDetailById(\$detail_id)
    {
        \$this->model = Detail::with([ 'client', 'response', 'beneficiaries', 'beneficiary' ])
            ->where('id', \$detail_id)
            ->get();

        if (\$this->model->count() === 1) {
            \$this->model = \$this->model->first();

            return true;
        }

        return false;
    }
    
    public function editDetail(\$request)
    {
        \$this->data = \$request->all();
        \$header     = \$this->data['header'];
        
        return \$this->saveModel();
    }
    
    public function updateHolder(\$holder)
    {
        \$this->model->holder = \$holder;
        
        return \$this->saveModel();
    }
    
    public function updateAccount(\$request)
    {
        \$this->data = \$request->all();

        \$tarjetaencriptada = 0;
        
        \$this->model->account_number    = \$this->data[ 'account_number' ];
        
        return \$this->saveModel();
    }
    
    public function checkQuestion(\$detail)
    {
        if(is_null(\$detail->response))
            return false;
        
        foreach (json_decode(\$detail->response->response) as \$key => \$value) {
            if(\$value->response == true)
                return false;
        }
        return true;
    }
    
    public function checkIssueByClient(\$detail)
    {
        \$model = Detail::whereHas('header', function (\$q) {
                            \$q->where('type', 'I')
                                ->where('issued', true)
                                ->where('canceled', false);
                        })
            ->where('op_client_id', \$detail->client->id)
            ->get();
                        
        if(\$model->count()>0)
            return true;
        else
            return false;
    }

}
";
}

function newIssueRepository($code)
{
    return "<?php

namespace Sibas\Repositories\\".ucfirst($code).";

use Sibas\Entities\\".ucfirst($code)."\Header;
use Sibas\Http\Controllers\ReportTrait;
use Sibas\Repositories\BaseRepository;

class IssueRepository extends BaseRepository
{
    use ReportTrait;

    public function getHeaderList(\$request)
    {
        \$headers = Header::with([
            'details.client',
            'user.city',
            'user.agency',
        ])
            ->where('type', 'Q');

        \$this->filtersByHeader(\$request, \$headers);

        return \$headers->get();
    }
}";
}

function newPreApprovedRepository($code)
{
    return "<?php

namespace Sibas\Repositories\\".ucfirst($code).";

use Illuminate\Http\Request;
use Sibas\Entities\\".ucfirst($code)."\Header;
use Sibas\Http\Controllers\ReportTrait;
use Sibas\Repositories\BaseRepository;

class PreApprovedRepository extends BaseRepository
{
    use ReportTrait;

    public function getHeaderList(Request \$request)
    {
        \$headers = Header::with('details.client', 'user.city', 'user.agency')
            ->where('type', 'I')
            ->where('issued', false)
            ->where('canceled', false);
        
        \$this->filtersByHeader(\$request, \$headers);

        return \$headers->get();
    }
}";
}