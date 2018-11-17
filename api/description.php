<?php

$returnValue = array(
    'profile' => array(
        'create' => array(
            'call' => 'POST',
            'path' => '/api/profile/create/',
            'param' => 'String userName, String firstName, String lastName, String email, String password',
        ),
        'activate' => array(
            'call' => 'GET',
            'path' => '/api/profile/activate/',
            'param' => 'String activateCode',
        ),
        'login' => array(
            'call' => 'POST',
            'path' => '/api/profile/login/',
            'param' => 'String userName, String password',
        ),
        'delete' => array(
            'call' => 'POST',
            'path' => '/api/profile/delete',
            'param' => 'String password',
        ),
        'uploadAvatar' => array(
            'call' => 'POST',
            'path' => '/api/profile/uploadAvatar',
            'param' => 'File image',
        ),
        'lists' => array(
            'call' => 'GET',
            'path' => '/api/profile/lists',
            'param' => '',
        ),
        'sharedlists' => array(
            'call' => 'GET',
            'path' => '/api/profile/sharedlists',
            'param' => '',
        ),)
);
if (isLoggedIn()) {
    $returnValue += array(
        'To-Do List' => array(
            'create' => array(
                'call' => 'POST',
                'path' => '/api/todolist/create',
                'param' => 'String listName',
            ),
            'delete' => array(
                'call' => 'POST',
                'path' => '/api/todolist/{listId}/delete',
                'param' => '',
            ),
            'list items' => array(
                'call' => 'GET',
                'path' => '/api/todolist/{listId}/items',
                'param' => '(optional) Datetime or Date lastCall',
            ),
            'share list' => array(
                'call' => 'POST',
                'path' => '/api/todolist/{listId}/share',
                'param' => 'String userName',
            ),
            'activate list' => array(
                'call' => 'GET',
                'path' => '/api/todolist/activate',
                'param' => 'String activateCode',
            ),
        ),
        'Items' => array(
            'add' => array(
                'call' => 'POST',
                'path' => '/api/todolist/{listId}/items/add',
                'param' => 'String itemName',
            ),
            'edit' => array(
                'call' => 'POST',
                'path' => '/api/todolist/{listId}/items/{itemId}',
                'param' => 'String itemName, date deadline, int sortIndex',
            ),
            'delete' => array(
                'call' => 'POST',
                'path' => '/api/todolist/{listId}/items/delete',
                'param' => 'int itemId',
            ),
        ),);
}


