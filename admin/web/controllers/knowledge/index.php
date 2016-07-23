<?php

/*
 * This file is part of the CRUD Admin Generator project.
 *
 * Author: Jon Segador <jonseg@gmail.com>
 * Web: http://crud-admin-generator.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


require_once __DIR__.'/../../../vendor/autoload.php';
require_once __DIR__.'/../../../src/app.php';

use Symfony\Component\Validator\Constraints as Assert;

$app->match('/knowledge/list', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {  
    $start = 0;
    $vars = $request->query->all();
    $qsStart = (int)$vars["start"];
    $search = $vars["search"];
    $order = $vars["order"];
    $columns = $vars["columns"];
    $qsLength = (int)$vars["length"];    
    
    if($qsStart) {
        $start = $qsStart;
    }    
	
    $index = $start;   
    $rowsPerPage = $qsLength;
       
    $rows = array();
    
    $searchValue = $search['value'];
    $orderValue = $order[0];
    
    $orderClause = "";
    if($orderValue) {
        $orderClause = " ORDER BY ". $columns[(int)$orderValue['column']]['data'] . " " . $orderValue['dir'];
    }
    
    $table_columns = array(
		'id', 
		'time', 
		'installation_id', 
		'question_id', 
		'trained', 

    );
    
    $table_columns_type = array(
		'int(11)', 
		'timestamp', 
		'int(11)', 
		'int(11)', 
		'int(1)', 

    );    
    
    $whereClause = "";
    
    $i = 0;
    foreach($table_columns as $col){
        
        if ($i == 0) {
           $whereClause = " WHERE";
        }
        
        if ($i > 0) {
            $whereClause =  $whereClause . " OR"; 
        }
        
        $whereClause =  $whereClause . " " . $col . " LIKE '%". $searchValue ."%'";
        
        $i = $i + 1;
    }
    
    $recordsTotal = $app['db']->executeQuery("SELECT * FROM `knowledge`" . $whereClause . $orderClause)->rowCount();
    
    $find_sql = "SELECT * FROM `knowledge`". $whereClause . $orderClause . " LIMIT ". $index . "," . $rowsPerPage;
    $rows_sql = $app['db']->fetchAll($find_sql, array());

    foreach($rows_sql as $row_key => $row_sql){
        for($i = 0; $i < count($table_columns); $i++){

			if($table_columns[$i] == 'installation_id'){
			    $findexternal_sql = 'SELECT `deviceToken` FROM `installation` WHERE `id` = ?';
			    $findexternal_row = $app['db']->fetchAssoc($findexternal_sql, array($row_sql[$table_columns[$i]]));
			    $rows[$row_key][$table_columns[$i]] = $findexternal_row['deviceToken'];
			}
			else if($table_columns[$i] == 'question_id'){
			    $findexternal_sql = 'SELECT `label` FROM `question` WHERE `id` = ?';
			    $findexternal_row = $app['db']->fetchAssoc($findexternal_sql, array($row_sql[$table_columns[$i]]));
			    $rows[$row_key][$table_columns[$i]] = $findexternal_row['label'];
			}
			else{
			    $rows[$row_key][$table_columns[$i]] = $row_sql[$table_columns[$i]];
			}


        }
    }    
    
    $queryData = new queryData();
    $queryData->start = $start;
    $queryData->recordsTotal = $recordsTotal;
    $queryData->recordsFiltered = $recordsTotal;
    $queryData->data = $rows;
    
    return new Symfony\Component\HttpFoundation\Response(json_encode($queryData), 200);
});




/* Download blob img */
$app->match('/knowledge/download', function (Symfony\Component\HttpFoundation\Request $request) use ($app) { 
    
    // menu
    $rowid = $request->get('id');
    $idfldname = $request->get('idfld');
    $fieldname = $request->get('fldname');
    
    if( !$rowid || !$fieldname ) die("Invalid data");
    
    $find_sql = "SELECT " . $fieldname . " FROM " . knowledge . " WHERE ".$idfldname." = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($rowid));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('menu_list'));
    }

    header('Content-Description: File Transfer');
    header('Content-Type: image/jpeg');
    header("Content-length: ".strlen( $row_sql[$fieldname] ));
    header('Expires: 0');
    header('Cache-Control: public');
    header('Pragma: public');
    ob_clean();    
    echo $row_sql[$fieldname];
    exit();
   
    
});



$app->match('/knowledge', function () use ($app) {
    
	$table_columns = array(
		'id', 
		'time', 
		'installation_id', 
		'question_id', 
		'trained', 

    );

    $primary_key = "id";	

    return $app['twig']->render('knowledge/list.html.twig', array(
    	"table_columns" => $table_columns,
        "primary_key" => $primary_key
    ));
        
})
->bind('knowledge_list');



$app->match('/knowledge/create', function () use ($app) {
    
    $initial_data = array(
		'time' => '', 
		'installation_id' => '', 
		'question_id' => '', 
		'trained' => '', 

    );

    $form = $app['form.factory']->createBuilder('form', $initial_data);

	$options = array();
	$findexternal_sql = 'SELECT `id`, `deviceToken` FROM `installation`';
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['id']] = $findexternal_row['deviceToken'];
	}
	if(count($options) > 0){
	    $form = $form->add('installation_id', 'choice', array(
	        'required' => true,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    ));
	}
	else{
	    $form = $form->add('installation_id', 'text', array('required' => true));
	}

	$options = array();
	$findexternal_sql = 'SELECT `id`, `label` FROM `question`';
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['id']] = $findexternal_row['label'];
	}
	if(count($options) > 0){
	    $form = $form->add('question_id', 'choice', array(
	        'required' => true,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    ));
	}
	else{
	    $form = $form->add('question_id', 'text', array('required' => true));
	}



	$form = $form->add('time', 'text', array('required' => true));
	$form = $form->add('trained', 'text', array('required' => true));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "INSERT INTO `knowledge` (`time`, `installation_id`, `question_id`, `trained`) VALUES (?, ?, ?, ?)";
            $app['db']->executeUpdate($update_query, array($data['time'], $data['installation_id'], $data['question_id'], $data['trained']));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'knowledge created!',
                )
            );
            return $app->redirect($app['url_generator']->generate('knowledge_list'));

        }
    }

    return $app['twig']->render('knowledge/create.html.twig', array(
        "form" => $form->createView()
    ));
        
})
->bind('knowledge_create');



$app->match('/knowledge/edit/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `knowledge` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if(!$row_sql){
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );        
        return $app->redirect($app['url_generator']->generate('knowledge_list'));
    }

    
    $initial_data = array(
		'time' => $row_sql['time'], 
		'installation_id' => $row_sql['installation_id'], 
		'question_id' => $row_sql['question_id'], 
		'trained' => $row_sql['trained'], 

    );


    $form = $app['form.factory']->createBuilder('form', $initial_data);

	$options = array();
	$findexternal_sql = 'SELECT `id`, `deviceToken` FROM `installation`';
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['id']] = $findexternal_row['deviceToken'];
	}
	if(count($options) > 0){
	    $form = $form->add('installation_id', 'choice', array(
	        'required' => true,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    ));
	}
	else{
	    $form = $form->add('installation_id', 'text', array('required' => true));
	}

	$options = array();
	$findexternal_sql = 'SELECT `id`, `label` FROM `question`';
	$findexternal_rows = $app['db']->fetchAll($findexternal_sql, array());
	foreach($findexternal_rows as $findexternal_row){
	    $options[$findexternal_row['id']] = $findexternal_row['label'];
	}
	if(count($options) > 0){
	    $form = $form->add('question_id', 'choice', array(
	        'required' => true,
	        'choices' => $options,
	        'expanded' => false,
	        'constraints' => new Assert\Choice(array_keys($options))
	    ));
	}
	else{
	    $form = $form->add('question_id', 'text', array('required' => true));
	}


	$form = $form->add('time', 'text', array('required' => true));
	$form = $form->add('trained', 'text', array('required' => true));


    $form = $form->getForm();

    if("POST" == $app['request']->getMethod()){

        $form->handleRequest($app["request"]);

        if ($form->isValid()) {
            $data = $form->getData();

            $update_query = "UPDATE `knowledge` SET `time` = ?, `installation_id` = ?, `question_id` = ?, `trained` = ? WHERE `id` = ?";
            $app['db']->executeUpdate($update_query, array($data['time'], $data['installation_id'], $data['question_id'], $data['trained'], $id));            


            $app['session']->getFlashBag()->add(
                'success',
                array(
                    'message' => 'knowledge edited!',
                )
            );
            return $app->redirect($app['url_generator']->generate('knowledge_edit', array("id" => $id)));

        }
    }

    return $app['twig']->render('knowledge/edit.html.twig', array(
        "form" => $form->createView(),
        "id" => $id
    ));
        
})
->bind('knowledge_edit');



$app->match('/knowledge/delete/{id}', function ($id) use ($app) {

    $find_sql = "SELECT * FROM `knowledge` WHERE `id` = ?";
    $row_sql = $app['db']->fetchAssoc($find_sql, array($id));

    if($row_sql){
        $delete_query = "DELETE FROM `knowledge` WHERE `id` = ?";
        $app['db']->executeUpdate($delete_query, array($id));

        $app['session']->getFlashBag()->add(
            'success',
            array(
                'message' => 'knowledge deleted!',
            )
        );
    }
    else{
        $app['session']->getFlashBag()->add(
            'danger',
            array(
                'message' => 'Row not found!',
            )
        );  
    }

    return $app->redirect($app['url_generator']->generate('knowledge_list'));

})
->bind('knowledge_delete');






