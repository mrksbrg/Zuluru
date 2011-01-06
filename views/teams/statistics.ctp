<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb (__('Statistics', true));
?>

<div class="teams statistics">
<h2><?php __('Team Statistics');?></h2>

<h3><?php __('Teams by League'); ?></h3>
<table>
	<thead>
		<tr>
			<th><?php __('League'); ?></th>
			<th><?php __('Teams'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$total = 0;
foreach ($counts as $league):
	$total += $league[0]['count'];
?>
		<tr>
			<td><?php echo $this->Html->link ($league['League']['long_name'],
					array('controller' => 'leagues', 'action' => 'view', 'league' => $league['League']['id']));
			?></td>
			<td><?php echo $league[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

		<tr>
			<td><?php __('Total'); ?></td>
			<td><?php echo $total; ?></td>
		</tr>
	</tbody>
</table>

<h3><?php __('Teams with too few players'); ?></h3>
<table>
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Players'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($shorts as $team):
?>
		<tr>
			<td><?php echo $this->Html->link ($team['Team']['name'],
					array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id']),
					array('title' => "League: {$team['League']['long_name']}"));
			?></td>
			<td><?php
			echo $team[0]['size'];
			if ($team[0]['subs'] > 0) {
				echo " ({$team[0]['subs']} subs)";
			}
			?></td>
		</tr>
<?php endforeach; ?>

	</tbody>
</table>

<h3><?php __('Top-rated Teams'); ?></h3>
<table>
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Rating'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($top_rating as $team):
?>
		<tr>
			<td><?php echo $this->Html->link ($team['Team']['name'],
					array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id']),
					array('title' => "League: {$team['League']['long_name']}"));
			?></td>
			<td><?php echo $team['Team']['rating']; ?></td>
		</tr>
<?php endforeach; ?>

	</tbody>
</table>

<h3><?php __('Lowest-rated Teams'); ?></h3>
<table>
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Rating'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($lowest_rating as $team):
?>
		<tr>
			<td><?php echo $this->Html->link ($team['Team']['name'],
					array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id']),
					array('title' => "League: {$team['League']['long_name']}"));
			?></td>
			<td><?php echo $team['Team']['rating']; ?></td>
		</tr>
<?php endforeach; ?>

	</tbody>
</table>

<h3><?php __('Top Defaulting Teams'); ?></h3>
<table>
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Defaults'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($defaulting as $team):
?>
		<tr>
			<td><?php echo $this->Html->link ($team[0]['team_name'],
					array('controller' => 'teams', 'action' => 'view', 'team' => $team[0]['team_id']),
					array('title' => "League: {$team['League']['long_name']}"));
			?></td>
			<td><?php echo $team[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

	</tbody>
</table>

<h3><?php __('Top Non-score-submitting Teams'); ?></h3>
<table>
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Games'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($no_scores as $team):
?>
		<tr>
			<td><?php echo $this->Html->link ($team[0]['team_name'],
					array('controller' => 'teams', 'action' => 'view', 'team' => $team[0]['team_id']),
					array('title' => "League: {$team['League']['long_name']}"));
			?></td>
			<td><?php echo $team[0]['count']; ?></td>
		</tr>
<?php endforeach; ?>

	</tbody>
</table>

<h3><?php __('Top Spirited Teams'); ?></h3>
<table>
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Average Spirit'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($top_spirit as $team):
?>
		<tr>
			<td><?php echo $this->Html->link ($team['Team']['name'],
					array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id']),
					array('title' => "League: {$team['League']['long_name']}"));
			?></td>
			<td><?php echo $team[0]['avgspirit']; ?></td>
		</tr>
<?php endforeach; ?>

	</tbody>
</table>

<h3><?php __('Lowest Spirited Teams'); ?></h3>
<table>
	<thead>
		<tr>
			<th><?php __('Team'); ?></th>
			<th><?php __('Average Spirit'); ?></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($lowest_spirit as $team):
?>
		<tr>
			<td><?php echo $this->Html->link ($team['Team']['name'],
					array('controller' => 'teams', 'action' => 'view', 'team' => $team['Team']['id']),
					array('title' => "League: {$team['League']['long_name']}"));
			?></td>
			<td><?php echo $team[0]['avgspirit']; ?></td>
		</tr>
<?php endforeach; ?>

	</tbody>
</table>

</div>
<div class="actions">
	<p><?php __('Other years'); ?>:</p>
	<ul>
<?php
foreach ($years as $y) {
	echo $this->Html->tag('li', $this->Html->link($y[0]['year'], array('year' => $y[0]['year'])));
}
?>

	</ul>
</div>

<?php if (isset($leagues)): ?>
<div class="actions" style="clear:both;">
	<p><?php __('Other leagues'); ?>:</p>
	<ul>
<?php
foreach ($leagues as $league_id => $league) {
	echo $this->Html->tag('li', $this->Html->link($league, array('year' => $year, 'league' => $league_id)));
}
?>

	</ul>
</div>
<?php endif; ?>
