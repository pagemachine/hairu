<?php

declare(strict_types=1);

namespace PAGEmachine\Hairu\Mail;

use TYPO3\CMS\Core\Mail\MailMessage;

interface MailMessageBuilderInterface
{
    public function from(string ...$addresses): MailMessageBuilderInterface;

    public function to(string ...$addresses): MailMessageBuilderInterface;

    public function subject(string $subject): MailMessageBuilderInterface;

    public function text(string $body): MailMessageBuilderInterface;

    public function html(string $body): MailMessageBuilderInterface;

    public function build(): MailMessage;
}
