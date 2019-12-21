<?php
/**
 * @version     1.1.0
 * @package     Akismet for Kunena
 * @author      JoomlaWorks https://www.joomlaworks.net
 * @copyright   Copyright (c) 2006 - 2019 JoomlaWorks Ltd. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/licenses/gpl.html
 */

// No direct access
defined('_JEXEC') or die;

class plgKunenaAkismet extends JPlugin
{
    public function onKunenaBeforeSave($context, $table, $isNew)
    {
        // Utilize a single language file
        $this->loadLanguage('plg_kunena_akismet.sys');

        if ($context == 'com_kunena.KunenaForumMessage' && $this->params->get('apiKey')) {
            $user = JFactory::getUser();

            // Disable for admins
            if ($user->authorise('core.login.admin')) {
                return true;
            }

            // Load the Akismet for PHP class
            require_once JPATH_SITE.'/plugins/kunena/akismet/Akismet.class.php';

            $akismet = new Akismet(JURI::root(false), $this->params->get('apiKey'));
            $akismet->setCommentAuthor($user->name);
            $akismet->setCommentAuthorEmail($user->email);
            $akismet->setCommentContent($table->message);

            try {
                $result = $akismet->isCommentSpam();

                if ($result) {
                    $table->setError(JText::_('PLG_KUNENA_AKISMET_SPAM_DETECTED'));

                    return false;
                } else {
                    return true;
                }
            } catch (Exception $exception) {
                $table->setError($exception->getMessage());

                return false;
            }
        }
    }
}
