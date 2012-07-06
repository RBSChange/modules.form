<?php
class form_PreviewFormErrorView extends change_View
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$this->setMimeContentType('html');
		$this->setTemplateName('PreviewForm-Error', 'html');
	}
}