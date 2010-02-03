<?php
class form_PreviewFormErrorView extends f_view_BaseView
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$this->setMimeContentType('html');
		$this->setTemplateName('PreviewForm-Error', K::HTML);
	}
}