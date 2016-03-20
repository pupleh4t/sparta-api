<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

/**
 * Add your routes here
 */
use Phalcon\Http\Response;
require_once 'funct/hashCls.php';

$app->get('/', function () use ($app) {
    echo $app['view']->render('index');
});

/**
 * Register Router
 */
$app->post('/user/register', function () use ($app){
    /**
     * Variables Initialization
     */
    $json_data = $app->request->getJsonRawBody();
    $hasher = new hashCls();
    $name = $json_data->name;
    $email = $json_data->email;
    $password = $json_data->password;
    $uuid = uniqid('',true);
    $hash = $hasher->hashSSHA($json_data->password);
    $encrypted_password = $hash["encrypted"];
    $salt = $hash["salt"];

    /**
     * PHQL for Checking if user is existed or not
     */
    $phqlUserExisted = "SELECT email from Usercredential WHERE email = :email:";
    $userExisted = $app->modelsManager->executeQuery($phqlUserExisted, array(
        'email' =>  $email
    ));

    /**
     * Creating response
     */
    $response = new Response();

    /**
     * Check if user has been on database or not
     */
    if($userExisted == false){
        /**
         * User already exists
         */
        $response->setStatusCode(409, 'Conflicted');
        $response->setJsonContent(array(
            'error' => true ,
            'error_msg' => $email
        ));
    }else{
        /**
         * User is not on database, So it valid to be created
         * Followed by insertion a user into database
         */
        $phql = "INSERT INTO Usercredential (unique_id, name, email, encrypted_password, salt, created_at) VALUES (:uuid:, :name:, :email:, :encrypted_password:, :salt:, NOW())";
        $status = $app->modelsManager->executeQuery($phql, array(
            'uuid' => $uuid,
            'name' => $name,
            'email'=> $email,
            'encrypted_password'=> $encrypted_password,
            'salt'=> $salt
        ));

        /**
         * If User registration succeed
         */
        if($status->success() == true)
        {
            $response->setStatusCode(201, "Created");
            $response->setJsonContent(
                array(
                    'error' => false,
                    'uid' => $status->getModel()->unique_id,
                    'user' => array(
                        'name'  => $status->getModel()->name,
                        'email' => $status->getModel()->email,
                        'created_at' => $status->getModel()->created_at
                    )
                )
            );
        } else {
            /**
             * If User regitration failed
             */
            $response->setStatusCode(409, "Conflict");

            /*$errors = array();
            foreach ($status->getMessages as $message) {
                $errors[] = $message->getMessage();
            }
            */
            $response->setJsonContent(
                array(
                    'error' => true,
                    'error_msg' => 'User Registration failed'
                )
            );
        }
        }

    return $response;
    });

/**
 * Login Router
 */
$app->post('/user/login', function () use ($app) {
    $json_data = $app->request->getJsonRawBody();
    $email = $json_data->email;
    $password = $json_data->password;
    $hasher = new hashCls();
    $response = new Response();

    /**
     * Searching record usercredential based on email
     */
    $phql = "SELECT * FROM Usercredential WHERE email = :email:";
    $user = $app->modelsManager->executeQuery($phql,array(
        'email' => $email
    ))->getFirst();

    if($user == null){
        $response->setJsonContent(array(
            'error' => true,
            'error_msg' => 'Combination Username or Password is Incorrect'
        ));

    }else{
        /**
         * Check if post parameter data meets a usercredential record
         */
        if($user->encrypted_password == $hasher->checkhashSSHA($user->salt, $password) )
        {
            $response->setJsonContent(array(
                'error'     => false,
                'uid'       => $user->unique_id,
                'user'      => array(
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'created_at'=> $user->created_at,
                    'updated_at'=> $user->updated_at
                )
            ));
        }
        else
        {
            $response->setJsonContent(array(
                'error' => true,
                'error_msg' => 'Combination Username or Password is Incorrect'
            ));
        }
    }
    return $response;
});

$app->post('/data/slot', function () use ($app) {
    $json_data = $app->request->getJsonRawBody();
    $id_lahan = $json_data->id_lahan;

    $phql = "SELECT * FROM Slotlahanparkir WHERE id_lahan = :id_lahan:";
    $datas = $app->modelsManager->executeQuery($phql, array('id_lahan' => $id_lahan));

    $data = array();
    foreach ($datas as $slot) {
        $data[] = array(
            'id_slot'   => $slot->id_slot,
            'latitude' => $slot->latitude,
            'longitude' => $slot->longitude,
            'status' => $slot->status
        );
    }
    $json_data = array('slot_data'=>$data);
    
    echo json_encode($json_data);
});

$app->post('/data/latlng', function () use ($app) {
    $json_data = $app->request->getJsonRawBody();
    $lat = $json_data->latitude;
    $lng = $json_data->longitude;

    $phql = "INSERT INTO TempLatlng (latitude, longitude) values (:latitude:, :longitude:)";
    $datas = $app->modelsManager->executeQuery($phql, array(
        'latitude' => $lat,
        'longitude' => $lng
    ));

    $response = new Response();
    if($datas->success() == true){
        $response->setJsonContent(
            array(
                "status"=>true
            )
        );
    }
    else{
        $response->setJsonContent(
            array(
                "status"=>false
            )
        );
    }

    return $response;
});

$app->get('/data/latlng', function () use ($app) {
    $phql = "SELECT * FROM TempLatlng";
    $datas = $app->modelsManager->executeQuery($phql);

    $response = new Response();
    if($datas->success() == true){
        $response->setJsonContent(
            array(
                "status"=>true
            )
        );
    }
    else{
        $response->setJsonContent(
            array(
                "status"=>false
            )
        );
    }

    return $response;
});

$app->post('/data/area', function () use ($app) {
    $json_data = $app->request->getJsonRawBody();
    $id_lahan = $json_data->id_lahan;
    $phql = "SELECT latitude, longitude FROM Arealahanparkir WHERE id_lahan = :id_lahan:";
    $datas = $app->modelsManager->executeQuery($phql, array("id_lahan"=>$id_lahan));

    $response = new Response();
    foreach ($datas as $marker) {
        $data[] = array(
            'latitude' => $marker->latitude,
            'longitude' => $marker->longitude
        );
    }

    $json_data=array('id_lahan'=>$id_lahan,'area_data'=>$data);
    $response->setJsonContent($json_data);

    return $response;
});

$app->post('/update', function () use ($app) {
    $json_data = $app->request->getJsonRawBody();
    $id_lahan = $json_data->id_lahan;

    $phql = "SELECT latitude, longitude FROM Slotlahanparkir WHERE id_lahan = :id_lahan:";
    $datas = $app->modelsManager->executeQuery($phql, array("id_lahan"=>$id_lahan));

    $response = new Response();

    if (count($datas)> 0){
        $totalWeight = count($datas);
        var_dump($totalWeight);
        $X = $Y = $Z = 0;

        foreach($datas as $marker){
            $radLat = deg2rad($marker->latitude);
            $radLng = deg2rad($marker->longitude);

            $x = cos($radLat)*cos($radLng);
            $y = cos($radLat)*sin($radLng);
            $z = sin($radLat);

            $X = $X + $x;
            $Y = $Y + $y;
            $Z = $Z + $z;
        }

        $X = $X/$totalWeight;
        $Y = $Y/$totalWeight;
        $Z = $Z/$totalWeight;

        $radMidLat = atan2($Y,$X);
        $hyp = sqrt(($X*$X)+($Y*$Y));
        $radMidLng = atan2($Z, $hyp);

        $midLat = rad2deg($radMidLat);
        $midLng = rad2deg($radMidLng);

        $phql2 = "UPDATE Lahanparkir SET latitude=:midLat:, longitude=:midLng:, max_kapasitas_mobil=:max_kapasitas_mobil: WHERE id_lahan=:id_lahan:";
        $datas2 = $app->modelsManager->executeQuery($phql2, array(
            "midLat" => $midLat,
            "midLng" => $midLng,
            "max_kapasitas_mobil" => $totalWeight,
            "id_lahan" => $id_lahan
        ));

        if($datas2->success() == true){
            $json_response = array(
                'error' => false,
                'error_msg' => "Update data successfully"
            );
            $response->setJsonContent($json_response);
        }
        else{
            $json_response = array(
                'error' => true,
                'error_msg' => "failed to update the new LatLng Coordinate"
            );
            $response->setJsonContent($json_response);
        }
    }else{
        $json_response = array(
            'error' => true,
            'error_msg' => 'No data for desired location'
        );
        $response->setJsonContent($json_response);
    }

    return $response;
//    if($datas->success() == true){
        /*$totalWeight = count($datas);
        var_dump($totalWeight);
        $X = $Y = $Z = 0;*/

        /*foreach($datas as $marker){
            $radLat = deg2rad($marker->latitude);
            $radLng = deg2rad($marker->longitude);

            $x = cos($radLat)*cos($radLng);
            $y = cos($radLat)*sin($radLng);
            $z = sin($radLat);

            $X = $X + $x;
            $Y = $Y + $y;
            $Z = $Z + $z;
        }

        $X = $X/$totalWeight;
        $Y = $Y/$totalWeight;
        $Z = $Z/$totalWeight;

        $radMidLat = atan2($Y,$X);
        $hyp = sqrt(($X*$X)+($Y+$Y));
        $radMidLng = atan2($Z, $hyp);

        $midLat = rad2deg($radMidLat);
        $midLng = rad2deg($radMidLng);*/

        /*$phql2 = "UPDATE Lahanparkir SET latitude=:midLat:, longitude=:midLng:, max_kapasitas_mobil=:max_kapasitas_mobil: WHERE id_lahan=:id_lahan:";
        $datas2 = $app->modelsManager->executeQuery($phql2, array(
            "midLat" => $midLat,
            "midLng" => $midLng,
            "max_kapasitas_mobil" => $totalWeight,
            "id_lahan" => $id_lahan
        ));

        if($datas2->success() == true){
            $json_data = array(
                'error' => false,
                'error_msg' => "Update data successfully"
            );
        }
        else{
            $json_data = array(
                'error' => true,
                'error_msg' => "failed to update the new LatLng Coordinate"
            );
            $response->setJsonContent($json_data);
        }*/

//        $json_data = array(
//            'error' => false,
//            'error_msg' => "id_lahan hasn't registered yet"
//        );
//        $response->setJsonContent($json_data);
//    }
//    else{
//        $json_data = array(
//            'error' => true,
//            'error_msg' => "id_lahan hasn't registered yet"
//            );
//        $response->setJsonContent($json_data);
//    }
//
//    return $response;
});

$app->post('/data/area/nearest', function () use ($app) {
    $json_data = $app->request->getJsonRawBody();
    $lat = $json_data->latitude;
    $lng = $json_data->longitude;


});

/**
 * Not found handler
 */
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo $app['view']->render('404');
});
