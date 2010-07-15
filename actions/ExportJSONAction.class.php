<?php
class form_ExportJSONAction extends f_action_BaseJSONAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$startIndex = $request->hasParameter('startIndex') ? $request->getParameter('startIndex') : 0;
		$pageSize = $request->hasParameter('pageSize') ? $request->getParameter('pageSize') : null;
		$form = $this->getDocumentInstanceFromRequest($request);
		$result = $form->getDocumentService()->getResponseDataByForm($form, $startIndex, $pageSize);
		return $this->sendJSON($result);
	}
}