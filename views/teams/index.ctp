<?php
$this->Html->addCrumb (__('Teams', true));
$this->Html->addCrumb (__('List', true));
?>

<div class="teams index">
<h2><?php __('List Teams');?></h2>
<p><?php
__('Locate by letter: ');
$links = array();
foreach ($letters as $l) {
	$l = up($l[0]['letter']);
	$links[] = $this->Html->link($l, array('action' => 'letter', 'letter' => $l));
}
echo implode ('&nbsp;&nbsp;', $links);
?></p>
<p>
<?php
echo $this->Paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table cellpadding="0" cellspacing="0">
<tr>
	<th><?php echo $this->Paginator->sort('name');?></th>
	<th><?php echo $this->Paginator->sort('league_id');?></th>
	<th class="actions"><?php __('Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($teams as $team):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link($team['Team']['name'], array('action' => 'view', 'team' => $team['Team']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($team['League']['long_name'], array('controller' => 'leagues', 'action' => 'view', 'league' => $team['League']['id'])); ?>
		</td>
		<td class="actions">
			<?php
			echo $this->Html->link(__('Schedule', true), array('action' => 'schedule', 'team' => $team['Team']['id']));
			echo $this->Html->link(__('Standings', true), array('controller' => 'leagues', 'action' => 'standings', 'league' => $team['League']['id'], 'team' => $team['Team']['id']));
			if ($is_admin) {
				echo $this->Html->link(__('Edit', true), array('action' => 'edit', 'team' => $team['Team']['id']));
				echo $this->Html->link(__('Delete', true), array('action' => 'delete', 'team' => $team['Team']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $team['Team']['id']));
			}
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="paging">
	<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $this->Paginator->numbers();?> | 
	<?php echo $this->Paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
</div>