<?php
	class Player {
		public $playername;
		public $steamid;
		public $team;
		public $kills;
		public $assists;
		public $deaths;
		public $mvps;
		public $score;
	}

	class Team {
		public $score;
		public $players;
	}

	class Match {
		public $date;
		public $map;
		public $teams;
	}

	$db = new mysqli(host, user, password, database);

	if ($db->connect_errno){
		echo "Failed to connect to database: ".$db->connect_error;
	}else{
		if($res = $db->query("SELECT * FROM mr_results ORDER BY matchid DESC LIMIT 7")){
			$matches = Array();
			$i = 0;
			$lowestmatch = NULL;
			$highestm = NULL;
			while($row = $res->fetch_assoc()) {
				$lowestmatch = ($lowestmatch == NULL) ? $row["matchid"] : (($row["matchid"]<$lowestmatch) ? $row["matchid"] : $lowestmatch);
				$highestm = ($highestm == NULL) ? $row["matchid"] : (($row["matchid"]>$highestm) ? $row["matchid"] : $highestm);
				$matches[$i] = new Match();
				$matches[$i]->date = $row["date"];
				$matches[$i]->map = $row["map"];
				$matches[$i]->teams = Array();
				$matches[$i]->teams[0] = new Team();
				$matches[$i]->teams[1] = new Team();
				$matches[$i]->teams[0]->players = Array();
				$matches[$i]->teams[1]->players = Array();
				$matches[$i]->teams[0]->score = $row["team1_score"];
				$matches[$i]->teams[1]->score = $row["team2_score"];
				$i++;
			}
			$res->free_result();
		}
		if($res = $db->query("SELECT * FROM mr_players WHERE match_id >= ".$lowestmatch." ORDER BY score DESC, kills DESC, deaths ASC")){
			while($row = $res->fetch_assoc()){
				$player = new Player();
				$player->playername = $row["player_name"];
				$player->steamid = $row["steamid"];
				$player->team = $row["team"];
				$player->kills = $row["kills"];
				$player->assists = $row["assists"];
				$player->deaths = $row["deaths"];
				$player->mvps = $row["mvps"];
				$player->score = $row["score"];
				array_push($matches[$highestm - $row["match_id"]]->teams[$player->team - 1]->players, $player);
			}
		}
		$db->close();
	}

	header('Content-Type: application/json');
    echo json_encode($matches);
?>