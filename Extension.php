<?php

namespace Bolt\Extension\Bolt\Members;

use \Bolt\Extension\Bolt\ClientLogin\ClientLoginEvent;

/**
 * Membership management extension for Bolt
 *
 * Copyright (C) 2014  Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class Extension extends \Bolt\BaseExtension
{
    /**
     * @var string Extension name
     */
    const NAME = 'Members';

    /**
     * Extension's container
     *
     * @var string
     */
    const CONTAINER = 'extensions.Members';

    public function getName()
    {
        return Extension::NAME;
    }

    public function initialize()
    {
        /*
         * Backend
         */
        if ($this->app['config']->getWhichEnd() == 'backend') {
            // Check & create database tables if required
            $records = new Records($this->app);
            $records->dbCheck();
        }

        /*
         * Frontend
         */
        if ($this->app['config']->getWhichEnd() == 'frontend') {
            // Register ourselves as a service
            $this->app->register(new Provider\MembersServiceProvider($this->app));

            // Set up controller routes
            $this->app->mount('/' . $this->config['basepath'], new Controller\MembersController());

            // Twig functions
            $this->app['twig']->addExtension(new Twig\MembersExtension($this->app));
        }

        /*
         * Hooks
         */
        $this->app['dispatcher']->addListener('clientlogin.Login',  array($this, 'loginCallback'));
        $this->app['dispatcher']->addListener('clientlogin.Logout', array($this, 'logoutCallback'));
    }

    /**
     * Hook for ClientLogin login events
     *
     * @param ClientLoginEvent $event
     */
    public function loginCallback(ClientLoginEvent $event)
    {
        $auth = new Authenticate($this->app);
        $auth->login($event);
    }

    /**
     * Hook for ClientLogin logout events
     *
     * @param ClientLoginEvent $event
     */
    public function logoutCallback(ClientLoginEvent $event)
    {
        $auth = new Authenticate($this->app);
        $auth->logout($event);
    }

    /**
     * Default config options
     *
     * @return array
     */
    protected function getDefaultConfig()
    {
        return array(
            'basepath' => 'members',
            'templates' => array(
                'parent'        => 'members.twig',
                'register'      => 'members_register.twig',
                'profile_edit'  => 'members_profile_edit.twig',
                'profile_view'  => 'members_profile_view.twig'
            ),
            'registration' => true,
            'csrf'         => true
        );
    }
}
