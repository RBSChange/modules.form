<?php
/**
 * form_patch_0306
 * @package modules.form
 */
class form_patch_0306 extends patch_BasePatch
{
 
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		//Original creation table Query expected
		//$this->executeSQLQuery("create table `m_form_doc_baseform` ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_bin select * `from m_form_doc_form`");
		//$this->executeSQLQuery("create table `m_form_doc_baseform_i18n` ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_bin select * from `m_form_doc_form_i18n`");
		
		try 
		{
			//Update Engine 
			$this->executeSQLQuery("ALTER TABLE `m_form_doc_baseform` ENGINE = innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_bin");
			$this->executeSQLQuery("ALTER TABLE `m_form_doc_baseform_i18n` ENGINE = innodb DEFAULT CHARACTER SET utf8 COLLATE utf8_bin");
		
			//Update Primary Key
			$this->executeSQLQuery("ALTER TABLE `m_form_doc_baseform` ADD PRIMARY KEY ( `document_id` ) ");
			$this->executeSQLQuery("ALTER TABLE `m_form_doc_baseform_i18n` ADD PRIMARY KEY ( `document_id`,  `lang_i18n`) ");
		}
		catch (BaseException $e)
		{
			//Multiple primary key defined
			if ($e->getAttribute('errorcode') == 1068)
			{
				$this->log('Table m_form_doc_baseform, m_form_doc_baseform_i18n already patched');
			}
			else
			{
				throw $e;
			}
		}
		
		//Optimize Index..
		//$this->executeSQLQuery("ALTER TABLE `m_form_doc_baseform` ADD UNIQUE `FORMID` ( `formid` )");
	}

	/**
	 * @return String
	 */
	protected final function getModuleName()
	{
		return 'form';
	}

	/**
	 * @return String
	 */
	protected final function getNumber()
	{
		return '0306';
	}
}