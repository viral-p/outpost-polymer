<?php
    #creates and returns a database with the name $databaseName
    function openDatabase($databaseName)
    {
        $host="127.0.0.1";
        $user="root";
        $password="";
//        $user="team1_parth";
//        $password="anderson";
//        $host="mysqldb2.ehost-services.com";
        $port=3306;
        $socket="";

        $database = new mysqli($host, $user, $password, $databaseName, $port, $socket);
        if ($database->connect_errno) 
        {
            echo "Failed to connect to MySQL: " . $database->connect_error;
        }

        return $database;
    }

    #gets the records pertaining to a specified team from a specified table
    function getTeamData($database, $team)
    {
        $errRes = array("code" => null, "type" => null, "msg" => null, "object" => null);
        
        $sql = "select * from joined where TeamNumber = $team;";
        $ret = $database->query($sql);
        
        if(!$ret)
        {
            $errRes['code'] = $database->errno;
            $errRes['msg'] = $database->error;
            $errRes['type'] = "mySQL database query error";
            $errRes['object'] = "getTeamData method";
            return $errRes;
        } 
        else 
        {
            $teams = array();
            while ($row = $ret->fetch_assoc())
            {
                array_push($teams, $row);
            }
            return json_encode($teams);
        }
    }

    #gets all the teams from the team table
    function getTeamsView($database)
    {
        $errRes = array("code" => null, "type" => null, "msg" => null, "object" => null);
        $sql = "SELECT * FROM teams";
        $ret = $database->query($sql);
        
        if(!$ret)
        {
            $errRes['code'] = $database->errno;
            $errRes['msg'] = $database->error;
            $errRes['type'] = "mySQL database query error";
            $errRes['object'] = "getTeamsView method";
            return $errRes;
        } 
        else 
        {
            $teams = array();
            while ($row = $ret->fetch_assoc())
            {
                array_push($teams, $row);
            }
            return json_encode($teams);
        }
    }

    #gets all the teams from the team table
    function getLastPlayed($database, $station)
    {
        $errRes = array("code" => null, "type" => null, "msg" => null, "object" => null);
        $sql = "select MatchNumber from forms where station = '$station' order by MatchNumber desc limit 1;";
        $ret = $database->query($sql);
        
        if(!$ret)
        {
            $errRes['code'] = $database->errno;
            $errRes['msg'] = $database->error;
            $errRes['type'] = "mySQL database query error";
            $errRes['object'] = "getLastPlayed method";
            return $errRes;
        } 
        else 
        {
            $last = $ret->fetch_assoc();
            return json_encode($last);
        }
    }

    #gets the entire match schedule
    function getMatchSchedule($database)
    {
        $errRes = array("code" => null, "type" => null, "msg" => null, "object" => null);
        $sql = "SELECT * FROM matches";
        $ret = $database->query($sql);
        
        if(!$ret)
        {
            $errRes['code'] = $database->errno;
            $errRes['msg'] = $database->error;
            $errRes['type'] = "mySQL database query error";
            $errRes['object'] = "getMatchSchedule method";
            return $errRes;
        } 
        else 
        {
            $matches = array();
            while ($row = $ret->fetch_assoc())
            {
                array_push($matches, $row);
            }
            return json_encode($matches);
        }
    }

    #gets match data: red alliance robots, blue alliance robots, and surrogates
    function getScheduledMatch($database, $match)
    {
        $errRes = array("code" => null, "type" => null, "msg" => null, "object" => null);
        $sql = "SELECT * FROM matches where matches.MatchNumber = $match";
        $ret = $database->query($sql);
        
        if(!$ret)
        {
            $errRes['code'] = $database->errno;
            $errRes['msg'] = $database->error;
            $errRes['type'] = "mySQL database query error";
            $errRes['object'] = "getScheduledMatch method";
            return $errRes;
        }
        else
        {
            return json_encode($ret->fetch_assoc());
        }
    }

    #gets the team data for the given match (a row from forms table)
    function getTeamInMatch($database, $team, $match)
    {
        $errRes = array("code" => null, "type" => null, "msg" => null, "object" => null);
        $sql = "SELECT * FROM Forms JOIN Teleop, Autonomous WHERE Forms.FormNumber = autonomous.FormNumber = teleop.FormNumber AND TeamNumber = $team AND MatchNumber = $match;";
        $ret = $database->query($sql);
        
        if(!$ret)
        {
            $errRes['code'] = $database->errno;
            $errRes['msg'] = $database->error;
            $errRes['type'] = "mySQL database query error";
            $errRes['object'] = "getTeamInMatch method";
            return $errRes;
        }
        else
        {
            return json_encode($ret->fetch_assoc());
        }
    }

    #gets strat match
    function getStratMatch($database,$match)
    {
        $errRes = array("code" => null, "type" => null, "msg" => null, "object" => null);
        $schMatch = json_decode(getScheduledMatch($database, $match));
        if(!$schMatch)
        {
            return json_encode([]);
        }
        
        $sql = "select * from teams where teamnumber = $schMatch->Red1 or teamnumber = $schMatch->Red2 or teamnumber = $schMatch->Red3 or teamnumber = $schMatch->Blue1 or teamnumber = $schMatch->Blue2 or teamnumber = $schMatch->Blue3;";
        $ret = $database->query($sql);
        
        if(!$ret)
        {
            $errRes['code'] = $database->errno;
            $errRes['msg'] = $database->error;
            $errRes['type'] = "mySQL database query error";
            $errRes['object'] = "getStratMatch method";
            return $errRes;
        }
        else
        {
            $teams = array();
            while ($row = $ret->fetch_assoc())
            {
                array_push($teams, $row);
            }
            return json_encode($teams);
        }
    }
 
    function submitData($database, $submit, $id)
    {
        $formData = json_decode($submit, true);
        
        $formErr = writeToFormsTable($database, $formData, $id);
        $autoErr = writeToAutoTable($database, $formData, $id);
        $teleErr = writeToTeleopTable($database, $formData, $id);
        
        if($formErr['code'] !== null)
        {
            return $formErr;
        }
        else       
        {
            if($autoErr['code'] !== null)
            {
                return $autoErr;
            }
            else
            {
                if($teleErr['code'] !== null)
                {
                    return $teleErr;
                }
                else
                {
                    $joined = joinForms($database);
                    if($joined['code'] !== null)
                    {
                        $delete = deleteForms($database, $id);
                        if($delete['code'] !== null)
                        {
                            return $delete;
                        }
                        else
                        {
                            return $joined;
                        }
                    }
                    else
                    {
                        $calc = avg($database, $formData);
                        if(gettype($calc) !== "string")
                        {
                            $delete = deleteForms($database, $id);
                            if($delete['code'] !== null)
                            {
                                return $delete;
                            }
                            else
                            {
                                return $calc;
                            }
                        }
                        else
                        {
                            return $calc;
                        }
                    }
                }
            }
        }
    }

    function deleteForms($database, $formNum)
    {
        $errRes = array("code" => null, "type" => null, "msg" => null, "object" => null);
        
        $del = "delete autonomous, teleop, forms from autonomous, teleop, forms where autonomous.FormNumber = $id and teleop.FormNumber = $id and forms.FormNumber = $id;";
        $res = $database->query($del);
        
        if(!$res)
        {
            $errRes['code'] = $database->errno;
            $errRes['msg'] = $database->error;
            $errRes['type'] = "mySQL database query error";
            $errRes['object'] = "Deleting form(s) error";
            return $errRes;
        }
        return "true";
    }

    function writeToFormsTable($database, $formData, $id)
    {
        $errRes = array("code" => null, "type" => null, "msg" => null, "object" => null);
        
        $formNum = $id;
        $matchNum = $formData['MatchNumber'];
        $teamNum = $formData['TeamNumber'];
        $station = $formData['Station'];
        $penalties = $formData['Penalties'];
        $allianceScore = $formData['AllianceScore'];
        $opponentScore = $formData['OpponentScore'];
        $comments = $formData['Comments'];
        
        $insertForm = "replace INTO forms values($formNum, $matchNum, $teamNum, '$station', $penalties, $allianceScore, $opponentScore, '$comments')";
        $ret = $database->query($insertForm);
        
        if(!$ret)
        {
            $errRes['code'] = $database->errno;
            $errRes['msg'] = $database->error;
            $errRes['type'] = "mySQL database query error";
            $errRes['object'] = "submitForm method error";
        }
        return $errRes;
    }

    function writeToAutoTable($database, $formData, $id)
    {
        $errRes = array("code" => null, "type" => null, "msg" => null, "object" => null);
        
        $formNum = $id;
        $autoMobility = $formData['AutoMobility'];
        $autoBinsMoved = $formData['AutoBinsMoved'];
        $autoBinsScored = $formData['AutoBinsScored'];
        $autoTotesMoved = $formData['AutoTotesMoved'];
        $autoTotesStacked = $formData['AutoTotesStacked'];
        $autoTotesScored = $formData['AutoTotesScored'];
        $landfillTotesAcquired = $formData['LandfillTotesAcquired'];
        $stepTotesAcquired = $formData['StepTotesAcquired'];
        $stepBinsAcquired = $formData['StepBinsAcquired'];
        
        $insertForm = "replace INTO autonomous VALUES($formNum, $autoMobility, $autoBinsMoved, $autoBinsScored, $autoTotesMoved, $autoTotesStacked, $autoTotesScored, $landfillTotesAcquired, $stepTotesAcquired, $stepBinsAcquired)";
        $ret = $database->query($insertForm);
        
        if(!$ret)
        {
            $errRes['code'] = $database->errno;
            $errRes['msg'] = $database->error;
            $errRes['type'] = "mySQL database query error";
            $errRes['object'] = "submitAuto method error";
        }
        return $errRes;
    }

    function writeToTeleopTable($database, $formData, $id)
    {
        $errRes = array("code" => null, "type" => null, "msg" => null, "object" => null);
        
        $formNum = $id;
        $totesScored = implode(',', $formData['TotesScored']);
        $binsScored = implode(',', $formData['BinsScored']);
        $litterProcessed = $formData['LitterProcessed'];
        $litterCapped = $formData['LitterCapped'];
        $totesAcquiredLandfill = $formData['TotesAcquiredLandfill'];
        $totesAcquiredHumanLoad = $formData['TotesAcquiredHumanLoad'];
        $totesAcquiredStep = $formData['TotesAcquiredStep'];
        $binsAcquiredStep = $formData['BinsAcquiredStep'];
        $binsAcquiredZone = $formData['BinsAcquiredZone'];
        $upsideDownTotesAcquired = $formData['UpsideDownTotesAcquired'];
        $nonUprightBinsAcquired = $formData['NonUprightBinsAcquired'];
        
        $insertForm = "replace INTO teleop VALUES($formNum, '$totesScored', '$binsScored', $litterProcessed, $litterCapped, $totesAcquiredLandfill, $totesAcquiredHumanLoad, $totesAcquiredStep, $binsAcquiredStep, $binsAcquiredZone, $upsideDownTotesAcquired, $nonUprightBinsAcquired)";
        $ret = $database->query($insertForm);
        
        if(!$ret)
        {
            $errRes['code'] = $database->errno;
            $errRes['msg'] = $database->error;
            $errRes['type'] = "mySQL database query error";
            $errRes['object'] = "submitTeleop method error";
        }
        return $errRes;
    }

    // NOTE: this may be a cause for concern with concurrency
    function joinForms($database)
    {
        $errRes = array("code" => null, "type" => null, "msg" => null, "object" => null);
        
        $lock = "lock tables joined write, forms as form write, autonomous  as auto write, teleop as tele write, teams write;";
        $acq = $database->query($lock);
        if(!$acq)
        {
            $errRes['code'] = $database->errno;
            $errRes['msg'] = $database->error;
            $errRes['type'] = "mySQL database query error";
            $errRes['object'] = "Locking Joined table error";
            return $errRes;
        }
        
//        $dropJoined = "delete from joined;";
//        $drop = $database->query($dropJoined);
//        if(!$drop)
//        {
//            $errRes['code'] = $database->errno;
//            $errRes['msg'] = $database->error;
//            $errRes['type'] = "mySQL database query error";
//            $errRes['object'] = "Deleting Joined table error";
//            return $errRes;
//        }
//        else
//        {
            $joinForms = "replace into joined (select form.FormNumber, form.MatchNumber, form.TeamNumber, form.Penalties, form.AllianceScore, form.OpponentScore, form.Comments,
auto.AutoMobility, auto.AutoBinsMoved, auto.AutoBinsScored, auto.AutoTotesMoved, auto.AutoTotesStacked, auto.AutoTotesScored, auto.LandfillTotesAcquired, auto.StepTotesAcquired, auto.StepBinsAcquired,
tele.TotesScored, tele.BinsScored, tele.LitterProcessed, tele.LitterCapped, tele.TotesAcquiredLandfill, tele.TotesAcquiredHumanLoad, tele.TotesAcquiredStep, tele.BinsAcquiredStep, tele.BinsAcquiredZone, tele.UpsideDownTotesAcquired, tele.NonUprightBinsAcquired
from forms as form
join autonomous as auto on (form.FormNumber = auto.FormNumber)
join teleop as tele on (form.FormNumber = tele.FormNumber));";
            $ret = $database->query($joinForms);
            if(!$ret)
            {
                $errRes['code'] = $database->errno;
                $errRes['msg'] = $database->error;
                $errRes['type'] = "mySQL database query error";
                $errRes['object'] = "Joining froms error";
                return $errRes;
            }
            
            $unlock = "unlock tables;";
            $acq = $database->query($unlock);
            if(!$acq)
            {
                $errRes['code'] = $database->errno;
                $errRes['msg'] = $database->error;
                $errRes['type'] = "mySQL database query error";
                $errRes['object'] = "Unlocking Joined table error";
                return $errRes;
            }
            
            return $ret;
//        }
    }

    function avg($database, $formData)
    {
        $errRes = array("code" => null, "type" => null, "msg" => null, "object" => null);
        $totes = array(0, 0, 0, 0, 0, 0);
        $bins = array(0, 0, 0, 0, 0, 0);
        $teamNum = $formData['TeamNumber'];
        
        $teamData = "select * from joined where TeamNumber = $teamNum;";
        $matches = $database->query($teamData);
        if(!$matches)
        {
            $errRes['code'] = $database->errno;
            $errRes['msg'] = $database->error;
            $errRes['type'] = "mySQL database query error";
            $errRes['object'] = "Select from joined table to average error";
            return $errRes;
        }
        
        for($i = 0; $row = $matches->fetch_assoc(); $i++)
        {
            foreach(explode(',', $row['TotesScored']) as $k => $val)
            {
                $totes[$k] += $val;
            }
            foreach(explode(',', $row['BinsScored']) as $k => $val)
            {
                $bins[$k] += $val;
            }
        }
        foreach($totes as $j => $levels)
        {
            $totes[$j] = number_format($totes[$j] / $i, 2);
            $bins[$j] = number_format($bins[$j] / $i, 2);
        }
        $totes = implode($totes, ',');
        $bins = implode($bins, ',');
        
        $updateTeam = "update teams
set AverageOver = (select count(*) from joined where TeamNumber = $teamNum),
Penalties = (select avg(Penalties) from joined where TeamNumber = $teamNum),
AllianceScore = (select avg(AllianceScore) from joined where TeamNumber = $teamNum),
OpponentScore = (select avg(OpponentScore) from joined where TeamNumber = $teamNum),
AutoMobility = (select avg(AutoMobility) from joined where TeamNumber = $teamNum),
AutoBinsMoved = (select avg(AutoBinsMoved) from joined where TeamNumber = $teamNum),
AutoBinsScored = (select avg(AutoBinsScored) from joined where TeamNumber = $teamNum),
AutoTotesMoved = (select avg(AutoTotesMoved) from joined where TeamNumber = $teamNum),
AutoTotesStacked = (select avg(AutoTotesStacked) from joined where TeamNumber = $teamNum),
AutoTotesScored = (select avg(AutoTotesScored) from joined where TeamNumber = $teamNum),
LandfillTotesAcquired = (select avg(LandfillTotesAcquired) from joined where TeamNumber = $teamNum),
StepTotesAcquired = (select avg(StepTotesAcquired) from joined where TeamNumber = $teamNum),
StepBinsAcquired = (select avg(StepBinsAcquired) from joined where TeamNumber = $teamNum),
TotesScored = '$totes',
BinsScored = '$bins',
TotesAcquiredLandfill = (select avg(TotesAcquiredLandfill) from joined where TeamNumber = $teamNum),
TotesAcquiredHumanLoad = (select avg(TotesAcquiredHumanLoad) from joined where TeamNumber = $teamNum),
TotesAcquiredStep = (select avg(TotesAcquiredStep) from joined where TeamNumber = $teamNum),
BinsAcquiredStep = (select avg(BinsAcquiredStep) from joined where TeamNumber = $teamNum),
BinsAcquiredZone = (select avg(BinsAcquiredZone) from joined where TeamNumber = $teamNum),
UpsideDownTotesAcquired = (select avg(UpsideDownTotesAcquired) from joined where TeamNumber = $teamNum),
NonUprightBinsAcquired = (select avg(NonUprightBinsAcquired) from joined where TeamNumber = $teamNum),
LitterProcessed = (select avg(LitterProcessed) from joined where TeamNumber = $teamNum),
LitterCapped = (select avg(LitterCapped) from joined where TeamNumber = $teamNum)
where TeamNumber = $teamNum;";
        $ret = $database->query($updateTeam);
        if(!$ret)
        {
            $errRes['code'] = $database->errno;
            $errRes['msg'] = $database->error;
            $errRes['type'] = "mySQL database query error";
            $errRes['object'] = "Updating team summary error";
            return $errRes;
        }
        
        return "Successfully Added!";
    }
?>