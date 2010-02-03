<?php
class form_BlockFormDummyView extends block_BlockView
{

	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 */
    public function execute($context, $request)
    {
    	$this->setTemplateName('Form-Dummy');
		$form = $this->getParameter('form');
		$this->setAttribute('form', $form);
    }
}