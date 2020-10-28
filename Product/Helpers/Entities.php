<?php

function newHeader($code)
{
    return '<?php

namespace Sibas\Entities\\'.ucfirst($code).';
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sibas\Entities\Certificate;
use Sibas\Entities\Plan;
use Sibas\Entities\RetailerProduct;
use Sibas\Entities\RetailerCityAgency;
use Sibas\Entities\User;

class Header extends Model
{

    protected $table = "op_'.$code.'_headers";

    public $incrementing = false;

    protected $casts = [
        "issued"   => "boolean",
        "canceled" => "boolean",
    ];

    protected $fillable = [
        "ad_retailer_product_id",
        "ad_certificate_id",
        "quote_number",
        "warranty",
        "validity_start",
        "validity_end",
        "type",
        "issue_number",
        "prefix",
        "policy_number",
        "ad_certificate_id",
        "issued",
        "date_issue",
        "bill_name",
        "bill_dni",
        "pre_printed",
        "pre_printed_number",
        "term",
        "type_term",
        "pledged_number",
        "pledged",
        "payment_method",
        "period",
        "case_number",
        "premium",
        "annual_premium",
        "total_premium",
        "ad_retailer_city_agency_id",
        "currency",
    ];
    
    protected $appends = [
        "questions",
        "complet_signature",
        "created_date",
        "days_from_creation",
    ];


    public function user()
    {
        return $this->belongsTo(User::class, "ad_user_id", "id");
    }


    public function plan()
    {
        return $this->belongsTo(Plan::class, "ad_plan_id", "id");
    }


    public function details()
    {
        return $this->hasMany(Detail::class, "op_'.$code.'_header_id", "id");
    }
    
    public function cancellation()
    {
        return $this->hasOne(Cancellation::class, "op_'.$code.'_header_id", "id");
    }


    public function retailerProduct()
    {
        return $this->belongsTo(RetailerProduct::class, "ad_retailer_product_id", "id");
    }
    
    public function retailerCityAgency()
    {
        return $this->belongsTo(RetailerCityAgency::class, "ad_retailer_city_agency_id", "id");
    }

    public function certificate()
    {
        return $this->belongsTo(Certificate::class, "ad_certificate_id", "id");
    }
    
    public function getQuestionsAttribute()
    {
        $i = 0;
        foreach ($this->details()->get() as $key => $value) {
            if($value->response)
                $i++;
        }
        
        if($this->details()->get()->count() == $i)
            return true;
        else
            return false;
    }
    
    public function getCreatedDateAttribute()
    {
        return Carbon::createFromTimestamp(strtotime($this->created_at))->format("d/m/Y H:i a");
    }
    
    public function getDaysFromCreationAttribute()
    {
        $date_now    = Carbon::now();
        $date_create = Carbon::createFromTimestamp(strtotime($this->created_at));

        return $date_now->diffInDays($date_create);
    }
    
    public function getCompletSignatureAttribute()
    {
        foreach ($this->details()->get() as $key => $detail) {
            
            if($detail->signature == "")
                return false;
        }
        return true;
    }
}
';   
}

function newBeneficiary($code)
{
    return "<?php

namespace Sibas\Entities\\".ucfirst($code).";

use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    protected \$table = 'op_".$code."_beneficiaries';

    public \$incrementing = false;

    protected \$fillable = [
        'id',
        'coverage',
        'first_name',
        'last_name',
        'mother_last_name',
        'dni',
        'extension',
        'relationship',
        'percentage',
    ];

    protected \$hidden = [
        'op_".$code."_detail_id',
        'created_at',
        'updated_at',
    ];
    
    protected \$appends = [
        'full_name',
        'full_dni',
    ];

    public function setFirstNameAttribute(\$value)
    {
        \$this->attributes[ 'first_name' ] = mb_strtoupper(\$value);
    }

    public function setLastNameAttribute(\$value)
    {
        \$this->attributes[ 'last_name' ] = mb_strtoupper(\$value);
    }

    public function setMotherLastNameAttribute(\$value)
    {
        \$this->attributes[ 'mother_last_name' ] = mb_strtoupper(\$value);
    }

    public function setDniAttribute(\$value)
    {
        \$this->attributes[ 'dni' ] = mb_strtoupper(\$value);
    }

    public function setRelationshipAttribute(\$value)
    {
        \$this->attributes[ 'relationship' ] = mb_strtoupper(\$value);
    }

    public function getFullNameAttribute()
    {
        return \$this->first_name . ' ' . \$this->last_name . ' ' . \$this->mother_last_name;
    }

    public function getFullDniAttribute()
    {
        return \$this->dni . ' ' . \$this->extension;
    }
}";
}

function newCancellation($code)
{
    return "<?php

namespace Sibas\Entities\\".ucfirst($code).";

use Illuminate\Database\Eloquent\Model;
use Sibas\Entities\User;
use Sibas\Entities\Annulled;

class Cancellation extends Model
{
    protected \$table = 'op_".$code."_cancellations';

    public \$incrementing = false;

    protected \$fillable = [
        'id',
        'op_".$code."_header_id',
        'ad_annulleds_id',
        'ad_user_id',
        'reason',
    ];


    public function user()
    {
        return \$this->belongsTo(User::class, 'ad_user_id', 'id');
    }

    public function Annulled()
    {
        return \$this->belongsTo(Annulled::class, 'ad_annulleds_id', 'id');
    }
    
    public function setReasonAttribute(\$value)
    {
        \$this->attributes['reason'] = mb_strtoupper(\$value);
    }
}
";
}

function newCollection($code)
{
    return "<?php

namespace Sibas\Entities\\".ucfirst($code).";

use Illuminate\Database\Eloquent\Model;


class Collection extends Model
{
    protected \$table = 'op_".$code."_collections';

    public \$incrementing = false;

    protected \$fillable = [
        'id',
        'op_".$code."_header_id',
        'fee_number',
        'fee_date',
        'deadline',
        'fee_amount',
        'charged',
        'transaction_number',
        'transaction_date',
        'transaction_amount',
        'reason',
    ];

    protected \$casts = [
        'charged' => 'boolean',
    ];

    public function header()
    {
        return \$this->belongsTo(Header::class, 'op_".$code."_header_id', 'id');
    }
}
";
}

function newDetail($code)
{
    return "<?php

namespace Sibas\Entities\\".ucfirst($code).";

use Illuminate\Database\Eloquent\Model;
use Sibas\Entities\Client;

class Detail extends Model
{

    protected \$table = 'op_".$code."_details';

    public \$incrementing = false;

    protected \$fillable = [
        'id',
        'op_client_id',
        'insured_value',
        'currency',
        'client_code',
        'taker_name',
        'taker_dni',
        'holder',
        'signature',
        'expiration_card_year',
        'expiration_card_month',
    ];

    protected \$appends = [
        'completed',
    ];

    public function client()
    {
        return \$this->belongsTo(Client::class, 'op_client_id', 'id');
    }

    public function header()
    {
        return \$this->belongsTo(Header::class, 'op_".$code."_header_id', 'id');
    }

    public function response()
    {
        return \$this->hasOne(Response::class, 'op_".$code."_detail_id', 'id');
    }

    public function beneficiary()
    {
        return \$this->hasOne(Beneficiary::class, 'op_".$code."_detail_id', 'id');
    }

    public function beneficiaries()
    {
        return \$this->hasMany(Beneficiary::class, 'op_".$code."_detail_id', 'id');
    }

    public function getCompletedAttribute()
    {
        /*if(empty(\$this->client->workplace)){
            return false;
        }else*/
            return true;
    }
}
";
}

function newResponse($code)
{
    return "<?php

namespace Sibas\Entities\\".ucfirst($code).";

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    protected \$table = 'op_".$code."_responses';

    public \$incrementing = false;

    protected \$fillable = [
        'id',
        'response',
        'observation',
    ];
    
    public function detail()
    {
        return \$this->belongsTo(Detail::class, 'op_".$code."_detail_id', 'id');
    }
}
";
    
}