<?php

function newHeaderFormRequest($code)
{
    return "<?php

namespace Sibas\Http\Requests\\".ucfirst($code).";

use Sibas\Http\Requests\Request;

class HeaderFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        \$rules = [
            'payment_method'    => 'required',
            'bill_name'         => 'required',
            'bill_dni'          => 'required',
            'account_number'    => 'numeric',
            'policy_number'     => 'required',
        ];

        return \$rules;
    }
}
";
    
}

function newBeneficiaryFormRequest($code)
{
    return "<?php

namespace Sibas\Http\Requests\\".ucfirst($code).";

use Sibas\Http\Requests\Request;

class BeneficiaryFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        \$rules = [
            'type'         => 'required',
            'first_name'   => 'required|alpha_space',
            'dni'          => 'alpha_dash',
            'complement'   => 'alpha_num',
            'extension'    => 'exists:ad_cities,abbreviation',
            'age'          => 'numeric',
            'relationship' => 'required|alpha_space',
        ];

        if (\$this->request->get('type') == 'CV')
            \$rules['percentage'] = 'required|numeric|max:100|min:1';
            
        if (empty(\$this->request->get('last_name')) && empty(\$this->request->get('mother_last_name'))) {
            \$rules['any_last_name'] = 'required';
        } else {
            if (! empty(\$this->request->get('last_name'))) {
                \$rules['last_name']        = 'required|alpha_space';
                \$rules['mother_last_name'] = 'alpha_space';
            } elseif (! empty(\$this->request->get('mother_last_name'))) {
                \$rules['last_name']        = 'alpha_space';
                \$rules['mother_last_name'] = 'required|alpha_space';
            }
        }

        return \$rules;
    }
}
";
}

function newClientComplementFormRequest($code)
{
    return "<?php

namespace Sibas\Http\Requests\\".ucfirst($code).";

use Sibas\Http\Requests\Request;

class ClientComplementFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        \$hands           = join(',', array_keys(config('base.client_hands')));
        
        \$rules['home_address']              = 'required';
        \$rules['phone_number_home']         = 'numeric|digits_between:7,8';
        \$rules['ad_activity_id']            = 'required|exists:ad_activities,id';
        \$rules['occupation_description']    = 'required';
        \$rules['phone_number_mobile']       = 'numeric|digits_between:7,8';
        \$rules['email']                     = 'email';
        
        \$rules['workplace']                 = 'alpha_space';
        \$rules['height']                    = 'integer';
        \$rules['weight']                    = 'integer';
        \$rules['position']                  = 'alpha_space';
        \$rules['name_spouse']               = 'alpha_space';
        \$rules['personal_reference']        = 'alpha_space';
        
        if(!is_null(\$this->request->get('ad_plan_id')))
            \$rules['ad_plan_id'] = 'required';
        
        return \$rules;
    }
}
";
    
}

function newClientCreateFormRequest($code)
{
    return "<?php

namespace Sibas\Http\Requests\\".ucfirst($code).";

use Sibas\Http\Requests\Request;

class ClientCreateFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        \$hands           = join(',', array_keys(config('base.client_hands')));
        
        \$rules['first_name']                = 'required|alpha_space';
        \$rules['last_name']                 = 'required|alpha_space';
        \$rules['mother_last_name']          = 'alpha_space';
        \$rules['married_name']              = 'alpha_space';
        \$rules['dni']                       = 'required|alpha_dash';
        \$rules['complement']                = 'alpha';
        \$rules['extension']                 = 'required|exists:ad_cities,abbreviation';
        \$rules['birthdate']                 = 'required|date_format:d/m/Y';
        \$rules['birth_place']               = 'required';
        \$rules['place_residence']           = 'required';
        \$rules['business_address']          = 'required';
        \$rules['home_address']              = 'required';
        \$rules['ad_activity_id']            = 'required|exists:ad_activities,id';
        \$rules['occupation_description']    = 'required';
        \$rules['civil_status']              = 'required';
        \$rules['phone_number_mobile']       = 'numeric|digits_between:7,8';
        \$rules['email']                     = 'email';
        
        if(\$this->request->get('edition') == 0){
            \$rules['ad_plan_id']                = 'required';
            \$rules['payment_method']            = 'required';
        }
        
        return \$rules;
    }
}
";    
}