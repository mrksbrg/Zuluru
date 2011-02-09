<?php
$this->Html->addCrumb (__('League', true));
$this->Html->addCrumb ($league['League']['long_name']);
$this->Html->addCrumb (__('Add Games', true));
$this->Html->addCrumb (__('Select Type', true));
?>

<div class="schedules add">
<?php echo $this->element('schedule/exclude'); ?>

<p>Please enter some information about the game(s) to create.</p>

<?php
echo $this->Form->create ('Game', array('url' => array('controller' => 'schedules', 'action' => 'add', 'league' => $id)));
$this->data['Game']['step'] = 'type';
echo $this->element('hidden', array('fields' => $this->data));
?>

<fieldset>
<legend>Create a ...</legend>
<?php
echo $this->Form->input('type', array(
		'legend' => false,
		'type' => 'radio',
		'options' => $types,
));
?>

<p>Select the type of game or games to add.  Note that for auto-generated round-robins, fields will be automatically allocated.</p>

<?php
echo $this->Form->input('publish', array(
		'label' => __('Publish created games for player viewing?', true),
		'type' => 'checkbox',
));
?>

<p>If this is checked, players will be able to view games immediately after creation.  Uncheck it if you wish to make changes before players can view.</p>
</fieldset>

<?php echo $this->Form->end(__('Next step', true)); ?>

</div>