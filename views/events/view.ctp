<?php
$this->Html->addCrumb (__('Registration', true));
$this->Html->addCrumb ($event['Event']['name']);
$this->Html->addCrumb (__('View', true));
?>

<div class="events view">
	<h2><?php echo __('View Event', true) . ': ' . $event['Event']['name'];?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['Event']['name']; ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Description'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['Event']['description']; ?>
			&nbsp;
		</dd>
<?php if (array_key_exists ('level_of_play', $event['Event']) && !empty ($event['Event']['level_of_play'])): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Level of Play'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['Event']['level_of_play']; ?>

		</dd>
<?php endif; ?>
		
<?php if (array_key_exists ('League', $event)): ?>
<?php if (count($sites) > 0 && count($sites) < 4):
		$site_links = array();
		foreach ($sites as $site_id => $site_name) {
			$site_links[] = $this->Html->link ($site_name, array('controller' => 'fields', 'action' => 'view', 'field' => $site_id));
		}
?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __(count($sites) == 1 ? 'Location' : 'Locations'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo implode (', ', $site_links); ?>

		</dd>
<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('First Game'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->Date ($event['League']['open']); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Last Game'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->Date ($event['League']['close']); ?>

		</dd>
<?php if (count($times) > 0 && count($times) < 5):
		$time_list = array();
		foreach ($times as $start => $end) {
			$time_list[] = $this->ZuluruTime->Time ($start) . '-' . $this->ZuluruTime->Time ($end);
		}
?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __(count($times) == 1 ? 'Game Time' : 'Game Times'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo implode (', ', $time_list); ?>

		</dd>
<?php endif; ?>
<?php endif; ?>

		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Event Type'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($event['EventType']['name']); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Cost'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php
			$cost = $event['Event']['cost'] + $event['Event']['tax1'] + $event['Event']['tax2'];
			if ($cost > 0) {
				echo '$' . $cost;
			} else {
				echo $this->Html->tag ('span', 'FREE', array('class' => 'free'));
			}
			?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Registration Opens'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->DateTime ($event['Event']['open']); ?>

		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Registration  Closes'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->ZuluruTime->DateTime ($event['Event']['close']); ?>

		</dd>
<?php if ($event['Event']['cap_female'] == -2): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Registration Cap'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['Event']['cap_male']; ?>

		</dd>
<?php else: ?>
<?php if ($event['Event']['cap_male'] > 0): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Male Cap'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['Event']['cap_male']; ?>

		</dd>
<?php endif; ?>
<?php if ($event['Event']['cap_female'] > 0): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Female Cap'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $event['Event']['cap_female']; ?>

		</dd>
<?php endif; ?>
<?php endif; ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Multiples'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __($event['Event']['multiple'] ? 'Allowed' : 'Not allowed'); ?>

		</dd>
<?php if ($event['Event']['anonymous']): ?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __('Survey'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php __('Results of this event\'s survey will be anonymous.'); ?>

		</dd>
<?php endif; ?>
	</dl>

<?php if (!$is_logged_in): ?>
<p class="error-message"><?php printf (__('You cannot register for any events until you are %s to the site and your %s profile has been completed.', true),
	$this->Html->link (__('logged on', true), Configure::read('urls.login')),
	$this->Html->link (Configure::read('site.name'), Configure::read('urls.zuluru_base') . '/')); ?></p>
<p><?php printf (__('The system can help you %s or %s.', true),
	$this->Html->link (__('recover forgotten passwords', true), Configure::read('urls.password_reset')),
	$this->Html->link (sprintf (__('register if you are a new %s member', true), Configure::read('organization.short_name')), Configure::read('urls.register'))); ?></p>
<?php
else:
	foreach ($messages as $message) {
		$class = null;
		if (is_array($message)) {
			$class = $message['class'];
			$message = $message['text'];
		}
		echo $this->Html->para ($class, $message);
	}
	if ($allowed) {
		echo $this->Html->tag ('h2', $this->Html->link(__('Register now!', true),
				array('controller' => 'registrations', 'action' => 'register', 'event' => $id),
				array('title' => __('Register for ', true) . $event['Event']['name'], 'style' => 'text-decoration: underline;')
		));
	}
?>

</div>

<div class="actions">
	<ul>
<?php if (array_key_exists ('League', $event)): ?>
		<li><?php echo $this->Html->link(sprintf(__('View %s', true), __('League', true)), array('controller' => 'leagues', 'action' => 'view', 'league' => $event['League']['id'])); ?> </li>
<?php endif; ?>
<?php if ($is_admin): ?>
		<li><?php echo $this->Html->link(sprintf(__('Edit %s', true), __('Event', true)), array('action' => 'edit', 'event' => $event['Event']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Delete %s', true), __('Event', true)), array('action' => 'delete', 'event' => $event['Event']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $event['Event']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Preregistrations', true)), array('controller' => 'registrations', 'action' => 'preregistrations', 'event' => $event['Event']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('New %s', true), __('Preregistration', true)), array('controller' => 'registrations', 'action' => 'preregister', 'event' => $event['Event']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('%s Summary', true), __('Registration', true)), array('controller' => 'registrations', 'action' => 'summary', 'event' => $event['Event']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Detailed %s List', true), __('Registration', true)), array('controller' => 'registrations', 'action' => 'full_list', 'event' => $event['Event']['id'])); ?> </li>
		<li><?php echo $this->Html->link(sprintf(__('Download %s List', true), __('Registration', true)), array('controller' => 'registrations', 'action' => 'full_list', 'event' => $event['Event']['id'], 'ext' => 'csv')); ?> </li>
<?php endif; ?>
	</ul>
</div>

<?php if (!empty($event['Preregistration']) && $is_admin):?>
<div class="related">
	<h3><?php printf(__('Related %s', true), __('Preregistrations', true));?></h3>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php __('Person Id'); ?></th>
		<th><?php __('Event Id'); ?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($event['Preregistration'] as $preregistration):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $preregistration['person_id'];?></td>
			<td><?php echo $preregistration['event_id'];?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('Delete', true), array('controller' => 'preregistrations', 'action' => 'delete', $preregistration['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $preregistration['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>

<?php endif; ?>