<?php

return array(

    'does_not_exist' => 'Family does not exist.',
    'no_association' => 'NO Family ASSOCIATED.',
    'no_association_fix' => 'This will break things in weird and horrible ways. Edit this asset now to assign it a model.',
    'assoc_users'	 => 'This model is currently associated with one or more assets and cannot be deleted. Please delete the assets, and then try deleting again. ',


    'create' => array(
        'error'   => 'Family was not created, please try again.',
        'success' => 'Family created successfully.',
        'duplicate_set' => 'An asset Family with that name, manufacturer and Family number already exists.',
    ),

    'update' => array(
        'error'   => 'Family was not updated, please try again',
        'success' => 'Family updated successfully.',
    ),

    'delete' => array(
        'confirm'   => 'Are you sure you wish to delete this asset Family?',
        'error'   => 'There was an issue deleting the Family. Please try again.',
        'success' => 'The Family was deleted successfully.'
    ),

    'restore' => array(
        'error'   		=> 'Family was not restored, please try again',
        'success' 		=> 'Family restored successfully.'
    ),

    'bulkedit' => array(
        'error'   		=> 'No fields were changed, so nothing was updated.',
        'success' 		=> 'Family successfully updated. |:model_count models successfully updated.',
        'warn'          => 'You are about to update the properies of the following Family: |You are about to edit the properties of the following :model_count models:',

    ),

    'bulkdelete' => array(
        'error'   		    => 'No Family were selected, so nothing was deleted.',
        'success' 		    => 'Model deleted!|:success_count Family deleted!',
        'success_partial' 	=> ':success_count model(s) were deleted, however :fail_count were unable to be deleted because they still have assets associated with them.'
    ),

);
