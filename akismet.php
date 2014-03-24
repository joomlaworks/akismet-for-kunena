<?php
// No direct access
defined('_JEXEC') or die ;

class plgKunenaAkismet extends JPlugin
{

	public function onKunenaBeforeSave($context, $table, $isNew)
	{
		$this->loadLanguage('plg_kunena_akismet.sys');
		if ($context == 'com_kunena.KunenaForumMessage' && $this->params->get('apiKey'))
		{
			require_once JPATH_SITE.'/plugins/kunena/akismet/Akismet.class.php';
			$user = JFactory::getUser();
			$akismet = new Akismet(JURI::root(false), $this->params->get('apiKey'));
			$akismet->setCommentAuthor($user->name);
			$akismet->setCommentAuthorEmail($user->email);
			$akismet->setCommentContent($table->message);
			try
			{
				$result = $akismet->isCommentSpam();
				if ($result)
				{
					$table->setError(JText::_('PLG_KUNENA_AKISMET_SPAM_DETECTED'));
					return false;
				}
				else
				{
					return true;
				}
			}
			catch(Exception $exception)
			{
				$table->setError($exception->getMessage());
				return false;
			}
		}
	}

}
