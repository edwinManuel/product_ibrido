<?php

function newRoutes($code)
{
    return "<?php
/*
 * Route Issuance ". ucfirst($code)."
 */
 Route::group(['middleware' => ['auth']], function () {
    Route::group(['middleware' => 'is_admin'], function () {
        Route::group(['prefix' => '".$code."/{rp_id}'], function () {

            Route::get('create', [
                'as' => '".$code.".create',
                'uses' => '". ucfirst($code)."\DetailController@create'
            ]);

            Route::post('store', [
                'as' => '".$code.".store',
                'uses' => '". ucfirst($code)."\DetailController@store'
            ]);

            Route::get('{header_id}/list', [
                'as' => '".$code.".client.list',
                'uses' => '". ucfirst($code)."\HeaderController@lists'
            ]);

            /*
             * Client edit
             */
            Route::get('{header_id}/client/edit/{detail_id}', [
                'as' => '".$code.".detail.edit',
                'uses' => '". ucfirst($code)."\DetailController@edit'
            ]);

            Route::put('{header_id}/client/edit/{detail_id}', [
                'as' => '".$code.".detail.update',
                'uses' => '". ucfirst($code)."\DetailController@update'
            ]);

            /*
             * Header result on Quote
             */
            Route::get('{header_id}/result', [
                'as' => '".$code.".result',
                'uses' => '". ucfirst($code)."\HeaderController@result'
            ]);

            /*
             * Header edit
             */
            Route::get('{header_id}/edit', [
                'as' => '".$code.".edit',
                'uses' => '". ucfirst($code)."\HeaderController@edit'
            ]);

            Route::put('edit/{header_id}', [
                'as' => '".$code.".update',
                'uses' => '". ucfirst($code)."\HeaderController@update'
            ]);

            /*
             * Header issuance
             */
            Route::get('issue/{header_id}', [
                'as' => '".$code.".issue',
                'uses' => '". ucfirst($code)."\HeaderController@issuance'
            ]);

            Route::get('issuance/{header_id}/result', [
                'as' => '".$code.".show.issuance',
                'uses' => '". ucfirst($code)."\HeaderController@showIssuance'
            ]);

            /*
             * Pre-Approved
             */
            Route::get('pre-approved', [
                'as' => '".$code.".pre.approved.lists',
                'uses' => '". ucfirst($code)."\PreApprovedController@lists'
            ]);

            /*
             * Issue Quote
             */
            Route::get('issue/{guest?}', [
                'as' => '".$code.".issue.lists',
                'uses' => '". ucfirst($code)."\IssueController@lists'
            ]);


            /*
             * Cancellations
             */
            Route::get('cancel', [
                'as' => '".$code.".cancel.lists',
                'uses' => '". ucfirst($code)."\CancellationController@lists'
            ]);

            Route::get('cancel/{header_id}/create', [
                'as' => '".$code.".cancel.create',
                'uses' => '". ucfirst($code)."\CancellationController@create'
            ]);

            Route::post('cancel/{header_id}/create', [
                'as' => '".$code.".cancel.store',
                'uses' => '". ucfirst($code)."\CancellationController@store'
            ]);
        });

        Route::group(['prefix' => '".$code."/{rp_id}/{header_id}'], function () {
            
            Route::get('beneficiary/create/{detail_id}', [
                'as' => '".$code.".beneficiary.create',
                'uses' => '". ucfirst($code)."\BeneficiaryController@create'
            ]);

            Route::post('beneficiary/create/{detail_id}', [
                'as' => '".$code.".beneficiary.store',
                'uses' => '". ucfirst($code)."\BeneficiaryController@store'
            ]);

            Route::get('beneficiary/edit/{detail_id}/{beneficiary_id}', [
                'as' => '".$code.".beneficiary.edit',
                'uses' => '". ucfirst($code)."\BeneficiaryController@edit'
            ]);

            Route::put('beneficiary/edit/{detail_id}/{beneficiary_id}', [
                'as' => '".$code.".beneficiary.update',
                'uses' => '". ucfirst($code)."\BeneficiaryController@update'
            ]);

            Route::delete('beneficiary/{beneficiary_id}', [
                'as' => '".$code.".beneficiary.destroy',
                'uses' => '". ucfirst($code)."\BeneficiaryController@destroy'
            ]);

            /*
             * Client edit complementary data
             */
            Route::get('edit/detail/edit/{detail_id}', [
                'as' => '".$code.".detail.i.edit',
                'uses' => '". ucfirst($code)."\DetailController@editIssue'
            ]);

            Route::put('edit/detail/edit/{detail_id}', [
                'as' => '".$code.".detail.i.update',
                'uses' => '". ucfirst($code)."\DetailController@updateIssue'
            ]);
        });
    });
});
";
}