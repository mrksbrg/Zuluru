<?php
/**
 * Base class for league-specific functionality.  This class defines default
 * no-op functions for all operations that leagues might need to do, as well
 * as providing some common utility functions that derived classes need.
 */

class LeagueTypeComponent extends Object
{
	/**
	 * Define the element to use for rendering various views
	 */
	var $render_element = 'rounds';

	function __construct(&$controller) {
		$this->_controller =& $controller;
	}

	/**
	 * Add any league-type-specific options to the menu.
	 * By default, there are no extra menu options.
	 *
	 * @param mixed $league Array containing the league data
	 * @param mixed $is_coordinator Indication of whether the user is a coordinator of this league
	 *
	 */
	function addMenuItems($league, $is_coordinator = false)	{
	}

	/**
	 * Sort the provided teams according to league-specific criteria.
	 * This default function is usually going to be good enough, but we put it
	 * here instead of having other code call usort directly, just in case.
	 *
	 * @param mixed $league League to sort (teams are in ['Team'] key)
	 *
	 */
	function sort(&$league) {
		$this->presort ($league);
		usort ($league['Team'], array($this, 'compareTeams'));
	}

	/**
	 * Do any calculations that will make the comparisons more efficient, such
	 * as determining wins, losses, spirit, etc.
	 * 
	 * @param mixed $league League to perform calculations on
	 *
	 */
	function presort(&$league) {
		if (array_key_exists ('Game', $league)) {
			$results = array();
			foreach ($league['Game'] as $game) {
				if (Game::_is_finalized($game)) {
					// Different read methods create arrays in different formats
					if (array_key_exists ('Game', $game)) {
						$result = $game['Game'];
					} else {
						$result = $game;
					}

					$this->addGameResult ($results, $result['home_team'], $result['away_team'],
							$result['round'], $result['home_score'], $result['away_score'],
							Game::_get_spirit_entry ($game, $result['home_team']),
							$result['status'] == 'home_default');
					$this->addGameResult ($results, $result['away_team'], $result['home_team'],
							$result['round'], $result['away_score'], $result['home_score'],
							Game::_get_spirit_entry ($game, $result['away_team']),
							$result['status'] == 'away_default');
				}
			}

			foreach ($league['Team'] as $key => $team) {
				if (array_key_exists ($team['id'], $results)) {
					$league['Team'][$key]['results'] = $results[$team['id']];
				} else {
					$league['Team'][$key]['results'] = array('W' => 0, 'L' => 0, 'T' => 0, 'def' => 0, 'pts' => 0, 'games' => 0,
							'gf' => 0, 'ga' => 0, 'str' => 0, 'str_type' => '', 'spirit' => 0,
							'rounds' => array(), 'vs' => array(), 'vspm' => array());
				}
			}
		}
	}

	function addGameResult (&$results, $team, $opp, $round, $score_for, $score_against, $spirit_for, $default) {
		// What type of result was this?
		if ($score_for > $score_against) {
			$type = 'W';
			// TODO: points for wins, losses and ties configurable?
			$points = 2;
		} else if ($score_for < $score_against) {
			$type = 'L';
			$points = 0;
		} else {
			$type = 'T';
			$points = 1;
		}

		// Make sure the team record exists in the results
		if (! array_key_exists ($team, $results)) {
			$results[$team] = array('W' => 0, 'L' => 0, 'T' => 0, 'def' => 0, 'pts' => 0, 'games' => 0,
									'gf' => 0, 'ga' => 0, 'str' => 0, 'str_type' => '', 'spirit' => 0,
									'rounds' => array(), 'vs' => array(), 'vspm' => array());
		}

		// Make sure a record exists for the round in the results
		// Some league types don't use rounds, but there's no real harm in calculating this
		if (! array_key_exists ($round, $results[$team]['rounds'])) {
			$results[$team]['rounds'][$round] = array('W' => 0, 'L' => 0, 'T' => 0, 'def' => 0, 'gf' => 0, 'ga' => 0);
		}

		// Make sure a record exists for the opponent in the vs arrays
		if (! array_key_exists ($opp, $results[$team]['vs'])) {
			$results[$team]['vs'][$opp] = 0;
			$results[$team]['vspm'][$opp] = 0;
		}

		if ($default) {
			++ $results[$team]['def'];
			++ $results[$team]['rounds'][$round]['def'];
			-- $points;
		}

		// Add the current game
		++ $results[$team]['games'];
		++ $results[$team][$type];
		++ $results[$team]['rounds'][$round][$type];
		$results[$team]['pts'] += $points;
		$results[$team]['gf'] += $score_for;
		$results[$team]['rounds'][$round]['gf'] += $score_for;
		$results[$team]['ga'] += $score_against;
		$results[$team]['rounds'][$round]['ga'] += $score_against;
		// TODO: drop high and low spirit?
		if (is_array ($spirit_for) && array_key_exists ('entered_sotg', $spirit_for)) {
			$results[$team]['spirit'] += $spirit_for['entered_sotg'];
		}
		$results[$team]['vs'][$opp] += $points;
		$results[$team]['vspm'][$opp] += $score_for - $score_against;

		// Add to the current streak, or reset it
		if ($type == $results[$team]['str_type']) {
			++ $results[$team]['str'];
		} else {
			$results[$team]['str_type'] = $type;
			$results[$team]['str'] = 1;
		}
	}

	/**
	 * By default, we just sort by name.
	 */
	static function compareTeams($a, $b) {
		return (strtolower ($a['name']) > strtolower ($b['name']));
	}

	/**
	 * Generate a list of extra league-type-specific edit/display fields, as
	 * field => details pairs.  Details are arrays with keys like label (mandatory)
	 * and any options to be passed to the html->input call.
	 * Titles are in English, and will be translated in the view.
	 * By default, there are no extra fields.
	 *
	 * @return mixed An array containing the extra fields
	 *
	 */
	function schedulingFields($is_admin, $is_coordinator) {
		return array();
	}

	/**
	 * Return entries for validation of any league-type-specific edit fields.
	 *
	 * @return mixed An array containing items to be added to the validation array.
	 *
	 */
	function schedulingFieldsValidation() {
		return array();
	}

	/**
	 * Returns the list of options for scheduling games in this type of league.
	 *
	 * @return mixed An array containing the list of scheduling options.
	 */
	function scheduleOptions($num_teams) {
		return array();
	}

	/**
	 * Get the description of a scheduling type.
	 *
	 * @param mixed $type The scheduling type to return the description of
	 * @param mixed $teams The number of teams to include in the description, or false to return a short description
	 * @return mixed The description
	 *
	 */
	function scheduleDescription($type, $teams = false) {
		$types = $this->scheduleOptions($teams);
		$desc = $types[$type];
		if ($teams === false) {
			$pos = strpos ($desc, '(');
			if ($pos !== false) {
				$desc = substr ($desc, 0, $pos);
			}
			$desc = trim ($desc);
		}
		return $desc;
	}

	/**
	 * Return the requirements of a particular scheduling type.  This is
	 * just a default stub, overloaded by specific algorithms.
	 *
	 * @param mixed $num_teams The number of teams to schedule for
	 * @param mixed $type The schedule type
	 * @return mixed An array with the number of days and number of fields needed each day
	 *
	 */
	function scheduleRequirements($type, $num_teams) {
		return array(0, 0);
	}

	/**
	 * Load everything required for scheduling.
	 */
	function startSchedule($league_id, $exclude_teams, $start_date) {
		$this->games = array();

		$this->_controller->League->contain (array (
			'Team' => array(
				'order' => 'Team.name',
				'conditions' => array('NOT' => array('id' => $exclude_teams)),
			),
			'Game' => array(
				'GameSlot',
			),
			'LeagueGameslotAvailability' => array(
				'GameSlot' => array(
					// This will still return all of the Availability records, but many will have
					// empty GameSlot arrays, so Set::Extract calls won't match and they're ignored
					// TODO: Can a better query improve the efficiency of this?
					'conditions' => array(
						'game_date >=' => $start_date,
						'game_id' => null,
					),
				),
			),
		));
		$this->league = $this->_controller->League->read(null, $league_id);
		if ($this->league === false) {
			$this->_controller->Session->setFlash(__('Invalid league', true));
			return false;
		}

		// Go through all the games and count the number of home and away games for each team
		$this->home_games = $this->away_games = array();
		foreach ($this->league['Game'] as $game) {
			if (!array_key_exists ($game['home_team'], $this->home_games)) {
				$this->home_games[$game['home_team']] = 1;
			} else {
				++ $this->home_games[$game['home_team']];
			}

			if (!array_key_exists ($game['away_team'], $this->away_games)) {
				$this->away_games[$game['away_team']] = 1;
			} else {
				++ $this->away_games[$game['away_team']];
			}
		}

		return true;
	}

	function finishSchedule($league_id, $publish) {
		if (empty ($this->games)) {
			return false;
		}

		// Add the publish flag and league id to every game
		for ($i = 0; $i < count ($this->games); ++ $i) {
			$this->games[$i]['league_id'] = $league_id;
			$this->games[$i]['round'] = ($this->league['League']['current_round'] === null ? 0 : $this->league['League']['current_round']);
			$this->games[$i]['published'] = $publish;
		}

		// Check that chosen game slots didn't somehow get allocated elsewhere in the meantime
		$slots = Set::extract ('/GameSlot/id', $this->games);
		$this->_controller->League->Game->GameSlot->recursive = -1;
		$taken = $this->_controller->League->Game->GameSlot->find('all', array('conditions' => array(
				'id' => $slots,
				'game_id !=' => null,
		)));
		if (!empty ($taken)) {
			$this->_controller->Session->setFlash(__('A game slot chosen for this schedule has been allocated elsewhere in the interim. Please try again.', true));
			return false;
		}

		// saveAll doesn't save GameSlot records here (the hasOne relation
		// indicates to Cake that slots are supposed to be created for games,
		// rather than being created ahead of time and assigned to games).
		// So, we replicate the important bits of saveAll here.
		$db =& ConnectionManager::getDataSource($this->_controller->League->Game->useDbConfig);
		$db->begin($this->_controller->League->Game);
		foreach ($this->games as $game) {
			$this->_controller->League->Game->create();
			$success = $this->_controller->League->Game->save($game);
			if ($success) {
				$game['GameSlot']['game_id'] = $this->_controller->League->Game->id;
				$success = $this->_controller->League->Game->GameSlot->save($game['GameSlot']);
			}

			if (!$success) {
				$db->rollback($this->_controller->League->Game);
				return false;
			}
		}

		return ($db->commit($this->_controller->League->Game) !== false);
	}

	/**
	 * Create a single game in this league
	 */
	function createEmptyGame($date) {
		$num_teams = count($this->league['Team']);

		if ($num_teams < 2) {
			$this->_controller->Session->setFlash(__('Must have two teams', true));
			return false;
		}

		// TODO: 'GameSlot' can't be the first key, or else Model::set uses it as the
		// parameter to getAssociated and the return value isn't null. Report as a bug
		// in CakePHP?
		$this->games[] = array(
			'home_team' => null,
			'away_team' => null,
			'GameSlot' => array(
				'id' => $this->selectRandomGameslot($date),
			),
		);

		return true;
	}
	
	/**
	 * Schedule one set of games, using weighted field assignment
	 *
	 * @param mixed $date The date of the games
	 * @param mixed $teams List of teams, sorted into pairs by matchup
	 * @return boolean indication of success
	 *
	 */
	function assignFields($date, $teams) {
		// We build a temporary array of games, and add them to the completed list when they're ready
		$games = array();

		// Iterate over teams array pairwise and create games with balanced home/away
		for($team_idx = 0; $team_idx < count($teams); $team_idx += 2) {
			$games[] = $this->addTeamsBalanced($teams[$team_idx], $teams[$team_idx + 1]);
		}

		// Iterate over all newly-created games, and assign fields based on region preference.
		if (!$this->assignFieldsByPreferences($date, $games)) {
			return false;
		}

		return true;
	}

	/**
	 * Add two opponents to a game, attempting to balance the number of home
	 * and away games
	 */
	function addTeamsBalanced($a, $b) {
		$a_ratio = $this->homeAwayRatio($a['id']);
		$b_ratio = $this->homeAwayRatio($b['id']);

		// team with lowest ratio (fewer home games) gets to be home.
		if ($a_ratio < $b_ratio) {
			$home = $a;
			$away = $b;
		} elseif ($a_ratio > $b_ratio) {
			$home = $b;
			$away = $a;
		} else {
			// equal ratios... choose randomly.
			if (rand(0,1) > 0) {
				$home = $a;
				$away = $b;
			} else {
				$home = $b;
				$away = $a;
			}
		}

		if (!array_key_exists ($home['id'], $this->home_games)) {
			$this->home_games[$home['id']] = 0;
		}
		if (!array_key_exists ($away['id'], $this->away_games)) {
			$this->away_games[$away['id']] = 0;
		}

		++ $this->home_games[$home['id']];
		++ $this->away_games[$away['id']];

		return array(
			'home_team' => $home['id'],
			'away_team' => $away['id'],
		);
	}

	function homeAwayRatio($id) {
		if (array_key_exists ($id, $this->home_games)) {
			$home_games = $this->home_games[$id];
		} else {
			$home_games = 0;
		}

		if (array_key_exists ($id, $this->away_games)) {
			$away_games = $this->away_games[$id];
		} else {
			$away_games = 0;
		}

		if ($home_games + $away_games < 1) {
			// Avoid divide-by-zero
			return 0;
		}

		return ($home_games / ($home_games + $away_games));
	}

	/**
	 * Assign field based on home field or region preference.
	 *
	 * It uses the select_weighted_gameslot function, which first looks at home field
	 * designation, then at field region preferences.
	 *
	 * We first sort teams in order of their allocation preference ratio.  Teams
	 * with a low ratio get first crack at a desired location.
	 *
	 * Then, we allocate gameslots to games where the home team has a home field.
	 * This is necessary to prevent another team with a lower ratio from scooping
	 * another team's dedicated home field.
	 *
	 * Following this, we simply loop over all remaining games and call
	 * select_weighted_gameslot(), which takes region preference into account.
	 *
	 */
	function assignFieldsByPreferences($date, $games) {
		/*
		 * We sort by ratio of getting their preference, from lowest to
		 * highest, so that teams who received their field preference least
		 * will have a better chance of it.
		 */
		try {
			usort($games, array($this, 'cmpHometeamPreferredFieldRatio'));
		} catch (Exception $e) {
			if (is_numeric ($date)) {
				$date = date('Y-m-d', $date);
			}
			$message = __('Failed to assign gameslots for requested games on ', true) . $date . ': ' .
				__($e->getMessage(), true);
			$this->_controller->Session->setFlash($message);
			return false;
		}

		while($game = array_pop($games)) {
			$slot_id = $this->selectWeightedGameslot($game, $date);
			if (!$slot_id) {
				return false;
			}
			$game['GameSlot'] = array(
				'id' => $slot_id,
			);

			$this->games[] = $game;
		}

		return true;
	}

	function cmpHometeamPreferredFieldRatio($a, $b) {
		$a_ratio = $this->preferredFieldRatio($a['home_team']);
		$b_ratio = $this->preferredFieldRatio($b['home_team']);
		if( $a_ratio == $b_ratio ) {
			return 0;
		}

		return ($a_ratio > $b_ratio) ? 1 : -1;
	}

	function preferredFieldRatio($id) {
		// Put all those teams with a home field at the top of the list
		$team = array_pop (Set::extract ("/Team[id=$id]/.", $this->league));
		if (! $team) {
			throw new Exception ('Weird error: Cached home team wasn\'t there!');
		}
		// TODO: Is this the right return value? Test with teams that have home fields.
		if ($team['home_field']) {
			return -1;
		}

		return 0;
/* TODO This whole thing, using arrays built in startSchedule
		if( $this->preferred_ratio >= 0 ) {
			return $this->preferred_ratio;
		}

		if( ! $this->region_preference
			|| $this->region_preference == '---' ) {
			// No preference means they're always happy.  We set
			// this to over 100% to force them to sort last when
			// ordering by ratio, so that teams with a preference
			// always appear before them.
			$this->preferred_ratio = 2;
			return ($this->preferred_ratio);
		}

		// It's not the most evil SQL hack in Leaguerunner, but it's
		// probably a runner-up.  The idea is to get a count of the
		// games played in the preferred region or on a home field, and
		// a count played outside.
		$sth = $dbh->prepare(
			'SELECT
				IF(g.fid = t.home_field, 1, COALESCE(f.region,p.region) = t.region_preference) AS is_preferred,
				COUNT(*) AS num_games
				FROM schedule s
				LEFT JOIN gameslot g USING (game_id)
				LEFT JOIN field f USING (fid)
				LEFT JOIN field p ON (f.parent_fid = p.fid),
				team t
				WHERE (s.home_team = t.team_id OR s.away_team = t.team_id)
				AND t.team_id = ? GROUP BY is_preferred');
		$sth->execute( array( $this->team_id) );

		$preferred     = 0;
		$not_preferred = 0;
		while($row = $sth->fetch( PDO::FETCH_ASSOC ) ) {
			if($row['is_preferred']) {
				$preferred = $row['num_games'];
			} else {
				$not_preferred = $row['num_games'];
			}
		}

		if( $preferred + $not_preferred < 1 ) {
			# Avoid divide-by-zero
			return 0;
		}


		$this->preferred_ratio = $preferred / ($preferred + $not_preferred);
		return ($this->preferred_ratio);
*/
	}
	
	/**
	 * Select a random gameslot
	 *
	 * @param mixed $date The date of the game
	 * @return mixed The id of the selected slot
	 *
	 */
	function selectRandomGameslot($date) {
		if (is_numeric ($date)) {
			$date = date('Y-m-d', $date);
		}
		$slots = Set::extract("/LeagueGameslotAvailability/GameSlot[game_date=$date]/id", $this->league);
		if (empty ($slots)) {
			$this->_controller->Session->setFlash('Couldn\'t get a slot ID');
			return false;
		}

		shuffle ($slots);
		$slot_id = $slots[0];
		$this->removeGameslot($slot_id);
		return $slot_id;
	}

	/**
	 * Select an appropriate gameslot for this game.  "appropriate" takes
	 * field quality, home field designation, and field preferences into account.
	 * Gameslot is to be selected from those available for the league in which
	 * this game exists.
	 * Single argument is to be the timestamp representing the date of the
	 * game.
	 * Changes are made directly in the database (no need to ->save() the
	 * game) however this means that you should probably call this only
	 * within a transaction if you want to roll back changes easily on
	 * error.
	 * Returns success or fail, depending on whether or not we could get a
	 * gameslot.
	 *
	 * TODO: Take field quality into account when assigning.  Easiest way
	 * to do this would be to order by field quality instead of RAND(),
	 * keeping our best fields in use.
	 *
	 * @param mixed $game Array of game details (e.g. home_team, away_team)
	 * @param mixed $date The date of the game
	 * @return mixed The id of the selected slot
	 *
	 */
	function selectWeightedGameslot($game, $date)
	{
		if (is_numeric ($date)) {
			$date = date('Y-m-d', $date);
		}
		$slots = array();

		// try to adhere to the home team's HOME FIELD DESIGNATION
		$team = array_pop (Set::extract ("/Team[id={$game['home_team']}]/.", $this->league));
		if (! $team) {
			throw new Exception ('Weird error: Cached home team wasn\'t there!');
		}
		if ($team['home_field']) {
			$slots = Set::extract("/LeagueGameslotAvailability/GameSlot[game_date=$date][field_id={$team['home_field']}]/.", $this->league);
		}

		if (empty ($slots) && $team['region_preference']) {
			// TODO: Test this once fields are fixed
			$slots = Set::extract("/LeagueGameslotAvailability/GameSlot[game_date=$date]/Field[region={$team['region_preference']}]/.", $this->league);
		}

		if (empty ($slots)) {
			$team = array_pop (Set::extract ("/Team[id={$game['away_team']}]/.", $this->league));
			if ($team['region_preference']) {
				// TODO: Test this once fields are fixed
				$slots = Set::extract("/LeagueGameslotAvailability/GameSlot[game_date=$date]/Field[region={$team['region_preference']}]/.", $this->league);
			}
		}

		// If still nothing can be found, last try is just random
		if (empty ($slots)) {
			return $this->selectRandomGameslot($date);
		}

		shuffle ($slots);
		$slot_id = $slots[0];
		$this->removeGameslot($slot_id);
		return $slot_id;
	}

	/**
	 * Remove a slot from the list of those available
	 *
	 * @param mixed $slot_id Id of the slot to remove
	 *
	 */
	function removeGameslot($slot_id) {
		foreach ($this->league['LeagueGameslotAvailability'] as $key => $slot) {
			if ($slot['game_slot_id'] == $slot_id) {
				unset ($this->league['LeagueGameslotAvailability'][$key]);
			}
		}
	}

	/**
	 * Count how many distinct gameslot days are availabe from $date onwards
	 *
	 */
	function countAvailableGameslotDays($date) {
		if (is_numeric ($date)) {
			$date = date('Y-m-d', $date);
		}
		$dates = array_unique (Set::extract("/LeagueGameslotAvailability/GameSlot[game_date>=$date]/game_date", $this->league));
		return count($dates);
	}

	/**
	 * Return next available day of play after $date, based on gameslot availability
	 *
	 * value returned is a UNIX timestamp for the game day.
	 */
	function nextGameslotDay($date) {
		if (is_numeric ($date)) {
			$date = date('Y-m-d', $date);
		}
		$dates = array_unique (Set::extract("/LeagueGameslotAvailability/GameSlot[game_date>$date]/game_date", $this->league));
		return min($dates);
	}

	/**
	 * Calculate the ELO change for the result provided.
	 *
	 * This uses a modified Elo system, similar to the one used for
	 * international soccer (http://www.eloratings.net) with several
	 * modifications:
	 * 	- all games are equally weighted
	 * 	- score differential bonus adjusted for Ultimate patterns (ie: a 3
	 * 	  point win in soccer is a much bigger deal than in Ultimate)
	 * 	- no bonus given for home-field advantage
	 */
	function calculateRatingsChange($home_score, $away_score, $expected_win) {
		$weight_constant = 40;  // All games weighted equally
		$score_weight    = 1;   // Games start with a weight of 1

		$game_value      = 1;   // Game value is always 1 or 0.5 as we're calculating the elo change for the winning team

		// Find winning/losing scores.  In the case of a tie,
		// the home team is considered the winner for purposes of
		// rating calculation.  This has nothing to do with the
		// tiebreakers used for standings purposes as in tie cases,
		// the $elo_change will work out the same regardless of which team is
		// considered the 'winner'
		if( $home_score == $away_score) {
			// For a tie, we assume the home team wins, but give the game a
			// value of 0.5
			$game_value = 0.5;
		}

		// Calculate score differential bonus.
		// If the difference is greater than 1/3 the winning score, the bonus
		// added is the ratio of score difference over winning score.
		$score_diff = abs($home_score - $away_score);
		$score_max  = max($home_score, $away_score);
		if( $score_max && ( ($score_diff / $score_max) > (1/3) )) {
			$score_weight += $score_diff / $score_max;
		}

		$elo_change = $weight_constant * $score_weight * ($game_value - $expected_win);
		return ceil($elo_change);
	}

}

?>