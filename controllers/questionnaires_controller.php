<?php
class QuestionnairesController extends AppController {

	var $name = 'Questionnaires';

	function index() {
		$this->Questionnaire->recursive = 0;
		$this->set('questionnaires', $this->paginate('Questionnaire', array('active' => true)));
		$this->set('active', true);
	}

	function deactivated() {
		$this->Questionnaire->recursive = 0;
		$this->set('questionnaires', $this->paginate('Questionnaire', array('active' => false)));
		$this->set('active', false);
		$this->render('index');
	}

	function view() {
		$id = $this->_arg('questionnaire');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'questionnaire'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Questionnaire->contain(array(
				'Question' => array('Answer'),
				'Event',
		));
		$this->set('questionnaire', $this->Questionnaire->read(null, $id));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Questionnaire->create();
			if ($this->Questionnaire->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), 'questionnaire'));
				$this->redirect(array('action' => 'edit', 'questionnaire' => $this->Questionnaire->id));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please, try again.', true), 'questionnaire'));
			}
		}
	}

	function edit() {
		$id = $this->_arg('questionnaire');
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'questionnaire'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Questionnaire->saveAll($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), 'questionnaire'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please, try again.', true), 'questionnaire'));
			}
		}
		if (empty($this->data)) {
			$this->Questionnaire->contain(array('Question' => array('Answer')));
			$this->data = $this->Questionnaire->read(null, $id);
		}
		$questions = $this->Questionnaire->Question->find('list');
		$this->set(compact('questions'));
	}

	function activate() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);
		$name = $this->Questionnaire->field('name', array('id' => $questionnaire));

		$success = $this->Questionnaire->updateAll (array('active' => true), array(
				'Questionnaire.id' => $questionnaire,
		));
		$this->set(compact('success', 'name'));
	}

	function deactivate() {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';

		extract($this->params['named']);
		$this->set($this->params['named']);
		$name = $this->Questionnaire->field('name', array('id' => $questionnaire));

		$success = $this->Questionnaire->updateAll (array('active' => 0), array(
				'Questionnaire.id' => $questionnaire,
		));
		$this->set(compact('success', 'name'));
	}

	function delete() {
		$id = $this->_arg('questionnaire');
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid id for %s', true), 'questionnaire'));
			$this->redirect(array('action'=>'index'));
		}

		// TODO: Don't delete questionnaires that are referenced by events

		// TODO Handle deletions
		$this->Session->setFlash(sprintf(__('Deleting %s is disabled', true), 'questionnaires'));
		$this->redirect(array('action' => 'index'));

		if ($this->Questionnaire->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), 'Questionnaire'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), 'Questionnaire'));
		$this->redirect(array('action' => 'index'));
	}

	function add_question($id, $i) {
		Configure::write ('debug', 0);
		$this->layout = 'ajax';
		$this->Questionnaire->Question->recursive = -1;
		$question = $this->Questionnaire->Question->read(null, $id);
		$question = $question['Question'];
		$this->set(compact('question', 'i'));
	}

	function consolidate() {
		$this->Questionnaire->contain(array('Question' => array('order' => 'QuestionnairesQuestion.sort')));
		$questionnaires = $this->Questionnaire->find('all', array(
				'order' => 'Questionnaire.id',
		));
		$this->QuestionnairesQuestions = ClassRegistry::init ('QuestionnairesQuestions');

		$matches = array();
		foreach ($questionnaires as $key_one => $one) {
			foreach ($questionnaires as $key_two => $two) {
				if ($key_one < $key_two) {
					$match = $this->_compare_questionnaires($one, $two);
					if ($match === true) {
						unset ($questionnaires[$key_two]);
						$matches[$one['Questionnaire']['id']][$two['Questionnaire']['id']] = $this->_merge_questionnaires ($one, $two);
					} else if ($match !== false) {
						$matches[$one['Questionnaire']['id']][$two['Questionnaire']['id']] = $match;
					}
				}
			}
		}

		$this->set(compact('matches'));
	}

	function _compare_questionnaires($one, $two) {
		$q1 = Set::extract ('/Question/id', $one);
		$q2 = Set::extract ('/Question/id', $two);
		return ($q1 == $q2);
	}

	function _merge_questionnaires($one, $two) {
		$result =
			$this->Questionnaire->Event->updateAll (
				array('questionnaire_id' => $one['Questionnaire']['id']),
				array('questionnaire_id' => $two['Questionnaire']['id'])
			) &&
			$this->QuestionnairesQuestions->deleteAll (
				array('questionnaire_id' => $two['Questionnaire']['id']), false
			) &&
			$this->Questionnaire->deleteAll (
				array('id' => $two['Questionnaire']['id']), false
			);

		return ($result ? true : 'Failed to merge');
	}
}
?>