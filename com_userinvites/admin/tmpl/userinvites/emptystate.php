<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userinvites
 *
 * @copyright   Copyright (C) NPEU 2023.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_USERINVITES',
    'formURL'    => 'index.php?option=com_userinvites',
];

/*
$displayData = [
    'textPrefix' => 'COM_USERINVITES',
    'formURL'    => 'index.php?option=com_userinvites',
    'helpURL'    => '',
    'icon'       => 'icon-globe userinvites',
];
*/

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_userinvites') || count($user->getAuthorisedCategories('com_userinvites', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_userinvites&view=sendinvites';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);