<?php
$this->Html->addCrumb (__('Questionnaire', true));
$this->Html->addCrumb ($this->data['Questionnaire']['name']);
$this->Html->addCrumb (__('Edit', true));
?>

<div class="questionnaires form">
<?php echo $this->Form->create('Questionnaire');?>
	<fieldset>
 		<legend><?php printf(__('Edit %s', true), __('Questionnaire', true)); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name', array('size' => 60));
		echo $this->Form->input('active');
	?>
	</fieldset>
	<fieldset>
 		<legend><?php __('Questions'); ?></legend>
	<?php
		echo $this->element('/questionnaire/edit', array('questionnaire' => $this->data));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
