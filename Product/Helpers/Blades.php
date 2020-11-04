<?php

function newCreateBlade($code)
{
    return "@extends('body')
@var \$step=1
@section('body')

    @include('partials.form-search-client', ['type_form' => 2, 'placeholder' => 'Ingrese CI'])
    {!! Form::open(['route' => [\$retailerProduct->companyProduct->product->code.'.store', 'rp_id' => \$rp_id],
    'method'        => 'post',
    'class'         => 'form-horizontal'
    ]) !!}
        <h2>Datos del Cliente</h2>

        @include('client.'.\$retailerProduct->companyProduct->product->code.'.partials.inputs-quote', ['form' => 'create'])

        <div class=\"col-xs-12 col-md-12\"><hr />
            <h2>Datos del Seguro</h2>
            <div class=\"col-xs-12 col-md-6\">
                @include('partials.fields', ['type'=>'combo2','label'=>'Plan','id'=>'ad_plan_id', 'required'=>1,'value'=>'','array'=>\$data['plans']->toArray()])
            </div>
            <div class=\"col-xs-12 col-md-6\" ng-init=\"freshPayment()\">
                @include('partials.fields', ['type'=>'combo2','label'=>'Forma de Pago','id'=>'payment_method', 'required'=>1,'value'=>'CO','array'=>\$data['payment_method']->toArray()])
            </div>
        </div>  
        <div class=\"col-xs-12 col-md-12\">
            <div class=\"col-md-12 text-right\">
                {!! Form::button('Cotiza tu mejor seguro <i class=\"icon-arrow-right14 position-right\"></i>', ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
            </div>
        </div>
    {!! Form::close() !!}
@endsection
";
}

function newEditBlade($code)
{
    return "@extends('body')
@var \$step=3
@section('body')
    <div class=\"col-md-10 col-md-offset-1\">
        @if (\$header->type === 'I')
            <div class=\"page-header\" style=\"padding: 5px;\">
                <h2>Póliza {{ \$header->policy_number}} - {{ \$header->issue_number}}</h2>
            </div>
        @endif
        <!--Titular-->
        @include(\$retailerProduct->companyProduct->product->code.'.partials.block-titular' )
    </div>
    <div class=\"col-md-10 col-md-offset-1\">
        <!--Beneficiary-->
        @include(\$retailerProduct->companyProduct->product->code.'.partials.block-beneficiaries' )
    </div>

    <span >
        <div class=\"col-xs-12 col-md-10 col-md-offset-1\">
            <!--formulario cotizacion (Q) -->
            @include(\$retailerProduct->companyProduct->product->code.'.partials.block-generals' )
        </div>

        @if(\$header->type === 'I' && \$header->issued==false)
            <div class=\"col-xs-12 col-md-10 col-md-offset-1\">
                <!--formulario Tomador -->
                @include(\$retailerProduct->companyProduct->product->code.'.partials.block-tomador' )
            </div>
            
            <div class=\"col-xs-12 col-md-10 col-md-offset-1\">
                <div class=\"col-md-4\">&nbsp;</div>
                <div class=\"col-xs-12 col-md-4\" style=\"border: 1px solid #ff0000; background: #fdffa1; text-align: center;\">
                    <br />
                    <b>FINALIZAR EMISIÓN</b>
                    <hr>
                    <div>
                        Para <b>concluir</b> la emisión presione el boton \"<b>Emitir</b>\".
                    </div>
                </div>
                <div class=\"col-md-4\">&nbsp;</div>
            </div>
            
            <div class=\"col-xs-12 col-md-10 col-md-offset-1 text-right\">
                <a href=\"{{ route('home', [])}}\" class=\"btn btn-info\" >
                    Guardar y Cerrar <i class=\"icon-floppy-disk position-right\"></i>
                </a>
                <a href=\"{{ route(\$retailerProduct->companyProduct->product->code.'.issue', ['rp_id' => \$rp_id, 'header_id' => \$header_id])}}\" class=\"btn btn-primary box-issued\" >
                    Emitir <i class=\"icon-play position-right\"></i>
                </a>
            </div>
        @endif
    </span>
@endsection
";
}

function newIssuanceBlade($code)
{
    return "@extends('body')
@var \$step=4
@section('body')
<div class=\"col-md-4 col-md-offset-2\">
    <div class=\"modal-header bg-primary\">
        <h6 class=\"modal-title\">Póliza {{ \$header->policy_number }} - {{ \$header->issue_number }}</h6>
    </div>
    <div class=\"panel panel-body border-top-primary text-center\">
        <p class=\"text-muted content-group-sm\">Cotizacion/Emisión </p>
        <div class=\"col-md-12\">
            <p>
                <a href=\"{{ route('certificate.show', [
                                                'code' => \$retailerProduct->companyProduct->product->code,
                                                'rp_id' => \$rp_id,
                                                'type' => 'certificate',
                                                'header_id' => encode(\$header->id),
                                                'format' => 'pdf',
                                        ])}}\"  target=\"_blank\"
                   class=\"btn btn-info btn-labeled btn-xlg col-lg-12\">
                    <b><i class=\"icon-printer4\"></i></b>
                    Ver Certificado de Emisión
                </a>
            </p>
            <div class=\"col-md-6\">&nbsp;</div>
        </div>
    </div>
</div>
@endsection";
}

function newListBlade($code)
{
    return "@extends('body')
@var \$step=1
@section('body')
    @if(is_null(\$header))
        <div class=\"alert bg-danger alert-styled-right\">
            <button type=\"button\" class=\"close\" data-dismiss=\"alert\"><span>×</span><span class=\"sr-only\">Close</span>
            </button>
            <span class=\"text-semibold\">La Cotización no existe</span>.
        </div>
    @endif
    
    @if(! is_null(\$header))
        <div class=\"col-xs-12\">
            <br>
            <div class=\"text-right\">
                @if(\$header->details->count() > 0 || \$header->details->count() > 1)
                    @if(\$header->questions)
                        <a class=\"btn btn-primary\"
                           href=\"{{ route(\$retailerProduct->companyProduct->product->code.'.result', ['rp_id' => \$rp_id, 'header_id' => \$header_id]) }}\">
                            Continuar <i class=\"icon-arrow-right14 position-right\"></i>
                        </a>
                    @endif
                @endif
            </div>
            <br>
        </div>
        <table class=\"table small_body\">
            @if(\$header->details->count() > 0)
                <thead>
                <tr style=\"background: #f3f3f3 none repeat scroll 0 0;\">
                    <th>Cliente</th>
                    <th>C.I.</th>
                    <th>Nombres y Apellidos</th>
                    <th>Fecha Nacimiento</th>
                    <th>% Participación</th>    
                    <th class=\"text-center\">Accion</th>
                </tr>
                </thead>
                <tbody>
                @foreach(\$header->details as \$key => \$detail)
                    <tr>
                        <td>{{ \$key === 0 ? 'T' : 'C' . \$key }}</td>
                        <td>
                            <a href=\"#\">
                                {{ \$detail->client->dni
                                    . (! empty(\$detail->client->complement) ? '-' . \$detail->client->complement : '') }}
                                {{ \$detail->client->extension }}
                            </a>
                        </td>
                        <td>{{ \$detail->client->full_name }}</td>
                        <td>{{ dateToFormat(\$detail->client->birthdate) }}</td>
                        <td>{{ \$detail->percentage_credit }} %</td>

                        <td class=\"text-center\">
                            <ul class=\"icons-list\">
                                <li class=\"dropdown\">
                                    <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">
                                        <i class=\"icon-menu9\"></i>
                                    </a>
                                    <ul class=\"dropdown-menu dropdown-menu-right\">
                                        <li>
                                            <a href=\"{{ route(\$retailerProduct->companyProduct->product->code.'.detail.edit', [
                                                'rp_id'     => \$rp_id,
                                                'header_id' => \$header_id,
                                                'detail_id' => encode(\$detail->id)
                                                ]) }}\">
                                                <i class=\"icon-plus2\"></i> Editar datos</a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            @endif
        </table>
    @endif
@endsection
";
}

function newResultBlade($code)
{
    return "@extends('body')
@var \$step=2
@section('body')
    <div class=\"panel-body \">
        <div class=\"col-xs-12 col-md-12\">
            <div class=\"col-md-4\">
                <div class=\"panel panel-body border-top-primary text-center\">
                    <div class=\"form-group\">
                        {!! Html::image(\$retailerProduct->companyProduct->company->image, null, ['style' => 'height: 100px;']) !!}
                    </div>
                    <h6 class=\"no-margin text-semibold\">{{\$header->plan->name}} </h6>
                    <p class=\"text-muted content-group-sm\">
                        <strong>Prima: Bs {{ number_format((\$header->plan->annual_premium), 2, '.', '')}}  Anual</strong>
                        <br>
                    </p>
                    <hr>
                    <a href=\"{{ route(\$retailerProduct->companyProduct->product->code.'.edit', ['rp_id' => \$rp_id, 'header_id' => \$header_id]) }}\"
                       class=\"btn btn-primary\"><i class=\"icon-arrow-right14 position-left\"></i>
                        Emitir
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
";
}

function newCreateCancelBlade($code)
{
    return "<div class=\"row\">
    <div class=\"col-md-12\">
        <div class=\"panel panel-flat\">
            <div class=\"col-md-12\">
                <h3>
                    Formulario de anulación Póliza Nº. {{ \$header->policy_number }} - {{ \$header->issue_number }}
                </h3>
            </div>
            {!! Form::open(['route' => [\$retailerProduct->companyProduct->product->code.'.cancel.store', 'rp_id' => \$rp_id, 'header_id' => encode(\$header->id)],
                'method'        => 'post',
                'class'         => 'form-horizontal',
                'ng-controller' => 'Cancellation".ucfirst($code)."Controller',
                'ng-submit'     => 'cancelStore(\$event)'
            ]) !!}
            <label class=\"control-label col-lg-12 label_required\">Tipo de anulación: </label>
            <div class=\"form-group animated\">
                <div class=\"col-lg-6\">
                    <div>
                        {!! SelectField::input('ad_annulleds_id', \$data['annulleds']->toArray(), [
                        'class' => 'form-control',
                        'id' => 'ad_annulleds_id',
                        'ng-change' => 'onChange()',
                        'ng-model' => 'formData.ad_annulleds_id'
                        ], old('ad_annulleds_id')) !!}
                    </div>
                    <label id=\"location-error\" class=\"validation-error-label\" for=\"location\" ng-show=\"errors.ad_annulleds_id\">
                        @{{ errors.ad_annulleds_id[0] }}
                    </label>
                </div>
            </div>
            <br>
            <label class=\"control-label col-lg-6 label_required\">Motivo de Anulación: </label>
            <div class=\"form-group animated\">
                <div class=\"col-lg-12\">
                    <div>
                        {!! Form::textarea('reason', null, [
                            'size'         => '4x4',
                            'class'        => 'form-control',
                            'placeholder'  => 'Motivo de Anulación',
                            'autocomplete' => 'off',
                            'id' => 'reason',
                            'ng-model'     => 'formData.reason'
                        ]) !!}
                    </div>
                    <label id=\"location-error\" class=\"validation-error-label\" for=\"location\" ng-show=\"errors.reason\">
                        @{{ errors.reason[0] }}
                    </label>
                </div>
            </div>
            <br>
            <div class=\"form-group text-right\">
                <div class=\"col-lg-12\">
                <script ng-if=\"success.cancellation\">
                    $(function () {
                        messageAction('succes', 'El Certificado fue anulado correctamente.');
                    });
                </script>
                <script ng-if=\"errors.msg\">
                    $(function () {
                        messageAction('error', 'No se pudo eliminar la pignoración');
                    });
                </script>
                <button type=\"button\" class=\"btn border-slate text-slate-800 btn-flat\" data-dismiss=\"modal\">Cancelar</button>
                {!! Form::button('Anular Póliza', ['type' => 'submit', 'class' => 'btn btn-primary']) !!}
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>";
}

function newListCancelBlade($code)
{
    return "@extends('body')
@var \$step=0
@section('body')
    <div class=\"col-xs-12\" ng-controller=\"Cancellation".ucfirst($code)."Controller\">
        <h6>Anulación de Certificados</h6>
        <hr />
        {!! Form::open(['url' => request()->fullUrl(),
                                'method' => 'get',
                                'class'  => 'form-horizontal'
                            ]) !!}
          <div class=\"col-xs-12 col-md-12\">
            @include('report.partials.inputs-search')
          </div>
        {!! Form::close() !!}
        <div class=\"clearfix\">:</div>
        <table class=\"table-bordered table-hover gridview small_body\" border=\"1\" width=\"100%\" >
          <thead>
            <tr>
                <th>NRO. DE PÓLIZA</th>
                <th>PLAN</th>
                <th>CLIENTE</th>
                <th>C.I.</th>
                <th>CLIENTE TOMADOR</th>
                <th>PRIMA TOTAL</th>
                <th>USUARIO</th>
                <th>SUCURSAL / AGENCIA</th>
                <th>ACCIÓN</th>
            </tr>
          </thead>
          <tbody>
            @foreach (\$headers as \$header)
              @foreach (\$header->details as \$detail)
                <tr>
                    <td style=\"font-weight: bold;\">{{ \$header->policy_number }} - {{ \$header->issue_number }}</td>
                    <td> Bs. {{ \$header->plan->name }}</td>
                    <td>{{ \$detail->client->full_name }}</td>
                    <td>{{ \$detail->client->dni }} {{ \$detail->client->extension }}</td>
                    <td>{{ \$detail->holder==1?'SI':'NO' }}</td>
                    <td>{{ \$header->total_premium }} {{ \$header->currency }}</td>
                    <td>{{ \$header->user->full_name }}</td>
                    <td>
                      {{ ! is_null(\$header->user->city) ? \$header->user->city->name : '' }}
                      {{ ! is_null(\$header->user->agency) ? '/ ' . \$header->user->agency->name : '' }}
                    </td>
                    <td>
                      <ul class=\"icons-list\">
                          <li class=\"dropdown\">
                              <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">
                                  <i class=\"icon-menu9\"></i>
                              </a>
                              <ul class=\"dropdown-menu dropdown-menu-right\">
                                  <li>
                                      <a href=\"{{ route(\$retailerProduct->companyProduct->product->code.'.cancel.create', ['rp_id' => \$rp_id, 'header_id' => encode(\$header->id)]) }}\" 
                                         ng-click=\"cancelCreate(\$event)\">
                                        Anular Certificado
                                      </a>
                                  </li>
                                  <li>
                                      <a href=\"{{ route('certificate.show', [
                                                'code' => \$retailerProduct->companyProduct->product->code,
                                                'rp_id' => \$rp_id,
                                                'type' => 'certificate',
                                                'header_id' => encode(\$header->id),
                                                'format' => 'pdf',
                                        ])}}\" target=\"_blank\">
                                        Ver Certificado de Emisión
                                      </a>
                                  </li>
                              </ul>
                          </li>
                      </ul>
                    </td>
                </tr>
              @endforeach
            @endforeach
          </tbody>
        </table>
        <br />
        <div style=\"text-align: center;\" class=\"small_body\">
            {!! \$headers->appends(request()->all())->render() !!}
        </div>
    </div> 
@endsection
";
}

function newBlockbeneficiariesPartialBlade($code)
{
    return "<div class=\"modal-header bg-primary recuadro\">
    <div class=\"panel-heading\">
        <h6 class=\"modal-title\">Beneficiarios para {{\$retailerProduct->companyProduct->product->name }}</h6>
    </div>
</div>
<div class=\"panel panel-body border-top-success\" ng-controller=\"DetailDeController as detailDe\">
    <div class=\"col-md-12\" style=\"text-align: right;\">
        @if(\$header->type === 'Q' && count(\$header->details->first()->beneficiaries->where('coverage','". strtoupper($code)."')) <= 2)
            <a href=\"{{ route(\$retailerProduct->companyProduct->product->code.'.beneficiary.create', [
                            'rp_id'     => \$rp_id,
                            'header_id' => \$header_id,
                            'detail_id' => encode(\$header->details->first()->id),
                            'type'      => strtoupper(\$retailerProduct->companyProduct->product->code)
                        ]) }}\"
                           ng-click=\"detailDe.createBeneficiary(\$event)\" class=\"btn btn-default\" title=\"Registrar Nuevo\">
                Registrar Nuevo <i class=\"glyphicon glyphicon-plus\"></i>
            </a>
        @endif                            
    </div>
    <table class=\"table small_body\" style=\"width: 100%\">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Ap. Paterno</th>
                <th>Ap. Materno</th>
                <th>DNI</th>
                <th>Extensión</th>
                <th>Parentesco</th>
                <th>Porcentaje</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            @var \$i=0
            @foreach(\$header->details->first()->beneficiaries as \$key => \$value)
                <tr>
                    <td style=\"font-weight: bold;\">{{\$value->first_name}}</td>
                    <td>{{\$value->last_name}}</td>
                    <td>{{\$value->mother_last_name}}</td>
                    <td>{{\$value->dni}}</td>
                    <td>{{\$value->extension}}</td>
                    <td>{{\$value->relationship}}</td>
                    <td style=\"text-align: center;\">{{\$value->percentage}} %</td>
                    <td>
                        @if(\$header->type === 'Q')
                            <a href=\"{{ route(\$retailerProduct->companyProduct->product->code.'.beneficiary.edit', [
                                'rp_id'     => \$rp_id,
                                'header_id' => \$header_id,
                                'detail_id' => encode(\$header->details->first()->id),
                                'beneficiary_id' => encode(\$value->id),
                                'type'      => strtoupper(\$retailerProduct->companyProduct->product->code)
                            ]) }}\"
                               ng-click=\"detailDe.editBeneficiary(\$event)\" title=\"Editar\">
                                Editar 
                            </a> | 
                            <a href=\"{{ route(\$retailerProduct->companyProduct->product->code.'.beneficiary.destroy', [
                                    'rp_id'     => \$rp_id,
                                    'header_id' => \$header_id,
                                    'beneficiary_id' => encode(\$value->id),
                                ]) }}\" ng-click=\"delete(\$event)\" title=\"Eliminar\">
                                Eliminar 
                            </a>
                        @endif
                    </td>
                </tr>
                @var \$i=\$i+\$value->percentage
            @endforeach
                <tr>
                    <th colspan=\"6\" style=\"text-align: right;\">La sumatoria de porcentajes debe ser igual al 100 %</th>
                    <th style=\"text-align: center;\" class=\"{{\$i != 100?'alert-danger':'alert-success'}}\">{{\$i}} %</th>
                    <th><strong>Total</strong></th>
                </tr>
        </tbody>
</table>
@if(checkBeneficiaries(\$header->details->first()) == false)
    <span style=\"color: red;\">Debe registrar al menos un beneficiario.</span>
@endif
</div>";
}

function newBlockgeneralsPartialBlade($code)
{
    return "<div class=\"modal-header bg-primary recuadro\">
    <div class=\"panel-heading\">
        <h6 class=\"modal-title\">Datos Generales</h6>
    </div>
</div>
<div class=\"panel panel-body border-top-success small_body\"> 
@if(! isset(\$_GET['idf']) && \$header->type === 'Q')
    {!! Form::open(array('route' => [\$retailerProduct->companyProduct->product->code.'.update','rp_id' => \$rp_id, 'header_id' => \$header_id], 
    'method'        => 'put', 
    'class'         => 'form-horizontal')) !!}
      <div class=\"col-md-12\">
        <div class=\"col-xs-12 col-md-6\">
            @include('partials.fields', ['type'=>'combo','label'=>'Forma de Pago','id'=>'payment_method','required'=>1,'value'=>\$header->payment_method, 'array'=>\$data['payment_method']->toArray()])
            @include('partials.fields', ['type'=>'text2','label'=>'Plazo (meses)','id'=>'term','required'=>1,'value'=>\$header->term])
            @include('partials.fields', ['type'=>'text','label'=>'Nro de Cuenta','id'=>'account_number','required'=>0,'value'=>\$header->details->first()->account_number])
        </div>
        <div class=\"col-xs-12 col-md-6\">
            @include('partials.fields', ['type'=>'combo','label'=>'Número de Póliza','id'=>'policy_number','required'=>1,'value'=>\$header->policy_number, 'array'=>\$data['policies']->toArray()])
            @include('partials.fields', ['type'=>'text2','label'=>'Facturar a','id'=>'bill_name','required'=>1,'value'=>\$header->details->first()->client->full_name])
            @include('partials.fields', ['type'=>'text2','label'=>'NIT','id'=>'bill_dni','required'=>1,'value'=>\$header->details->first()->client->dni])
        </div>
      </div>
      <div class=\"text-right\">
          @if(checkBeneficiaries(\$header->details->first()) && \$header->details->first()->completed)
            <button type=\"submit\" class=\"btn btn-primary\">Guardar <i
                    class=\"glyphicon glyphicon-floppy-disk position-right\"></i>
            </button>
          @else
            <span class=\"alert alert-danger\">
                Favor de verificar datos del (Titular / Beneficiario)
            </span>
          @endif
      </div>
  {!!Form::close()!!}
@else
    @var \$tomador = \$header->details->where(\"holder\",1)->first()
    <div class=\"form-horizontal col-md-12\" style=\"background: #fafafa;\">
        <div class=\"col-xs-12 col-md-6\">
            @include('partials.fields', ['type'=>'label','label'=>'Forma de Pago','value'=>config('base.payment_methods.'.\$header->payment_method)])
            @include('partials.fields', ['type'=>'label','label'=>'Plazo (meses)','value'=>\$header->term])
            @include('partials.fields', ['type'=>'label','label'=>'Nro de Cuenta','value'=>\$tomador->account_number])
        </div>
        <div class=\"col-xs-12 col-md-6\">
            @include('partials.fields', ['type'=>'label','label'=>'Número de Póliza','value'=>\$header->policy_number])
            @include('partials.fields', ['type'=>'label','label'=>'Facturar a','value'=>\$header->bill_name])
            @include('partials.fields', ['type'=>'label','label'=>'NIT','value'=>\$header->bill_dni])
        </div>
    </div>
@endif
</div>";
}

function newBlocktitularPartialBlade($code)
{
    return "<div class=\"modal-header bg-primary recuadro\">
    <div class=\"panel-heading\">
        <h6 class=\"modal-title\">Datos del Titular </h6>
    </div>
</div>
<div class=\"panel panel-body border-top-success\">
    <table class=\"table small_body\" style=\"width: 100%;\">
        <thead>
            <tr>
                <th style=\"background: #fafafa none repeat scroll 0 0;\">C.I.</th>
                <th>Nombres y Apellidos</th>
                <th>Fecha Nacimiento</th>
                <th>% Participación</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>    
            <tr>
                <td style=\"background: #fafafa none repeat scroll 0 0;\">
                    {{ \$header->details->first()->client->dni
                            . (! empty(\$header->details->first()->client->complement) ? '-' . \$header->details->first()->client->complement : '') }}
                        {{ \$header->details->first()->client->extension }}
                </td>
                <td>{{ \$header->details->first()->client->full_name }}</td>
                <td>{{ dateToFormat(\$header->details->first()->client->birthdate) }}</td>
                <td>{{ \$header->details->first()->percentage_credit }} %</td>
                <td>
                    @if(\$header->type === 'Q')
                        <a href=\"{{ route(\$retailerProduct->companyProduct->product->code.'.detail.i.edit', [
                            'rp_id'     => \$rp_id,
                            'header_id' => \$header_id,
                            'detail_id' => encode(\$header->details->first()->id),
                        ]) }}\">
                            @if(\$header->details->first()->completed)
                                <span class=\"label label-success\">Completado</span>
                            @else
                                <span class=\"label label-danger\">Pendiente</span>
                            @endif
                        </a>
                    @else
                        <span class=\"label label-success\">Completado</span>
                    @endif
                </td>
                <td>
                    @if(\$header->type === 'Q')
                        <a href=\"{{ route(\$retailerProduct->companyProduct->product->code.'.detail.i.edit', [
                            'rp_id'     => \$rp_id,
                            'header_id' => \$header_id,
                            'detail_id' => encode(\$header->details->first()->id),
                            ]) }}\">
                            <i class=\"icon-pencil3\"></i> Editar
                        </a>
                    @endif
                </td>
            </tr>
        </tbody>
    </table><br />
</div>";
}

function newBlocktomadorPartialBlade($code)
{
    return "<div class=\"modal-header bg-primary recuadro\">
    <div class=\"panel-heading\">
        <h6 class=\"modal-title\">Datos del tomador</h6>
    </div>
</div>
<div class=\"panel panel-body border-top-success small_body\">     
    <div class=\"col-md-12\">
        <table class=\"table small_body\">
            <tr>
                <th style=\"background: #fafafa;\"><strong>CI / NIT</strong></th>
                <th><strong>Nombres y Apellidos</strong></th>
                <th><strong>Fecha Nacimiento</strong></th>
            </tr>
            @foreach(\$header->details->where(\"holder\",1) as \$key => \$detail)
                <tr>
                    <td style=\"background: #fafafa;\">
                        {{ \$detail->client->dni
                                . (! empty(\$detail->client->complement) ? '-' . \$detail->client->complement : '') }}
                            {{ \$detail->client->extension }}
                    </td>
                    <td>{{ \$detail->client->full_name }}</td>
                    <td>{{ dateToFormat(\$detail->client->birthdate) }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>";
}

function newListPreaprovedBlade($code)
{
    return "@extends('body')
@var \$step=0
@section('body')
    <div class=\"col-xs-12\">
        <h6>Solicitudes Preaprobadas</h6>
        <hr />
        {!! Form::open(['route' => [\$retailerProduct->companyProduct->product->code.'.pre.approved.lists', 'rp_id' => \$rp_id], 'method' => 'get', 'class' => 'form-horizontal']) !!}
          <div class=\"col-xs-12 col-md-12\">
            @include('report.partials.inputs-search')
          </div>
        {!! Form::close() !!}

        <table class=\"table datatable-fixed-left table-striped small_body\" width=\"100%\">
          <thead>
            <tr>
                <th>NRO. DE PÓLIZA</th>
                <th>CLIENTE</th>
                <th>C.I.</th>
                <th>PRIMA TOTAL</th>
                <th>PLAZO (MESES)</th>
                <th>USUARIO</th>
                <th>SUCURSAL / AGENCIA</th>
                <th>FECHA DE INGRESO</th>
                <th>ACCIÓN</th>
            </tr>
          </thead>
          <tbody>
            @foreach (\$headers as \$header)
              @foreach (\$header->details as \$detail)
                <tr>
                    <td>{{ \$header->prefix }}-{{ \$header->issue_number }}</td>
                    <td>{{ \$detail->client->full_name }}</td>
                    <td>{{ \$detail->client->dni }} {{ \$detail->client->extension }}</td>
                    <td>{{ \$header->total_premium }} {{ \$header->currency }}</td>
                    <td>{{ \$header->term }}</td>
                    <td>{{ \$header->user->full_name }}</td>
                    <td>
                      {{ ! is_null(\$header->user->city) ? \$header->user->city->name : '' }}
                      {{ ! is_null(\$header->user->agency) ? '/ ' . \$header->user->agency->name : '' }}
                    </td>
                    <td>
                      {{ \$header->created_date }}
                    </td>
                    <td>
                      <ul class=\"icons-list\">
                          <li class=\"dropdown\">
                              <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">
                                  <i class=\"icon-menu9\"></i>
                              </a>
                              <ul class=\"dropdown-menu dropdown-menu-right\">
                                  <li>
                                      <a href=\"{{ route(\$retailerProduct->companyProduct->product->code.'.edit', ['rp_id' => \$rp_id, 'header_id' => encode(\$header->id)]) }}\" >
                                        <i class=\"icon-database-edit2\"></i> Editar Póliza
                                      </a>
                                  </li>
                              </ul>
                          </li>
                      </ul>
                    </td>
                </tr>
              @endforeach
            @endforeach
          </tbody>
        </table>
    </div>    
@endsection
";
}

function newListQuoteBlade($code)
{
    return "@extends('body')
@var \$step=0
@section('body')
    <div class=\"col-xs-12\">
        <h6>Emisión de Solicitudes</h6>
        <hr />
        {!! Form::open(['route' => [\$retailerProduct->companyProduct->product->code.'.issue.lists', 'rp_id' => \$rp_id], 'method' => 'get', 'class' => 'form-horizontal']) !!}
        <div class=\"col-xs-12 col-md-12\">
            @include('report.partials.inputs-search', ['type' => 'Q'])
        </div>
        {!! Form::close() !!}

        <table class=\"table datatable-fixed-left table-striped small_body\" width=\"100%\">
            <thead>
            <tr>
                <th>NRO DE COTIZACIÓN</th>
                <th>CLIENTE</th>
                <th>C.I.</th>
                <th>USUARIO</th>
                <th>SUCURSAL / AGENCIA</th>
                <th>FECHA DE INGRESO</th>
                <th>ACCIÓN</th>
            </tr>
            </thead>
            <tbody>
            @foreach (\$headers as \$header)
                @foreach (\$header->details as \$detail)
                    <tr>
                        <td>{{ \$header->quote_number }}</td>
                        <td>{{ \$detail->client->full_name }}</td>
                        <td>{{ \$detail->client->dni }} {{ \$detail->client->extension }}</td>
                        <td>{{ \$header->user->full_name }}</td>
                        <td>
                            {{ ! is_null(\$header->user->city) ? \$header->user->city->name : '' }}
                            {{ ! is_null(\$header->user->agency) ? '/ ' . \$header->user->agency->name : '' }}
                        </td>
                        <td>
                            {{ \$header->created_date }}
                        </td>
                        <td>
                            <ul class=\"icons-list\">
                                <li class=\"dropdown\">
                                    <a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">
                                        <i class=\"icon-menu9\"></i>
                                    </a>
                                    <ul class=\"dropdown-menu dropdown-menu-right\">
                                        <li>
                                            @if (\$header->days_from_creation > \$parameter->expiration)
                                                <a href=\"#\">
                                                    <span class=\"label label-danger\">Fecha limite de emisión caducada</span>
                                                </a>
                                            @else
                                                <a href=\"{{ route(\$retailerProduct->companyProduct->product->code.'.result', ['rp_id' => \$rp_id, 'header_id' => encode(\$header->id)]) }}\">
                                                    <i class=\"icon-database-edit2\"></i> Emitir Solicitud
                                                </a>
                                            @endif
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>
@endsection";
}

function newEditissueClientBlade($code)
{
    return "@extends('body')
@var \$step=2
@section('body')
    {!! Form::open(['route' => [\$retailerProduct->companyProduct->product->code.'.detail.i.update',  'rp_id' => \$rp_id, 'header_id' => \$header_id, 'detail_id' => \$detail_id], 
    'method' => 'put', 'class' => 'form-horizontal']) !!}
        <div class=\"panel-body \">
            @include('client.'.\$retailerProduct->companyProduct->product->code.'.partials.inputs-quote', ['form' => 'edit_issue'])

            <div class=\"text-right\">
                <a href=\"{{ route(\$retailerProduct->companyProduct->product->code.'.edit', ['rp_id' => \$rp_id, 'header_id' => \$header_id]) }}\"
                   class=\"btn border-slate text-slate-800 btn-flat\">Cancelar</a>

                {!! Form::button('Actualizar <i class=\"icon-arrow-right14 position-right\"></i>', [
                    'type' => 'submit',
                    'class' => 'btn btn-primary'])
                !!}
            </div>
        </div>
    {!! Form::close() !!}
@endsection";
}

function newEditClientBlade($code)
{
    return "@extends('body')
@var \$step=2
@section('body')
    {!! Form::open(['route' => [\$retailerProduct->companyProduct->product->code.'.detail.update',  'rp_id' => \$rp_id, 'header_id' => \$header_id, 'detail_id' => \$detail_id], 'method' => 'put', 'class' => 'form-horizontal']) !!}
        <div class=\"panel-body \">
            @include('client.'.\$retailerProduct->companyProduct->product->code.'.partials.inputs-quote', ['form' => 'create'])
            <div class=\"text-right\">
                <a href=\"{{ route(\$retailerProduct->companyProduct->product->code.'.client.list', ['rp_id' => \$rp_id, 'header_id' => \$header_id]) }}\"
                   class=\"btn border-slate text-slate-800 btn-flat\">Cancelar</a>
                {!! Form::button('Actualizar Cliente <i class=\"icon-arrow-right14 position-right\"></i>', [
                    'type' => 'submit',
                    'class' => 'btn btn-primary'])
                !!}
            </div>
        </div>
    {!! Form::close() !!}
@endsection";
}

function newInputsquoteClientBlade($code)
{
    return "@if(\$form == 'create')
    <input type=\"hidden\" value=\"{{ old('code', \$client->code) }}\" name=\"code\">
    <input type=\"hidden\" value=\"{{ (isset(\$detail_id))?1:0 }}\" name=\"edition\">
    <input type=\"hidden\" value=\"{{ old('operations', empty(\$client->operations) ? json_encode([]) : \$client->operations) }}\" name=\"operations\">
@endif
<input type=\"hidden\" value=\"{{ old('type', \$client->type) }}\" name=\"type\">
<div class=\"col-xs-12 col-md-6\">
    @if(\$form == 'create')
        @include('partials.fields', ['type'=>'text', 'label'=>'Nombres', 'id'=>'first_name','required'=>1,'value'=>\$client->first_name])
        @include('partials.fields', ['type'=>'text',    'label'=>'Apellido Paterno',    'id'=>'last_name',          'required'=>1,'value'=>\$client->last_name])
        @include('partials.fields', ['type'=>'text',    'label'=>'Apellido Materno',    'id'=>'mother_last_name',   'required'=>0,'value'=>\$client->mother_last_name])
        @include('partials.fields', ['type'=>'text',    'label'=>'Apellido de Casada',  'id'=>'married_name',       'required'=>0,'value'=>\$client->married_name])
        @include('partials.fields', ['type'=>'text',    'label'=>'C.I.',        'id'=>'dni',            'required'=>1,'value'=>\$client->dni])
        @include('partials.fields', ['type'=>'text',    'label'=>'Complemento', 'id'=>'complement',     'required'=>0,'value'=>\$client->complement])
        @include('partials.fields', ['type'=>'combo',   'label'=>'Extensión',   'id'=>'extension',      'required'=>1,'value'=>\$client->extension,'array'=>\$data['cities']['CI']->toArray()])
        @include('partials.fields', ['type'=>'date','label'=>'Fecha de nacimiento','id'=>'birthdate','required'=>1,'value'=>\$client->birthdate])    
        @include('partials.fields', ['type'=>'text', 'label'=>'Lugar de Nacimiento', 'id'=>'birth_place', 'required'=>1,'value'=>\$client->birth_place])
        @include('partials.fields', ['type'=>'text', 'label'=>'Pais de Residencia', 'id'=>'place_residence', 'required'=>1,'value'=>\$client->place_residence])
    @elseif(\$form == 'edit_issue')
        @include('partials.fields', ['type'=>'label', 'label'=>'Nombres', 'value'=>\$client->first_name])
        @include('partials.fields', ['type'=>'label', 'label'=>'Apellido Paterno', 'value'=>\$client->last_name])
        @include('partials.fields', ['type'=>'label', 'label'=>'Apellido Materno', 'value'=>\$client->mother_last_name])
        @include('partials.fields', ['type'=>'label', 'label'=>'Apellido de Casada', 'value'=>\$client->married_name])
        @include('partials.fields', ['type'=>'label', 'label'=>'C.I.', 'value'=>\$client->dni])
        @include('partials.fields', ['type'=>'label', 'label'=>'Complemento', 'value'=>\$client->complement])
        @include('partials.fields', ['type'=>'label', 'label'=>'Extensión', 'value'=>\$client->extension,'array'=>\$data['cities']['CI']->toArray()])
        @include('partials.fields', ['type'=>'label', 'label'=>'Fecha de nacimiento', 'value'=>\$client->birthdate])    
        @include('partials.fields', ['type'=>'label', 'label'=>'Lugar de Nacimiento', 'value'=>\$client->birth_place])
    @endif
    @include('partials.fields', ['type'=>'textarea','label'=>'Dirección','id'=>'home_address','required'=>1,'value'=>\$client->home_address])
</div>
<div class=\"col-xs-12 col-md-6\">
    @include('partials.fields', ['type'=>'combo',   'label'=>'Estado Civil',               'id'=>'civil_status',         'required'=>1,'value'=>\$client->civil_status,'array'=>\$data['civil_status']->toArray()])    
    @include('partials.fields', ['type'=>'text',    'label'=>'Teléfono Celular',        'id'=>'phone_number_mobile',    'required'=>0,'value'=>\$client->phone_number_mobile, 'icon'=>'icon-phone'])
    @include('partials.fields', ['type'=>'text',    'label'=>'Email',                   'id'=>'email',                  'required'=>0,'value'=>\$client->email, 'icon'=>'icon-mail5'])
    @include('partials.fields', ['type'=>'combo',   'label'=>'Ocupación',               'id'=>'ad_activity_id',         'required'=>1,'value'=>\$client->ad_activity_id,'array'=>\$data['activities']->toArray()])    
    @include('partials.fields', ['type'=>'textarea','label'=>'¿Cual es su profesion u ocupacion habitual?',   'id'=>'occupation_description', 'required'=>1,'value'=>\$client->occupation_description])
    @include('partials.fields', ['type'=>'textarea','label'=>'Lugar de trabajo',   'id'=>'business_address', 'required'=>1,'value'=>\$client->business_address])
    @include('partials.fields', ['type'=>'text', 'label'=>'Cargo', 'id'=>'position', 'required'=>0,'value'=>\$client->position])
    @include('partials.fields', ['type'=>'date', 'label'=>'Fecha de Ingreso','id'=>'date_admission','required'=>0,'value'=>\$client->date_admission])    
    @include('partials.fields', ['type'=>'text', 'label'=>'Zona (Comercial)', 'id'=>'zone', 'required'=>0,'value'=>\$client->zone])
    
    @if(\$form == 'edit_issue')
        @include('partials.fields', ['type'=>'text', 'label'=>'Lugar de Trabajo', 'id'=>'workplace', 'required'=>0,'value'=>\$client->workplace])
        @include('partials.fields', ['type'=>'text',    'label'=>'Peso (Kg)',        'id'=>'weight',            'required'=>0,'value'=>\$client->weight])
        @include('partials.fields', ['type'=>'text',    'label'=>'Estatura (cm)',        'id'=>'height',            'required'=>0,'value'=>\$client->height])
        @include('partials.fields', ['type'=>'textarea','label'=>'Dirección Comercial (Dirección Laboral)', 'id'=>'commercial_address', 'required'=>0,'value'=>\$client->commercial_address])
        @include('partials.fields', ['type'=>'text', 'label'=>'Nombre Cónyugue (Si corresponde)', 'id'=>'name_spouse', 'required'=>0,'value'=>\$client->name_spouse])
        @include('partials.fields', ['type'=>'text', 'label'=>'Referencia Personal', 'id'=>'personal_reference', 'required'=>0,'value'=>\$client->personal_reference])
    @endif
</div>
";
}

function newCertificateBlade($code)
{
    return "@for (\$i = 1; \$i <= 2; \$i++)
    @foreach(\$header->details as \$dk => \$detail)
        <div style=\"width: 725px; font-weight: normal; font-size: 11px; font-family: Arial, Helvetica, sans-serif; color: #000000; border: 0px solid #FFFF00;\">
            @if(\$i>1)
                <page>
                    <div style=\"page-break-before: always;\">&nbsp;</div>
                </page>
            @endif
            <div style=\"font-size: 50%; text-align: justify; width: 100%;\">
                <div style=\"text-align: center; font-weight: bold;\">
                    DECLARACION JURADA DE SALUD<br />
                </div>
                <br /><br />
                <strong>1. DATOS PERSONALES DEL ASEGURADO:</strong>
                <br />
                <table cellpadding=\"0\" cellspacing=\"0\" style=\"width: 100%; font-size: 50%;\">
                    <tr>
                        <td style=\"width: 13%;\">Nombre del Asegurado:</td>
                        <td style=\"width: 37%; border-bottom: 1px dotted #000000;text-align: center;\">
                            {{\$detail->client->first_name}}
                        </td>
                        <td style=\"width: 5%;\">Apellidos: </td>
                        <td style=\"width: 45%; border-bottom: 1px dotted #000000;text-align: center;\">
                            {{\$detail->client->last_name}} {{\$detail->client->mother_last_name}}
                            @if(\$detail->client->married_name)
                                @if(\$detail->client->civil_status == 'V')
                                    Vda. de {{\$detail->client->married_name}}
                                @else
                                    de {{\$detail->client->married_name}}
                                @endif
                            @endif
                        </td>
                    </tr>
                </table>
                <br />
                <strong>4. BENEFICIARIOS</strong>
                <br />
                <table cellpadding=\"0\" cellspacing=\"0\" style=\"width: 100%; font-size: 50%; text-align: justify;\">
                    @foreach(\$detail->beneficiaries as \$Bkey => \$beneficiary)
                    <tr>
                        <td style=\"width: 20%; background: #00008b; color: ghostwhite;padding: 5px;\"> Beneficiario {{\$Bkey+1}}</td>
                        <td style=\"width: 30%;padding: 5px;background: #FCE1E5;\"> {{\$beneficiary->full_name}}</td>
                        <td style=\"width: 15%; text-align: center;padding: 5px;background: #FCE1E5;\"> {{\$beneficiary->relationship}}</td>
                        <td style=\"width: 20%; text-align: center;padding: 5px;background: #FCE1E5;\"> {{\$beneficiary->full_dni}}</td>
                        <td style=\"width: 15%; text-align: center;padding: 5px;background: #FCE1E5;\"> {{\$beneficiary->percentage}}</td>
                    </tr>
                    @endforeach
                </table>
                <br />
                <strong>5. CUESTIONARIO: </strong> (Marque con una X si corresponde)<br />
                <br />
                <table cellpadding=\"0\" cellspacing=\"0\" style=\"width: 100%; font-size: 50%; text-align: justify;\">
                    @foreach(json_decode(\$detail->response->response) as \$Qkey => \$question)
                        <tr>
                            <td style=\"width: 97%;\">{{\$question->question}}</td>
                            <td style=\"border: 1px solid #000000; width: 3%; text-align: center;\">
                                {{\$question->response?'X':''}}
                            </td>
                            <td style=\"width: 80%;\"></td>
                        </tr>
                        <tr>
                            <td colspan=\"3\">&nbsp;</td>
                        </tr>
                    @endforeach
                </table>
                <br />
                Declaro que todas las respuestas en esta solicitud son ciertas según leal saber y entender, y comprendo que
            </div>
            @if(\$header->issued && ! \$header->canceled)
            <page>
                <div style=\"page-break-before: always;\">&nbsp;</div>
            </page>
            <div style=\"font-size: 50%; text-align: justify; width: 100%;\">
                <div style=\"text-align: center; font-weight: bold;\">
                    CERTIFICADO DE EMISION
                    <br />
                    CERTIFICADO DE COBERTURA N° {{\$header->issue_number}}
                </div>
            </div>
            @endif
        </div>
    @endforeach
@endfor";
}