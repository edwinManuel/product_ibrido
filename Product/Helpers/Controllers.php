<?php

function newHeaderController($code)
{
    return "<?php

namespace Sibas\Http\Controllers\\".ucfirst($code).";

use Illuminate\Auth\Guard;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Sibas\Entities\\".ucfirst($code)."\Header;
use Sibas\Entities\Client;
use Sibas\Entities\ProductParameter;
use Sibas\Http\Controllers\DataTrait;
use Sibas\Http\Controllers\MailController;
use Sibas\Http\Requests;
use Sibas\Http\Requests\\".ucfirst($code)."\HeaderFormRequest;
use Sibas\Http\Controllers\Controller;
use Sibas\Repositories\Retailer\RetailerProductRepository;
use Sibas\Repositories\\".ucfirst($code)."\HeaderRepository;
use Sibas\Repositories\\".ucfirst($code)."\DetailRepository;
use Sibas\Repositories\\".ucfirst($code)."\CollectionRepository;
use Sibas\Repositories\Retailer\PolicyRepository;
use Sibas\Repositories\Client\ClientRepository;
use Sibas\Repositories\WsRepository;

class HeaderController extends Controller
{

    /**
     * @var HeaderRepository
     */
    protected \$repository;

    /**
     * @var RetailerProductRepository
     */
    protected \$retailerProductRepository;

    /**
     * @var ClientRepository
     */
    protected \$clientRepository;

    /**
     * @var DetailRepository
     */
    protected \$detailRepository;

    /**
     * @var PolicyRepository
     */
    protected \$policyRepository;

    /**
     * @var CollectionRepository
     */
    protected \$collectionRepository;

    /**
     * @var WsRepository
     */
    protected \$wsRepository;

    public function __construct(
        HeaderRepository \$repository,
        RetailerProductRepository \$retailerProductRepository,
        ClientRepository \$clientRepository,
        DetailRepository \$detailRepository,
        PolicyRepository \$policyRepository,
        CollectionRepository \$collectionRepository,
        WsRepository \$wsRepository
    ) {
        \$this->repository                   = \$repository;
        \$this->retailerProductRepository    = \$retailerProductRepository;
        \$this->clientRepository             = \$clientRepository;
        \$this->detailRepository             = \$detailRepository;
        \$this->policyRepository             = \$policyRepository;
        \$this->collectionRepository         = \$collectionRepository;
        \$this->wsRepository                 = \$wsRepository;
    }
    
    use DataTrait;
    
    public function lists(\$rp_id, \$header_id)
    {
        \$header = null;

        if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id))) {

            if (\$this->repository->getHeaderById(decode(\$header_id))) {
                \$header = \$this->repository->getModel();
                
                \$user            = auth()->user();
                \$retailerProduct = \$this->retailerProductRepository->getModel();
                
                \$parameter       = \$retailerProduct->parameters->filter(function (\$item) {
                    return \$item->slug === 'GE';
                })->first();
                
                \$coverage_detail = (\$parameter->detail)?\$parameter->detail:0;
                
            }

            return view(\$retailerProduct->companyProduct->product->code.'.list',
                compact('rp_id', 'header_id', 'header', 'coverage_detail', 'user', 'retailerProduct'));
        }

        return redirect()->back();
    }
    
    public function result(\$rp_id, \$header_id)
    {
        if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id))) {
            \$retailerProduct = \$this->retailerProductRepository->getModel();
           
            if (\$this->repository->getHeaderById(decode(\$header_id)) && \$this->repository->setRetailerProduct(\$retailerProduct)) {
                \$header    = \$this->repository->getModel();
                \$user      = request()->user();
                #actualiza tasas y primas

                return view(\$retailerProduct->companyProduct->product->code.'.result', compact('rp_id', 'header_id', 'header', 'retailerProduct'));
            }
        }

        return redirect()->back()->with([ 'error_header' => 'La tasa no pudo ser registrada' ]);
    }
    
    public function edit(\$rp_id, \$header_id)
    {
        if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id))
            && \$this->repository->getHeaderById(decode(\$header_id))) {
            \$retailerProduct = \$this->retailerProductRepository->getModel();
            \$header          = \$this->repository->getModel();
            
            \$data = \$this->getData(\$rp_id);
            
            \$data['policies'] = \$this->policyRepository->getPolicyForIssuance(decode(\$rp_id), \$header->currency);
            
            return view(\$retailerProduct->companyProduct->product->code.'.edit', compact('rp_id', 'header_id', 'header', 'data', 'retailerProduct'));
        }

        return redirect()->back();
    }
    
    public function update(HeaderFormRequest \$request, \$rp_id, \$header_id)
    {
        if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id)) && \$this->repository->getHeaderById(decode(\$header_id))) {
            \$retailerProduct = \$this->retailerProductRepository->getModel();
            \$header = \$this->repository->getModel();
            
                if(\$this->detailRepository->checkIssueByClient(\$header->details->first()) == false) {
                    
                    if (\$this->repository->updateHeader(\$request, \$retailerProduct)) {
                        \$header = \$this->repository->getModel();

                        if (\$this->detailRepository->getDetailById(\$header->details->first()->id)) {
                            \$detail = \$this->detailRepository->getModel();

                            return redirect()->route(\$retailerProduct->companyProduct->product->code . '.edit', [
                                        'rp_id' => \$rp_id,
                                        'header_id' => \$header_id,
                                    ])->with(['success_header' => 'La Póliza fue actualizada con éxito.']);
                        }
                    }
                }else
                   return redirect()->back()->with([ 'error_header' => 'El Cliente ya cuenta con un seguro vigente' ]); 
        }

        return redirect()->back()->withInput();
    }
    
    public function issuance(\$rp_id, \$header_id)
    {
        if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id)) && \$this->repository->getHeaderById(decode(\$header_id))) {
            
            \$retailerProduct = \$this->retailerProductRepository->getModel();
            \$header = \$this->repository->getModel();
            \$cobro = 0;
            
            if(\$this->detailRepository->checkIssueByClient(\$header->details->first()) == false){
                if (\$this->repository->issuanceHeader()) {
                    \$this->collectionRepository->storeCollections(\$header, \$cobro);
                    return redirect()->route(\$retailerProduct->companyProduct->product->code.'.show.issuance', [
                        'rp_id'     => \$rp_id,
                        'header_id' => \$header_id,
                    ])->with([ 'success_header' => 'La Póliza fue emitida con éxito. ' ]);
                }    
            }else
                return redirect()->back()->with([ 'error_header' => 'El Cliente ya cuenta con un seguro de \"Protección Covid\" vigente' ]); 
            
        }

        return redirect()->back();
    }
    
    public function showIssuance(\$rp_id, \$header_id)
    {
        if(\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id)) && \$this->repository->getHeaderById(decode(\$header_id))){
            \$retailerProduct = \$this->retailerProductRepository->getModel();
            \$header = \$this->repository->getModel();
            
            if (\$header->issued) 
                return view(\$retailerProduct->companyProduct->product->code.'.issuance', compact('rp_id', 'header_id', 'header','retailerProduct'))
                    ->with([ 'success_header' => 'La Póliza fue emitida con éxito.' ]);
        }
        return redirect()->back();
    }
}
";
}

function newBeneficiaryController($code)
{
    return "<?php

namespace Sibas\Http\Controllers\\".ucfirst($code).";

use Sibas\Entities\\".ucfirst($code)."\Beneficiary;
use Sibas\Http\Controllers\Controller;
use Sibas\Http\Requests\\".ucfirst($code)."\BeneficiaryFormRequest;
use Sibas\Repositories\\".ucfirst($code)."\BeneficiaryRepository;
use Sibas\Repositories\\".ucfirst($code)."\DetailRepository;
use Sibas\Repositories\De\DataRepository;
use Sibas\Repositories\Retailer\CityRepository;
use Sibas\Repositories\Retailer\RetailerProductRepository;

class BeneficiaryController extends Controller
{
    /**
     * @var BeneficiaryRepository
     */
    protected \$repository;

    /**
     * @var DetailRepository
     */
    protected \$detailRepository;

    /**
     * @var RetailerProductRepository
     */
    protected \$retailerProductRepository;

    /**
     * @var CityRepository
     */
    protected \$cityRepository;

    /**
     * @var DataRepository
     */
    protected \$dataRepository;


    public function __construct(
        BeneficiaryRepository \$repository,
        DetailRepository \$detailRepository,
        RetailerProductRepository \$retailerProductRepository,
        CityRepository \$cityRepository
    ) {
        \$this->repository                = \$repository;
        \$this->detailRepository          = \$detailRepository;
        \$this->cityRepository            = \$cityRepository;
        \$this->retailerProductRepository = \$retailerProductRepository;
        \$this->dataRepository            = new DataRepository();
    }

    public function create(\$rp_id, \$header_id, \$detail_id)
    {
        if (request()->ajax()) {
            if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id)) && \$this->detailRepository->getDetailById(decode(\$detail_id))) {
                \$retailerProduct = \$this->retailerProductRepository->getModel();
                \$detail          = \$this->detailRepository->getModel();
                \$beneficiary     = new Beneficiary();
                
                \$data = [
                    'cities' => \$this->cityRepository->getCitiesByType(),
                    'vg'     => false,
                    'relationships' => \$this->dataRepository->getRelationship(),
                ];
                
                \$payload = view('beneficiary.create', compact('rp_id', 'header_id', 'detail', 'beneficiary', 'data', 'retailerProduct'));

                return response()->json([
                    'payload' => \$payload->render()
                ]);
            }

            return response()->json([ 'err' => 'Unauthorized action.' ], 401);
        }

        return redirect()->back();
    }

    public function store(BeneficiaryFormRequest \$request, \$rp_id, \$header_id, \$detail_id)
    {
        if (\$request->ajax()) {
            if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id)) && \$this->detailRepository->getDetailById(decode(\$detail_id))) {
                \$retailerProduct    = \$this->retailerProductRepository->getModel();
                \$request['detail']  = \$this->detailRepository->getModel();
                
                if (\$this->repository->storeBeneficiary(\$request)) {
                    return response()->json([
                        'location' => route(\$retailerProduct->companyProduct->product->code.'.edit', compact('rp_id', 'header_id'))
                    ]);
                }
            }

            return response()->json([ 'err' => 'Unauthorized action.' ], 401);
        }

        return redirect()->back();
    }

    public function edit(\$rp_id, \$header_id, \$detail_id, \$beneficiary_id)
    {
        if (request()->ajax()) {
            if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id)) && \$this->detailRepository->getDetailById(decode(\$detail_id))) {
                \$retailerProduct = \$this->retailerProductRepository->getModel();
                \$detail          = \$this->detailRepository->getModel();

                \$beneficiary     = \$detail->beneficiary()->where('id', decode(\$beneficiary_id))->first();

                \$data = [
                    'cities' => \$this->cityRepository->getCitiesByType(),
                    'vg'     => false,
                    'relationships' => \$this->dataRepository->getRelationship(),
                ];

                \$payload = view('beneficiary.edit', compact('rp_id', 'header_id', 'detail', 'beneficiary', 'data', 'beneficiary_id', 'retailerProduct'));

                return response()->json([
                    'payload'     => \$payload->render(),
                    'beneficiary' => [ \$beneficiary ]
                ]);
            }

            return response()->json([ 'err' => 'Unauthorized action.' ], 401);
        }

        return redirect()->back();
    }

    public function update(BeneficiaryFormRequest \$request, \$rp_id, \$header_id, \$detail_id, \$beneficiary_id)
    {
        if (\$request->ajax()) {
            if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id)) && \$this->detailRepository->getDetailById(decode(\$detail_id)) && \$this->repository->getBeneficiaryById(decode(\$beneficiary_id))) {
                \$retailerProduct    = \$this->retailerProductRepository->getModel();
                \$request['detail']  = \$this->detailRepository->getModel();

                if (\$this->repository->updateBeneficiary(\$request)) {
                    return response()->json([
                        'location' => route(\$retailerProduct->companyProduct->product->code.'.edit', compact('rp_id', 'header_id'))
                    ]);
                }
            }

            return response()->json([ 'err' => 'Unauthorized action.' ], 401);
        }

        return redirect()->back();
    }
    
    public function destroy(\$rp_id, \$header_id, \$beneficiary_id)
    {
        if (request()->ajax()) {
            if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id)) && \$this->repository->getBeneficiaryById(decode(\$beneficiary_id)) && \$this->repository->removeBeneficiary()) {
                \$retailerProduct    = \$this->retailerProductRepository->getModel();
                return response()->json([
                    'location' => route(\$retailerProduct->companyProduct->product->code.'.edit', compact('rp_id', 'header_id'))
                ]);
            }

            return response()->json([ 'err' => 'Unauthorized action.' ], 401);
        }

        return redirect()->back();
    }
}
";
}

function newCancellationController($code)
{
    return "<?php

namespace Sibas\Http\Controllers\\".ucfirst($code).";

use Illuminate\Auth\Guard;
use Illuminate\Http\Request;
use Sibas\Http\Controllers\MailController;
use Sibas\Http\Controllers\Controller;
use Sibas\Http\Controllers\ReportTrait;
use Sibas\Repositories\\".ucfirst($code)."\CancellationRepository;
use Sibas\Repositories\\".ucfirst($code)."\HeaderRepository;
use Sibas\Repositories\Retailer\RetailerProductRepository;
use Sibas\Repositories\Retailer\AnnulledRepository;

class CancellationController extends Controller
{

    use ReportTrait;

    /**
     * @var CancellationRepository
     */
    protected \$repository;

    /**
     * @var HeaderRepository
     */
    protected \$headerRepository;

    /**
     * @var RetailerProductRepository
     */
    protected \$retailerProductRepository;

    /**
     * @var AnnulledRepository
     */
    protected \$annulledRepository;

    /**
     * CancellationController constructor.
     *
     * @param CancellationRepository \$repository
     * @param HeaderRepository       \$headerRepository
     */
    public function __construct(
        CancellationRepository \$repository,
        HeaderRepository \$headerRepository,
        RetailerProductRepository \$retailerProductRepository,
        AnnulledRepository \$annulledRepository
    ) {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);

        \$this->repository       = \$repository;
        \$this->headerRepository = \$headerRepository;
        \$this->retailerProductRepository = \$retailerProductRepository;
        \$this->annulledRepository = \$annulledRepository;
    }

    public function lists(Guard \$auth, Request \$request, \$rp_id)
    {
        if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id))) {
            \$retailerProduct = \$this->retailerProductRepository->getModel();    
            \$data    = \$this->data(\$auth->user());
            \$headers = [ ];
            \$code    = \$retailerProduct->companyProduct->product->code;
            
            if (\$request->has('_token')) {
                \$request->flash();
            }
            
            \$headers = \$this->repository->getHeaderList(\$request);
            
            return view(\$retailerProduct->companyProduct->product->code.'.cancellation.list', compact('rp_id', 'headers', 'data', 'code', 'retailerProduct'));
        }
        return redirect()->back();
    }
    
    public function create(\$rp_id, \$header_id)
    {
        if (request()->ajax()) {
            if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id)) && \$this->headerRepository->getHeaderById(decode(\$header_id))) {
                \$retailerProduct = \$this->retailerProductRepository->getModel();
                \$header = \$this->headerRepository->getModel();
                \$data['annulleds'] = \$this->annulledRepository->getAnnulledByActive();
                
                \$payload = view(\$retailerProduct->companyProduct->product->code.'.cancellation.create', compact('rp_id', 'header', 'retailerProduct', 'data'));

                return response()->json([
                    'payload' => \$payload->render()
                ]);
            }

            return response()->json([ 'err' => 'Unauthorized action.' ], 401);
        }

        return redirect()->back();
    }
    
    public function store(Request \$request, \$rp_id, \$header_id)
    {
        \$this->validate(\$request, [
            'reason' => 'required|ands_full',
            'ad_annulleds_id' => 'required',
        ]);

        \$aut = true;

        if (request()->ajax()) {
            if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id)) && \$this->headerRepository->getHeaderById(decode(\$header_id))) {
                \$retailerProduct = \$this->retailerProductRepository->getModel();
                \$header = \$this->headerRepository->getModel();

                if (\$aut == true){
                    if (\$this->repository->storeCancellation(\$request, \$header)) {
                        \$mail           = new MailController(\$request->user());
                        \$mail->subject  = 'Anulacion de Poliza No. ' . \$header->policy_number.' - '.\$header->issue_number;
                        \$mail->template = 'de.cancellation';
                        
                        array_push(\$mail->receivers, [
                            'email' => \$header->user->email,
                            'name'  => \$header->user->full_name,
                        ],[
                            'email' => \$request->user()->email,
                            'name'  => \$request->user()->full_name,
                        ]);
                        
                        if (\$mail->send(decode(\$rp_id), [ 'header' => \$header ])) {

                        }

                        return response()->json([
                            'location' => route(\$retailerProduct->companyProduct->product->code.'.cancel.lists', [ 'rp_id' => \$rp_id ])
                        ]);
                    }
                }
            }

            return response()->json([ 'err' => 'Unauthorized action.' ], 401);
        }

        return redirect()->back();
    }
}
";
}

function newDetailController($code)
{
    return "<?php

namespace Sibas\Http\Controllers\\".ucfirst($code).";

use Illuminate\Http\Request;
use Sibas\Http\Requests;
use Sibas\Http\Controllers\Controller;
use Sibas\Http\Controllers\DataTrait;
use Sibas\Entities\Client;
use Sibas\Entities\ProductParameter;
use Sibas\Repositories\Retailer\RetailerProductRepository;
use Sibas\Http\Requests\\".ucfirst($code)."\ClientCreateFormRequest;
use Sibas\Http\Requests\\".ucfirst($code)."\ClientComplementFormRequest;
use Sibas\Repositories\\".ucfirst($code)."\DetailRepository;
use Sibas\Repositories\\".ucfirst($code)."\HeaderRepository;
use Sibas\Repositories\Client\ClientRepository;

class DetailController extends Controller
{
    /**
     * @var DetailRepository
     */
    private \$repository;
    
    /**
     * @var RetailerProductRepository
     */
    private \$retailerProductRepository;
    
    /**
     * @var HeaderRepository
     */
    private \$headerRepository;
    
    /**
     * @var ClientRepository
     */
    private \$clientRepository;
    
    public function __construct(
            DetailRepository \$repository,
            RetailerProductRepository \$retailerProductRepository,
            HeaderRepository \$headerRepository,
            ClientRepository \$clientRepository
    )
    {
        \$this->repository = \$repository;
        \$this->retailerProductRepository = \$retailerProductRepository;
        \$this->headerRepository = \$headerRepository;
        \$this->clientRepository = \$clientRepository;
    }
    
    use DataTrait;
    
    public function create(\$rp_id)
    {
        if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id))) {
            \$retailerProduct    = \$this->retailerProductRepository->getModel();
            \$data               = \$this->getData(\$rp_id);
            \$client             = new Client();

            if (session('client'))
                \$client = session('client');

            return view(\$retailerProduct->companyProduct->product->code.'.create', compact('rp_id', 'data', 'client', 'retailerProduct'));
        }
        return redirect()->back();
    }
    
    public function store(ClientCreateFormRequest \$request, \$rp_id)
    {
        if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id))) {
            \$retailerProduct = \$this->retailerProductRepository->getModel();
            \$parameter       = \$retailerProduct->parameters->filter(function (\$item) {
                return \$item->slug === 'GE';
            })->first();

            
            if (\$this->headerRepository->storeHeader(\$request)) {
                \$request['header'] = \$this->headerRepository->getModel();
                
                if (\$parameter instanceof ProductParameter) {
                    
                    if (\$this->repository->checkAgeByParameter(\$request->get('birthdate'), \$parameter)) {
                        
                        if (\$this->clientRepository->createClient(\$request)) {
                            \$request['client'] = \$this->clientRepository->getModel();
                            
                            if (\$this->repository->createDetail(\$request)) {
                                \$detail = \$this->repository->getModel();
                                
                                return redirect()->route('de.question.create', [
                                    'rp_id'     => \$rp_id,
                                    'header_id' => encode(\$request['header']->id),
                                    'detail_id' => encode(\$detail->id),
                                ])->with([ 'success_client' => 'La información del Cliente fue registrada' ]);
                            }
                        }
                    } else {
                        return redirect()->back()->with([
                            'error_client' => 'El Cliente no cumple con el rango de edades [ '
                                              . \$parameter->age_min . ' - '
                                              . \$parameter->age_max . ' ]',
                        ])->withInput()->withErrors(\$this->repository->getErrors());
                    }
                }
            }
        }

        return redirect()->back()->with([ 'error_detail' => 'El Cliente no pudo ser registrado' ])->withInput()
            ->withErrors(\$this->repository->getErrors());
    }
    
    public function edit(\$rp_id, \$header_id, \$detail_id)
    {
        if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id)) && \$this->headerRepository->getHeaderById(decode(\$header_id))
            && \$this->repository->getDetailById(decode(\$detail_id))) {
            \$retailerProduct    = \$this->retailerProductRepository->getModel();
            \$header             = \$this->headerRepository->getModel();
            \$detail             = \$this->repository->getModel();
            
            if (\$detail->client instanceof Client) {
                \$client = \$detail->client;

                ClientEdit:
                \$data   = \$this->getData(\$rp_id);

                return view('client.'.\$retailerProduct->companyProduct->product->code.'.edit',
                    compact('rp_id', 'header_id', 'detail_id', 'header', 'detail', 'data', 'client', 'retailerProduct'));
            }
        }

        return redirect()->back()->with([ 'error_client_edit' => 'El Cliente no existe' ]);
    }
    
    public function update(ClientCreateFormRequest \$request, \$rp_id, \$header_id, \$detail_id)
    {
        if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id))) {
            \$retailerProduct = \$this->retailerProductRepository->getModel();
            \$parameter       = \$retailerProduct->parameters->filter(function (\$item) {
                return \$item->slug === 'GE';
            })->first();

            if (\$this->headerRepository->getHeaderById(decode(\$header_id))
                && \$this->repository->getDetailById(decode(\$detail_id))) {
                \$request[ 'header' ] = \$this->headerRepository->getModel();
                \$detail              = \$this->repository->getModel();

                if (\$parameter instanceof ProductParameter) {
                    if (\$this->repository->checkAgeByParameter(\$request->get('birthdate'), \$parameter)) {
                        if (\$this->clientRepository->editClient(\$request, \$detail->client)) {
                            if (\$this->repository->editDetail(\$request)) {
                                ClientUpdate:
                                return redirect()->route(\$retailerProduct->companyProduct->product->code.'.client.list', [
                                    'rp_id'     => \$rp_id,
                                    'header_id' => \$header_id
                                ])->with([ 'success_client' => 'La información del Cliente se actualizó correctamente' ]);
                            }
                        }
                    } else {
                        return redirect()->back()->with([
                            'error_client' => 'El Cliente no cumple con el rango de edades [ '
                                              . \$parameter->age_min . ' - '
                                              . \$parameter->age_max . ' ]',
                        ])->withInput()->withErrors(\$this->repository->getErrors());
                    }
                }
            }
        }

        return redirect()->back()
            ->with([ 'error_client_edit' => 'La información del Cliente no puede ser actualizada' ])->withInput()
            ->withErrors(\$this->repository->getErrors());
    }

    public function editIssue(\$rp_id, \$header_id, \$detail_id)
    {
        if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id)) && \$this->headerRepository->getHeaderById(decode(\$header_id))) {
            \$retailerProduct = \$this->retailerProductRepository->getModel();
            \$header = \$this->headerRepository->getModel();

            if (\$this->repository->getDetailById(decode(\$detail_id))) {
                \$detail = \$this->repository->getModel();

                \$data = \$this->getData(\$rp_id);
                \$client = \$detail->client;

                if (\$client instanceof Client) {
                    return view('client.'.\$retailerProduct->companyProduct->product->code.'.edit-issue', 
                            compact('rp_id', 'header_id', 'detail_id', 'retailerProduct', 'data', 'client'));
                }
            }
        }

        return redirect()->back()->with([ 'error_client' => 'La información del Cliente no puede ser editada' ]);
    }
    
    public function updateIssue(ClientComplementFormRequest \$request, \$rp_id, \$header_id, \$detail_id)
    {
        if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id)) && \$this->headerRepository->getHeaderById(decode(\$header_id))
            && \$this->repository->getDetailById(decode(\$detail_id))
        ) {
            \$request['header'] = \$this->headerRepository->getModel();
            \$detail            = \$this->repository->getModel();
            \$retailerProduct = \$this->retailerProductRepository->getModel();

            if (( \$detail->client instanceof Client )
                && \$this->clientRepository->updateIssueClient(\$request, \$detail->client)
            ) {
                    return redirect()->route(\$retailerProduct->companyProduct->product->code.'.edit', [
                        'rp_id'     => \$rp_id,
                        'header_id' => \$header_id
                    ])->with([ 'success_client' => 'La información del Cliente se actualizó correctamente' ]); 
            }
        }

        return redirect()->back()->with([ 'error_client' => 'La información del Cliente no pudo ser actualizada' ])->withInput()->withErrors(\$this->repository->getErrors());
    }
}
";
}

function newIssueController($code)
{
    return "<?php

namespace Sibas\Http\Controllers\\".ucfirst($code).";

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Sibas\Entities\ProductParameter;
use Sibas\Http\Controllers\Controller;
use Sibas\Http\Controllers\ReportTrait;
use Sibas\Repositories\\".ucfirst($code)."\IssueRepository;
use Sibas\Repositories\Retailer\RetailerProductRepository;

class IssueController extends Controller
{

    use ReportTrait;

    /**
     * @var IssueRepository
     */
    protected \$repository;

    /**
     * @var RetailerProductRepository
     */
    protected \$retailerProductRepository;


    public function __construct(
        IssueRepository \$repository,
        RetailerProductRepository \$retailerProductRepository
    ) {
        \$this->repository                = \$repository;
        \$this->retailerProductRepository = \$retailerProductRepository;
    }

    public function lists(Guard \$auth, Request \$request, \$rp_id, \$guest = null)
    {
        if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id))) {
            \$retailerProduct = \$this->retailerProductRepository->getModel();    
            \$data      = \$this->data(\$auth->user());
            \$headers   = [ ]; 

            if (\$request->has('_token')) {
                \$request->flash();
            }

            \$parameter = \$retailerProduct->parameters()->where('slug', 'GE')->first();

            if (\$parameter instanceof ProductParameter) {
                \$headers = \$this->repository->getHeaderList(\$request);
            }

            return view(\$retailerProduct->companyProduct->product->code.'.quote.list', compact('rp_id', 'headers', 'data', 'parameter', 'retailerProduct'));
        }
        return redirect()->back();
    }
}
";
}

function newPreApprovedController($code)
{
    return "<?php

namespace Sibas\Http\Controllers\\".ucfirst($code).";

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Sibas\Http\Controllers\Controller;
use Sibas\Http\Controllers\ReportTrait;
use Sibas\Repositories\\".ucfirst($code)."\PreApprovedRepository;
use Sibas\Repositories\Retailer\RetailerProductRepository;

class PreApprovedController extends Controller
{

    use ReportTrait;

    /**
     * @var PreApprovedRepository
     */
    protected \$repository;

    /**
     * @var RetailerProductRepository
     */
    protected \$retailerProductRepository;


    public function __construct(
            PreApprovedRepository \$repository,
            RetailerProductRepository \$retailerProductRepository
            )
    {
        \$this->repository = \$repository;
        \$this->retailerProductRepository = \$retailerProductRepository;
    }

    public function lists(Guard \$auth, Request \$request, \$rp_id)
    {
        if (\$this->retailerProductRepository->getRetailerProductById(decode(\$rp_id))) {
            \$retailerProduct = \$this->retailerProductRepository->getModel();    
            \$data    = \$this->data(\$auth->user());
            \$headers = [ ];
            \$code    = '';

            if (\$request->has('_token')) {
                \$request->flash();
            }

            \$headers = \$this->repository->getHeaderList(\$request);
            
            return view(\$retailerProduct->companyProduct->product->code.'.pre-approved.list', compact('rp_id', 'headers', 'data', 'code', 'retailerProduct'));
        }
        return redirect()->back();
    }
}
";
}