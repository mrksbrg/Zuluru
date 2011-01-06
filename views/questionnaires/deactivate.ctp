<?php
if ($success) {
	$content = $this->Js->link(__('Activate', true),
			array('action' => 'activate', 'questionnaire' => $questionnaire, 'id' => $id),
			array('update' => "#temp_update")
	);
	echo $this->Html->scriptBlock ("$('#$id').html('$content')");
} else {
	echo $this->Html->scriptBlock ("alert('Failed to deactivate questionnaire \'$name\'.')");
}

// Output the event handler code for the links
echo $this->Js->writeBuffer();
?>
