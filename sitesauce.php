<?php

defined('_JEXEC') or die;

class plgSystemSitesauce extends JPlugin
{
    /**
     * @var \Joomla\CMS\Application\CMSApplication
     */
    protected $app;

    /***
     * @param $context
     * @return array|void
     */
    public function onGetIcons($context)
    {
        if ($context !== 'mod_quickicon') {
            return;
        }

        if (!$this->params->get('build_hook', false)) {
            return;
        }

        if (!$this->params->get('enable_quickicon', true)) {
            return;
        }

        $this->loadLanguage('plg_system_sitesauce');

        return [
            [
                'image' => $this->params->get('quickicon_icon', 'star'),
                'text' => $this->params->get('quickicon_label', \JText::_('PLG_SYSTEM_SITESAUCE_QUICKICON_LABEL')),
                'link' => "index.php?option=com_ajax&p=sitesauce&t=deploy",
            ]
        ];
    }

    public function onAfterRoute()
    {
        if (!$this->app->isClient('administrator')) {
            return;
        }

        $this->handleRequest();
    }

    protected function handleRequest()
    {
        $path = $this->app->input->get('p');
        $task = $this->app->input->get('t');
        $option = $this->app->input->getCmd('option');

        if ($option !== 'com_ajax' || $path !== 'sitesauce') {
            return;
        }

        if (!$this->params->get('build_hook', false)) {
            return;
        }

        if (\JFactory::getUser()->guest) {
            $this->app->redirect(\JRoute::_('index.php?option=com_users&view=login', false));
            return;
        }

        if ($task === 'deploy') {
            $this->deploy();
            return;
        }
    }

    protected function deploy()
    {
        $response = JHttpFactory::getHttp()->get($this->params->get('build_hook'));
        if ($response->code >= 200 && $response->code <= 299) {
            $this->app->enqueueMessage(\JText::_('PLG_SYSTEM_SITESAUCE_DEPLOY_STARTED'));
            $this->app->redirect(\JRoute::_('index.php', false));
            return;
        }

        $this->app->enqueueMessage('PLG_SYSTEM_SITESAUCE_DEPLOY_FAILED');
        $this->app->redirect(\JRoute::_('index.php', false));
    }
}
