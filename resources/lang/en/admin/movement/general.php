<?php

return [
    'about_movements_title'           => 'About Movements',
    'about_movements_text'            => 'Movements are items tracked by serial number or Movement tag.  They tend to be higher value items where identifying a specific item matters.',
    'archived'  				=> 'Archived',
    'movement'  					=> 'Movement',
    'bulk_checkout'             => 'Checkout Movements',
    'bulk_checkin'              => 'Checkin Movements',
    'checkin'  					=> 'Checkin Movement',
    'checkout'  				=> 'Checkout Movement',
    'clone'  					=> 'Clone Movement',
    'deployable'  				=> 'Ready For Rent',
    'deleted'  					=> 'This Movement has been deleted.',
    'edit'  					=> 'Edit Movement',
    'model_deleted'  			=> 'This Movements model has been deleted. You must restore the model before you can restore the Movement.',
    'model_invalid'             => 'The Model of this Movement is invalid.',
    'model_invalid_fix'         => 'The Movement should be edited to correct this before attempting to check it in or out.',
    'requestable'               => 'Requestable',
    'requested'				    => 'Requested',
    'not_requestable'           => 'Not Requestable',
    'requestable_status_warning' => 'Do not change requestable status',
    'restore'  					=> 'Restore Movement',
    'pending'  					=> 'Not Ready For Rent For This Current Time',
    'undeployable'  			=> 'Not Ready For Rent',
    'undeployable_tooltip'  	=> 'This Movement has a status label that is Not Ready For Rent and cannot be checked out at this time.',
    'view'  					=> 'View Movement',
    'csv_error' => 'You have an error in your CSV file:',
    'import_text' => '
    <p>
    Upload a CSV that contains Movement history. The Movements and users MUST already exist in the system, or they will be skipped. Matching Movements for history import happens against the Movement number. We will try to find a matching user based on the user\'s name you provide, and the criteria you select below.
    </p>

    <p>Fields included in the CSV must match the headers: <strong>Movement Number, Name, Checkout Date, Checkin Date</strong>. Any additional fields will be ignored. </p>

    <p>Checkin Date: blank or future checkin dates will checkout items to associated user.  Excluding the Checkin Date column will create a checkin date with todays date.</p>
    ',
    'csv_import_match_f-l' => 'Try to match users by firstname.lastname (jane.smith) format',
    'csv_import_match_initial_last' => 'Try to match users by first initial last name (jsmith) format',
    'csv_import_match_first' => 'Try to match users by first name (jane) format',
    'csv_import_match_email' => 'Try to match users by email as username',
    'csv_import_match_username' => 'Try to match users by username',
    'error_messages' => 'Error messages:',
    'success_messages' => 'Success messages:',
    'alert_details' => 'Please see below for details.',
    'custom_export' => 'Custom Export',
    'mfg_warranty_lookup' => ':manufacturer Warranty Status Lookup',
];
