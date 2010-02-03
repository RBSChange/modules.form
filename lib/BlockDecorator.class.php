<?php
abstract class form_BlockFormDecorator
{
    /**
     * @var block_BlockAction
     */
    private $blockAction;

    /**
     * @param block_BlockAction $blockAction
     */
    public final function __construct($blockAction)
	{
        $this->blockAction = $blockAction;
	}

	/**
	 * @param String $name
	 * @param String $value
	 */
	protected final function setParameter($name, $value)
	{
		$this->blockAction->setParameter($name, $value);
	}

	/**
	 * @param String $name
	 * @return mixed
	 */
	protected final function getParameter($name)
	{
		return $this->blockAction->getParameter($name);
	}

	/**
	 * @param block_BlockContext $context
	 * @param block_BlockRequest $request
	 */
	abstract public function execute($context, $request);
}