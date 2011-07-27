<?php
class form_FormLoadHandler extends website_ViewLoadHandlerImpl 
{
	/**
	 * @param website_BlockActionRequest $request
	 * @param website_BlockActionResponse $response
	 */
	public function execute($request, $response)
	{
		FormHelper::addScriptsAndStyles($this->getContext());
	}
}