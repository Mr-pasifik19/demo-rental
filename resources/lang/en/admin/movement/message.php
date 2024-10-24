<?php

return [

    'undeployable'         => '<strong>Warning: </strong> This Movement has been marked as currently undeployable.
                        If this status has changed, please update the Movement status.',
    'does_not_exist'     => 'Movement does not exist.',
    'does_not_exist_or_not_requestable' => 'That Movement does not exist or is not requestable.',
    'assoc_users'         => 'This Movement is currently checked out to a user and cannot be deleted. Please check the Movement in first, and then try deleting again. ',

    'create' => [
        'error'           => 'Movement was not created, please try again. :(',
        'success'         => 'Movement created successfully. :)',
    ],
    'change_status' => [
        'error'           => 'Change status movement, please try again. :(',
        'error_selectdate'           => 'Change status movement, please select date!. :(',
        'success'         => 'Change status movement successfully. :)',
    ],
    'project_movement' => [
        'error'           => 'Project Movement was not created, please try again. :(',
        'errorDelete'           => 'Project Movement was not deleted, please try again. :(',
        'errorUpdate'           => 'Project Movement was not updated, please try again. :(',
        'success'         => 'Project Movement created successfully. :)',
        'successDelete'         => 'Project Movement deleted successfully. :)',
        'successUpdate'         => 'Project Movement updated successfully. :)',
        'notFound' => 'Project Movement not found'
    ],
    'company_movement' => [
        'error'           => 'Company Branch was not created, please try again. :(',
        'errorDelete'           => 'Company Branch was not deleted, please try again. :(',
        'errorUpdate'           => 'Company Branch was not updated, please try again. :(',
        'success'         => 'Company Branch created successfully. :)',
        'successUpdate'         => 'Company Branch updated successfully. :)',
        'successDelete'         => 'Company Branch deleted successfully. :)',
        'notFound' => 'Company Branch not found'
    ],

    'update' => [
        'error'               => 'Movement was not updated, please try again',
        'success'             => 'Movement updated successfully.',
        'nothing_updated'    =>  'No fields were selected, so nothing was updated.',
        'no_movements_selected'  =>  'No Movements were selected, so nothing was updated.',
    ],

    'restore' => [
        'error'           => 'Movement was not restored, please try again',
        'success'         => 'Movement restored successfully.',
        'bulk_success'         => 'Movement restored successfully.',
        'nothing_updated'   => 'No Movements were selected, so nothing was restored.',
    ],

    'audit' => [
        'error'           => 'Movement audit was unsuccessful. Please try again.',
        'success'         => 'Movement audit successfully logged.',
    ],


    'deletefile' => [
        'error'   => 'File not deleted. Please try again.',
        'success' => 'File successfully deleted.',
    ],

    'upload' => [
        'error'   => 'File(s) not uploaded. Please try again.',
        'success' => 'File(s) successfully uploaded.',
        'nofiles' => 'You did not select any files for upload, or the file you are trying to upload is too large',
        'invalidfiles' => 'One or more of your files is too large or is a filetype that is not allowed. Allowed filetypes are png, gif, jpg, doc, docx, pdf, and txt.',
    ],

    'import' => [
        'error'                 => 'Some items did not import correctly.',
        'errorDetail'           => 'The following Items were not imported because of errors.',
        'success'               => 'Your file has been imported',
        'file_delete_success'   => 'Your file has been been successfully deleted',
        'file_delete_error'      => 'The file was unable to be deleted',
        'header_row_has_malformed_characters' => 'One or more attributes in the header row contain malformed UTF-8 characters',
        'content_row_has_malformed_characters' => 'One or more attributes in the first row of content contain malformed UTF-8 characters',
    ],


    'delete' => [
        'confirm'       => 'Are you sure you wish to delete this Movement?',
        'error'           => 'There was an issue deleting the Movement. Please try again.',
        'nothing_updated'   => 'No Movements were selected, so nothing was deleted.',
        'success'         => 'The Movement was deleted successfully.',
    ],

    'checkout' => [
        'error'           => 'Movement was not checked out, please try again',
        'success'         => 'Movement checked out successfully.',
        'user_does_not_exist' => 'That user is invalid. Please try again.',
        'not_available' => 'That Movement is not available for checkout!',
        'no_movements_selected' => 'You must select at least one Movement from the list',
    ],

    'checkin' => [
        'error'           => 'Movement was not checked in, please try again',
        'success'         => 'Movement checked in successfully.',
        'user_does_not_exist' => 'That user is invalid. Please try again.',
        'already_checked_in'  => 'That Movement is already checked in.',

    ],

    'requests' => [
        'error'           => 'Movement was not requested, please try again',
        'success'         => 'Movement requested successfully.',
        'canceled'      => 'Checkout request successfully canceled',
    ],

];
