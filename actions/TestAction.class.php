<?php
class form_TestAction extends f_action_BaseAction
{

	public function _execute($context, $resquest)
	{
		echo "<h2>Avec FormHelper</h2>\n";
		echo FormHelper::textBox("firstname", "firstname_10025", "fred")."<br />\n";
		echo FormHelper::multilineTextBox("address", "155", "11 rue Icare")."<br />\n";
		echo FormHelper::comboBox("title", "155", "M", array("Mme" => "Madame", "M"=>"Monsieur"))."<br />\n";
		echo FormHelper::listBox("sports", "spr15", array("Roller","Natation"), array("Roller"=>"Roller", "Cyclisme"=>"Cyclisme","Natation"=>"Natation","Skateboard"=>"Skateboard"))."<br />\n";
		echo FormHelper::checkBox("agree", "10027", "fred")."<br />\n";

		echo "<h2>A partir de documents</h2>\n";
		echo FormHelper::fromFieldDocument(DocumentHelper::getDocumentInstance(10055), array("Mlle","Mme")) . "<br />\n";
		echo FormHelper::fromFieldDocument(DocumentHelper::getDocumentInstance(10052), "Fred") . "<br />\n";
		echo FormHelper::fromFieldDocument(DocumentHelper::getDocumentInstance(10036)) . "<br />\n";
	}
}