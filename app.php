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
});

$app->post('/syncdata', function () use ($app) {
    $json_data = $app->request->getJsonRawBody();
    $id_lahan = $json_data->id_lahan;

    $phql = "SELECT latitude, longitude FROM Tempslotlahanparkir WHERE id_lahan = :id_lahan:";
    $arrayLatLng = $app->modelsManager->executeQuery($phql, array("id_lahan"=>$id_lahan));

    $response = new Response();

    foreach($arrayLatLng as $latLng){
        $phql = "INSERT INTO Slotlahanparkir (id_lahan, status, latitude, longitude) values (:id_lahan:, :status:, :latitude:, :longitude:)";
        $status = $app->modelsManager->executeQuery($phql, array(
            "id_lahan"=>$id_lahan,
            "status"=>"FREE",
            "latitude"=>$latLng["latitude"],
            "longitude"=>$latLng["longitude"]
        ));
    }
    if($status->success() == false){
        $json_response = array("error"=>true,"error_msg"=>"Error in copying temp data to main data");
        $response->setJsonContent($json_response);
    }
    else{
        $X = $Y = $Z = 0;
        $totalWeight = count($arrayLatLng);
        foreach($arrayLatLng as $latLng){
            $radLat = deg2rad($latLng["latitude"]);
            $radLng = deg2rad($latLng["longitude"]);

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

        $phql = "UPDATE Lahanparkir SET latitude=:midLat:, longitude=:midLng:, max_kapasitas_mobil=:max_kapasitas_mobil: WHERE id_lahan=:id_lahan:";
        $status = $app->modelsManager->executeQuery($phql, array(
            "midLat" => $midLat,
            "midLng" => $midLng,
            "max_kapasitas_mobil" => $totalWeight,
            "id_lahan" => $id_lahan
        ));
        if($status->success() == false){
            $json_response = array("error"=>true,"error_msg"=>"Error calibrating the mid point");
            $response->setJsonContent($json_response);
        }
        else{
            $json_response = array("error"=>false,"error_msg"=>"");
            $response->setJsonContent($json_response);
        }
    }
    return $response;
});

$app->post('/data/latlng2', function () use ($app) {
    $json_data = $app->request->getJsonRawBody();
    $id_lahan = $json_data->id_lahan;
    $lat1 = $json_data->fromLatitude;
    $lng1 = $json_data->fromLongitude;
    $lat2 = $json_data->toLatitude;
    $lng2 = $json_data->toLongitude;
    $numIntermediatePoint = $json_data->numPoints;

    $distance = getDistance($lat1, $lng1, $lat2, $lng2);
    $distancePoint = $distance/($numIntermediatePoint-1);
    $interval=$distancePoint;

    $initialBearing = getBearing($lat1, $lng1, $lat2, $lng2);

    $response = new Response();

    $array_data[] = array("latitude"=>$lat1,"longitude"=>$lng1);

    if($numIntermediatePoint>2){
        for($i=0; $i<$numIntermediatePoint-1; $i++){
            $array_data[] = getDestinationPoint($lat1, $lng1, $initialBearing, $interval);
            $interval = $interval+$distancePoint;
        }

        foreach($array_data as $data){
            $phql = "INSERT INTO Tempslotlahanparkir (id_lahan, latitude, longitude) values(:id_lahan:, :latitude:, :longitude:)";
            $status = $app->modelsManager->executeQuery($phql, array(
                "id_lahan"=>$id_lahan,
                "latitude"=>$data["latitude"],
                "longitude"=>$data["longitude"]
            ));
        }
        if($status->success()==true){
            $response->setJsonContent(array(
                'error'=>false,
                'error_msg'=>''
            ));
        }
        else{
            $response->setJsonContent(array(
                'error'=>true,
                'error_msg'=>'Error adding in database'
            ));
        }
    }
    else{
        if($numIntermediatePoint==2){
            $response->setJsonContent(array(
                'error'=>false,
                'error_msg'=>'Warning : No intermediate point'
            ));
        }
        else{
            $response->setJsonContent(array(
                    'error'=>true,
                    'error_msg'=>'minimal points is 2'
                )
            );
        }
    }

    return $response;
});

/**
 * Not found handler
 */
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo $app['view']->render('404');
});

function getBearing($lat1, $lng1, $lat2, $lng2){
    $phi1 = deg2rad($lat1);
    $phi2 = deg2rad($lat2);
    $lambda1 = deg2rad($lng1);
    $lambda2 = deg2rad($lng2);

    $y = sin($lambda2-$lambda1) * cos($phi2);
    $x = ( cos($phi1) * sin($phi2) ) -
        ( sin($phi1) * cos($phi2) * cos($lambda2-$lambda1));

    $bearing = atan2($y,$x);
    $bearingDeg = rad2deg($bearing);

    return $bearingDeg;
}

function getDestinationPoint($lat, $lng, $initBearing, $distance){
    $phi1 = deg2rad($lat);
    $lambda1 = deg2rad($lng);

    $bearing=deg2rad($initBearing);
    $radius = 6371000.00; // in meters
    $d = $distance/$radius;

    $phi2 = asin( (sin($phi1) * cos($d)) + (cos($phi1) * sin($d) * cos($bearing)));

    $y = sin($bearing) * sin($d) * cos($phi1);
    $x = cos($d) - sin($phi1) * sin($phi2);
    $lambda2 = $lambda1 + atan2($y,$x);

    $lat2 = rad2deg($phi2);
    $lng2 = rad2deg($lambda2);

    $lng2 = fmod($lng2+540, 360) - 180;

    $latlng = array('latitude'=>$lat2,'longitude'=>$lng2);

    return $latlng;
}

function getDistance2($lat1, $lng1, $lat2, $lng2){
    $radius = 6371000; // in meters

    $phi1 = deg2rad($lat1);
    $phi2 = deg2rad($lat2);
    $delta_phi = deg2rad($lat2-$lat1);
    $delta_lambda = deg2rad($lng2-$lng1);

    $a = (sin($delta_phi/2) * sin($delta_phi/2))
        + (cos($phi1) * cos($phi2) * sin($delta_lambda/2) * sin($delta_lambda/2));

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));

    $distance = $radius * $c;

    return $distance;
}

function getDistance($lat1, $lng1, $lat2, $lng2){
    $R = 6371000;

    $phi1 = deg2rad($lat1);
    $phi2 = deg2rad($lat2);
    $lambda1 = deg2rad($lng1);
    $lambda2 = deg2rad($lng2);

    $deltaPhi = $phi2 - $phi1;
    $deltaLambda = $lambda2 - $lambda1;

    $a = (sin($deltaPhi/2) * sin($deltaPhi/2))
        + (cos($phi1) * cos($phi2) * sin($deltaLambda/2) * sin($deltaLambda/2));

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));

    $d = $R * $c;

    return $d;
}