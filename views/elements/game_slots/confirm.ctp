<li><span class="name"><?php echo $field['name'] . ' ' . $field['num']; ?></span>
<div class="hidden">
<?php
foreach ($weeks as $key => $week) {
	echo $this->Form->input("GameSlot.Create.{$field['id']}.$key", array(
			'div' => false,
			'label' => $week,
			'type' => 'checkbox',
			'hiddenField' => false,
			'checked' => true,
	));
}
?>
</div>
</li>
