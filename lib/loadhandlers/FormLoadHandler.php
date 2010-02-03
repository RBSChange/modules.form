<?php
class form_FormLoadHandler extends website_ViewLoadHandlerImpl 
{
	/**
	 * @param website_BlockActionRequest $request
	 * @param website_BlockActionResponse $response
	 */
	public function execute($request, $response)
	{
		$page = $this->getPage(); 
		$page->addStyle('modules.form.frontoffice');
		$page->addScript('modules.form.lib.js.date-picker.date');
		$page->addScript('modules.form.lib.js.date-picker.date_'.RequestContext::getInstance()->getLang());
		$page->addScript('modules.form.lib.js.date-picker.jquery-bgiframe');
		$page->addScript('modules.form.lib.js.date-picker.jquery-datePicker');
		$page->addScript('modules.form.lib.js.form');
	}
}