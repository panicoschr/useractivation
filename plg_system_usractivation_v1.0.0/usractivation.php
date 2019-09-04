<?php

/**
 * @package	Usractivation.Plugin
 * @subpackage	System.Usractivation
 * @copyright	WWW.MEPRO.CO - All rights reserved.
 * @author	MEPRO SOFTWARE SOLUTIONS
 * @link	http://www.mepro.co
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// No direct access
defined('_JEXEC') or die;


class plgSystemUsractivation extends JPlugin {

    var $_cache = null;

    function __construct(& $subject, $config) {
        parent::__construct($subject, $config);
    }

    public function onAfterRoute() {
        $url = JUri::getInstance();
        $findit = 'task=registration.activate&token=';
        $pos = strpos($url, $findit);
        if ($pos > 0) {
            $this->moveParamsToToken();
        }
    }

    public function onAfterRender() {
        $this->moveTokenToParams();
    }

    public function moveTokenToParams() {
        $db = JFactory::getDBO();
        JPluginHelper::importPlugin('user');
        $activated1 = '"activate":1';
        $searchactivated1 = $db->Quote('{' . $activated1 . '}');
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('id', 'activation')))
                ->from($db->quoteName('#__users'))
                ->where('(params = ' . $searchactivated1 . ')')
                ->where($db->quoteName('block') . ' = ' . 1)
                ->where($db->quoteName('lastvisitDate') . ' = ' . $db->quote($db->getNullDate()));
        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach ($items as $item) {
            $userid = $item->id;
            $activation = $item->activation;
            $user = JFactory::getUser($userid);
            $user->setParam('activation', $activation);
            $user->set('activation', '');
            if (!$user->save()) {
                $this->setError(JText::sprintf('COM_USERS_REGISTRATION_ACTIVATION_SAVE_FAILED', $user->getError()));
                return false;
            }
            return $user;
        }
    }

    public function moveParamsToToken() {
        $db = JFactory::getDBO();
        JPluginHelper::importPlugin('user');
        $activated1 = '"activate":1,"activation":"';
        $searchactivated1 = $db->Quote('%' . $activated1 . '%');
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('id')))
                ->from($db->quoteName('#__users'))
                ->where('(params LIKE ' . $searchactivated1 . ')')
                ->where($db->quoteName('block') . ' = ' . 1)
                ->where($db->quoteName('lastvisitDate') . ' = ' . $db->quote($db->getNullDate()));
        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach ($items as $item) {
            $userid = $item->id;
            $user = JFactory::getUser($userid);
            $activation = $user->getParam('activation');
            $user->set('activation', $activation);
            $user->setParam('activation', '');
            if (!$user->save()) {
                $this->setError(JText::sprintf('COM_USERS_REGISTRATION_ACTIVATION_SAVE_FAILED', $user->getError()));
                return false;
            }
            return $user;
        }
    }

}
