<?php
class form_ExportAction extends f_action_BaseAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$form = $this->getDocumentInstanceFromRequest($request);
		$request->setAttribute('form', $form);

		// determine output type
		if ( $request->hasParameter('output') )
		{
			$output = ucfirst(strtolower($request->getParameter('output')));
		}
		if ( empty($output) )
		{
			$output = ucfirst(K::HTML);
		}
		
		if ( $request->getParameter('all') == 'all')
		{
			$request->setAttribute('all', 'all');
		}
		
		$className = 'form_Export' . $output . 'View';
		if ( ! f_util_ClassUtils::classExists($className) )
		{
			throw new Exception('Unable to format output as "'.$output.'".');
		}
		$request->setAttribute('form', $form);

		return $output;
	}
}