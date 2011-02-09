<?php
$this->Html->addCrumb (__('Settings', true));
$this->Html->addCrumb (__('Scoring', true));
?>

<div class="settings form">
<?php echo $this->Form->create('Settings', array('url' => array('scoring')));?>
	<fieldset>
 		<legend><?php __('Defaulted Games'); ?></legend>
	<?php
	echo $this->element ('setting/input', array(
		'category' => 'scoring',
		'name' => 'default_winning_score',
		'options' => array(
			'label' => 'Winning score to record for defaulted games',
			'size' => 6,
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'scoring',
		'name' => 'default_losing_score',
		'options' => array(
			'label' => 'Losing score to record for defaulted games',
			'size' => 6,
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'scoring',
		'name' => 'default_transfer_ratings',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'label' => 'Transfer ratings points for defaulted games',
		),
	));
	?>
	</fieldset>

	<fieldset>
 		<legend><?php __('Spirit Scores'); ?></legend>
	<?php
	echo $this->element ('setting/input', array(
		'category' => 'scoring',
		'name' => 'spirit_questions',
		'options' => array(
			'type' => 'select',
			'options' => Configure::read ('options.spirit_questions'),
			'after' => 'Default type of spirit questions to use when creating a new league.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'scoring',
		'name' => 'spirit_numeric',
		'options' => array(
			'type' => 'select',
			'options' => Configure::read ('options.enable'),
			'after' => 'Default enable or disable entry of numeric spirit scores when creating a new league.',
		),
	));

	echo $this->Html->para(null, __('By using various combinations of questions and numeric entry above, you can have just the questionnaire, just the numeric entry, both or neither.', true));
	echo $this->Html->para(null, __('The values set above will be the default value for leagues, but can be overridden on a per-league basis.', true));

	echo $this->element ('setting/input', array(
		'category' => 'scoring',
		'name' => 'missing_score_spirit_penalty',
		'options' => array(
			'label' => 'Spirit penalty for not entering score',
			'size' => 6,
		),
	));
	?>
	</fieldset>

	<fieldset>
 		<legend><?php __('Score Entry Features'); ?></legend>
	<?php
	echo $this->element ('setting/input', array(
		'category' => 'scoring',
		'name' => 'allstars',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'If enabled, all-star submissions will be a per-league option; otherwise, they will be disabled entirely.',
		),
	));
	echo $this->element ('setting/input', array(
		'category' => 'scoring',
		'name' => 'incident_reports',
		'options' => array(
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'after' => 'If enabled, captains will be allowed to file incident reports when submitting scores.',
		),
	));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>