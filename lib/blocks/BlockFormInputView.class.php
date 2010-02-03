<?php
class form_BlockFormInputView extends block_BlockView
{
	/**
	 * Mandatory execute method...
	 *
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 */
    public function execute($context, $request)
    {
		$context->addScript('modules.form.lib.js.date-picker.date');
		$context->addScript('modules.form.lib.js.date-picker.date_'.RequestContext::getInstance()->getLang());
		$context->addScript('modules.form.lib.js.date-picker.jquery-bgiframe');
		$context->addScript('modules.form.lib.js.date-picker.jquery-dimensions');
		$context->addScript('modules.form.lib.js.date-picker.jquery-datePicker');
		$context->addScript('modules.form.lib.js.form');
    	$form = $this->getParameter('form');
    	$this->setTemplateName('markup/'.$form->getMarkup().'/Form');
    	$this->setAttribute('form', $form);

    	$contents = array();

    	form_FormService::getInstance()->buildContentsFromRequest($form->getDocumentNode()->getChildren(), $contents, $request, $form);

    	$this->setAttribute('elements', $contents);

    	if ( $this->hasParameter('errors') )
    	{
    		$this->setAttribute('errors', $this->getParameter('errors'));
    	}
    	$this->setAttribute('selfUrl', $_SERVER['REQUEST_URI']);
    	if ($request->getParameter(form_FormConstants::BACK_URL_PARAMETER))
    	{
    		$backUrl = $request->getParameter(form_FormConstants::BACK_URL_PARAMETER);
    	}
    	else if (isset($_SERVER['HTTP_REFERER']))
    	{
    		$backUrl = $_SERVER['HTTP_REFERER'];
    	}
    	else
    	{
    		$backUrl = website_WebsiteModuleService::getInstance()->getCurrentWebsite()->getUrl();
    	}
		$this->setAttribute("receiverLabels", $this->getParameter("receiverLabels"));
    	$this->setAttribute('requestParameters', $request->getParameters());
    	$this->setAttribute('backUrl', $backUrl);
    	
    	$this->setAttribute('jQueryConditionalElement', $form->getDocumentService()->getJQueryForConditionalElementsOf($form));
    }
}
