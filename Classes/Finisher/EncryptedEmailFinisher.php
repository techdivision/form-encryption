<?php
namespace TechDivision\Form\Encryption\Finisher;

/**
 * This file is part of the TechDivision.Form.Encryption package.
 *
 * TechDivision - neos@techdivision.com
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Form\Exception\FinisherException;
use Neos\Form\Finishers\EmailFinisher;
use Neos\SwiftMailer\Message as SwiftMailerMessage;
use Neos\Flow\Annotations as Flow;
use TechDivision\Form\Encryption\Encrypter\GpgEncrypter;

/**
 * Class EncryptedEmailFinisher
 * @package TechDivision\Form\Encryption\Finisher
 */
class EncryptedEmailFinisher extends EmailFinisher
{

    /**
     * @Flow\Inject
     * @var GpgEncrypter
     */
    protected $encrypter;


    /**
     * Executes this finisher, but relies heavily on the EmailFinisher Logic.
     *
     * @return void
     * @throws FinisherException
     */
    protected function executeInternal()
    {
        if (!class_exists(SwiftMailerMessage::class)) {
            throw new FinisherException('The "neos/swiftmailer" doesn\'t seem to be installed, but is required for the EmailFinisher to work!', 1503392532);
        }
        $formRuntime = $this->finisherContext->getFormRuntime();
        $standaloneView = $this->initializeStandaloneView();
        $standaloneView->assign('form', $formRuntime);
        $referrer = $formRuntime->getRequest()->getHttpRequest()->getUri();
        $standaloneView->assign('referrer', $referrer);
        $message = $standaloneView->render();

        $subject = $this->parseOption('subject');
        $recipientAddress = $this->parseOption('recipientAddress');
        $recipientName = $this->parseOption('recipientName');
        $senderAddress = $this->parseOption('senderAddress');
        $senderName = $this->parseOption('senderName');
        $replyToAddress = $this->parseOption('replyToAddress');
        $carbonCopyAddress = $this->parseOption('carbonCopyAddress');
        $blindCarbonCopyAddress = $this->parseOption('blindCarbonCopyAddress');
        $format = $this->parseOption('format');
        $testMode = $this->parseOption('testMode');

        if ($subject === null) {
            throw new FinisherException('The option "subject" must be set for the EmailFinisher.', 1327060320);
        }
        if ($recipientAddress === null) {
            throw new FinisherException('The option "recipientAddress" must be set for the EmailFinisher.', 1327060200);
        }
        if (is_array($recipientAddress) && !empty($recipientName)) {
            throw new FinisherException('The option "recipientName" cannot be used with multiple recipients in the EmailFinisher.', 1483365977);
        }
        if ($senderAddress === null) {
            throw new FinisherException('The option "senderAddress" must be set for the EmailFinisher.', 1327060210);
        }

        $mail = new SwiftMailerMessage();

        $mail
            ->setFrom(array($senderAddress => $senderName))
            ->setSubject($subject);

        if (is_array($recipientAddress)) {
            $mail->setTo($recipientAddress);
        } else {
            $mail->setTo(array($recipientAddress => $recipientName));
        }

        if ($replyToAddress !== null) {
            $mail->setReplyTo($replyToAddress);
        }

        if ($carbonCopyAddress !== null) {
            $mail->setCc($carbonCopyAddress);
        }

        if ($blindCarbonCopyAddress !== null) {
            $mail->setBcc($blindCarbonCopyAddress);
        }

        if ($format === self::FORMAT_PLAINTEXT) {
            $mail->setBody($message, 'text/plain');
        } else {
            $mail->setBody($message, 'text/html');
        }
        $this->addAttachments($mail);

        if ($testMode === true) {
            \Neos\Flow\var_dump(
                array(
                    'sender' => array($senderAddress => $senderName),
                    'recipients' => is_array($recipientAddress) ? $recipientAddress : array($recipientAddress => $recipientName),
                    'replyToAddress' => $replyToAddress,
                    'carbonCopyAddress' => $carbonCopyAddress,
                    'blindCarbonCopyAddress' => $blindCarbonCopyAddress,
                    'message' => $message,
                    'format' => $format,
                ),
                'E-Mail "' . $subject . '"'
            );
        } else {
            // Encrypt message body
            $encryptedMessage = $this->encrypter->encryptMessage($mail->getBody());

            if ($format === self::FORMAT_PLAINTEXT) {
                $mail->setBody($encryptedMessage);
            } else {
                $mail->setBody('<pre>' . $encryptedMessage . '</pre>');
            }

            // Send mail
            $mail->send();
        }
    }

}
