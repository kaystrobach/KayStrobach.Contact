<?php

namespace KayStrobach\Contact\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "SBS.LaPo".              *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objekt zum erzeugen von Studenten!
 *
 * @Flow\Scope("singleton")
 */

class MailUtility
{
    /**
     * The current view, as resolved by resolveView()
     *
     * @Flow\Inject
     * @var \TYPO3\Fluid\View\StandaloneView
     * @api
     */
    protected $view = null;

    /**
     * @Flow\Inject
     * @var \TYPO3\SwiftMailer\TransportFactory
     * @api
     */
    protected $mailerTransportFactory = null;

    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Configuration\ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings = array();

    /**
     * Hier wird die Mail an das Sendesystem übergeben.
     *
     * Das Template sollte wie folgt aussehen:
     *
     * <f:section name="subject">
     *     Betreff
     * </f:section>
     * <f:section name="text">
     *     Mailtext ohne html
     * </f:section>
     * <f:section name="html">
     *     html mail
     * </f:section>
     *
     * @param string $recipientMail
     * @param string $templateFilePath Pfad zum Fluid Template
     * @param array $values    array mit Schlüssel => Objekt / Wert Zuordungen
     */
    public function send($recipientMail, $templateFilePath, $values = array(), $replyTo = null)
    {
        $this->initConfiguration();

        /** @var $mail \TYPO3\SwiftMailer\Message() */
        $this->view->setTemplatePathAndFilename(FLOW_PATH_ROOT . 'Packages/Application/KayStrobach.Contact/Resources/Private/Mails/' . $templateFilePath . '.html');
        $this->view->assignMultiple($values);

        $renderedMailSubject     = trim($this->view->renderSection('subject', null, true));
        $renderedMailContentText = trim($this->view->renderSection('text', null, true));
        $renderedMailContentHtml = trim($this->view->renderSection('html', null, true));

        $mail = new \TYPO3\SwiftMailer\Message();
        $mail
            ->setFrom($this->settings['from'])
            ->setReplyTo($replyTo ? $replyTo : $this->settings['reply-to'])
            ->setTo($recipientMail)
            ->setSubject($renderedMailSubject)
            ->setPriority(1);
        if ($renderedMailContentHtml !== '') {
            $mail->addPart($renderedMailContentHtml, 'text/html', 'utf-8');
        }
        if ($renderedMailContentText !== '') {
            $mail->addPart($renderedMailContentText, 'text/plain', 'utf-8');
        }
        $mail->send();
    }

    protected function initConfiguration()
    {
        $this->settings = $this->configurationManager->getConfiguration(
            \TYPO3\FLOW\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS,
            'KayStrobach.Contact.MailUtility'
        );
    }
}
