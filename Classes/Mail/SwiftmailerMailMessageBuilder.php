<?php
declare(strict_types = 1);

namespace PAGEmachine\Hairu\Mail;

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class SwiftmailerMailMessageBuilder implements MailMessageBuilderInterface
{
    /**
     * @var MailMessage
     */
    private $message;

    public function __construct()
    {
        $this->message = GeneralUtility::makeInstance(MailMessage::class);
    }

    public function from(string ...$addresses): MailMessageBuilderInterface
    {
        $this->message->setFrom($addresses);

        return $this;
    }

    public function to(string ...$addresses): MailMessageBuilderInterface
    {
        $this->message->setTo($addresses);

        return $this;
    }

    public function subject(string $subject): MailMessageBuilderInterface
    {
        $this->message->setSubject($subject);

        return $this;
    }

    public function text(string $body): MailMessageBuilderInterface
    {
        $this->message->setBody($body, 'text/plain');

        return $this;
    }

    public function html(string $body): MailMessageBuilderInterface
    {
        $this->message->addPart($body, 'text/html');

        return $this;
    }

    public function build(): MailMessage
    {
        return $this->message;
    }
}
