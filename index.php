<?php
    require './Slim/Slim.php';
    include("db.php");
    \Slim\Slim::registerAutoloader();

    $slim = new \Slim\Slim();
    $slim->response->headers->set('Content-Type', 'application/json');
    $data = openDatabase('scouttesting');
    
    #get match schedule
    $slim->get('/matches', function ()  use($data, $slim){
		$matchSchedule = getMatchSchedule($data);
        if(is_array($matchSchedule))
        {
            $slim->response->setStatus(500);
            $matchSchedule = json_encode($matchSchedule);
        }
        echo $matchSchedule;
		$data->close();
    });

    #get match data
    $slim->get('/match/:id', function ($id) use($data, $slim) {
        $match = getScheduledMatch($data, $id);
        if(is_array($match))
        {
            $slim->response->setStatus(500);
            $match = json_encode($match);
        }
        echo $match;
		$data->close();
    });

    #get match data
    $slim->get('/last/:id', function ($id) use($data, $slim) {
        $last = getLastPlayed($data, $id);
        if(is_array($last))
        {
            $slim->response->setStatus(500);
            $last = json_encode($last);
        }
        echo $last;
		$data->close();
    });

    #get team info
    $slim->get('/team/:id', function ($id)  use($data){
		$teamData = getTeamData($data, $id);
        echo $teamData;
		$data->close();
    });

    #get team list(summary)
    $slim->get('/teams', function ()  use($data, $slim){
		$teamList = getTeamsView($data);
        if(is_array($teamList))
        {
            $slim->response->setStatus(500);
            $teamList = json_encode($teamList);
        }
        echo $teamList;
		$data->close();
    });

    //get one record, with team and match specified
    $slim->get('/team/:id/:match', function ($id, $match)  use($data, $slim){
		$teamMatchRecord = getTeamInMatch($data, $id, $match);
        if(is_array($teamMatchRecord))
        {
            $slim->response->setStatus(500);
            $teamMatchRecord = json_encode($teamMatchRecord);
        }
        echo $teamMatchRecord;
		$data->close();
    });

    #submit a form of data
    $slim->post('/form/:id', function ($id)  use($data, $slim){
		$result = submitData($data, $slim->request->getBody(), $id);
        if($result !== "Successfully Added!")
        {
            $slim->response->setStatus(500);
        }
        echo json_encode($result);
		$data->close();
    });

    #post photo (pit scouting)
    $slim->post('/photo', function ()  use($data, $slim){
        if (!isset($_FILES['photo'])) {
            $slim->response->setStatus(500);
            echo "No files Uploaded";
            return;
        }
        
        $error = $_FILES["photo"]["error"];
        if ($error == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["photo"]["tmp_name"];
            $name = $_POST['team'];
            $move = move_uploaded_file($_FILES["photo"]["tmp_name"], "robotPhotos/".$name);
            if(!$move)
            {
                $slim->response->setStatus(500);
                echo "Failed to move file!";
                return;
            }
        }        
        else
        {
            echo $error;
        }
        
        printf('%s <img src="%s"/><br/>', $name, "/rest/robotPhotos/".$name);

        $data->close();
    });

    $slim->run();
?>
