<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userinvites
 *
 * @copyright   Copyright (C) NPEU 2024.
 * @license     MIT License; see LICENSE.md
 */

/**
 *
 */

namespace NPEU\Component\Userinvites\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\Router\RouterInterface;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\MVC\Factory\MVCFactoryAwareTrait;

class RouterFactory extends \Joomla\CMS\Component\Router\RouterFactory
{
    use MVCFactoryAwareTrait;

    public function createRouter(CMSApplicationInterface $application, AbstractMenu $menu): RouterInterface
    {
        $router = parent::createRouter($application, $menu);

        $router->setMVCFactory($this->getMVCFactory());

        return $router;
    }
}