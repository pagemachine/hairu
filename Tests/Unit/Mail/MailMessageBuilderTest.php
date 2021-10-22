<?php
declare(strict_types = 1);

namespace PAGEmachine\Hairu\Tests\Mail;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use PAGEmachine\Hairu\Mail\MailMessageBuilder;
use Symfony\Component\Mime\Email;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for PAGEmachine\Hairu\Mail\MailMessageBuilder
 */
final class MailMessageBuilderTest extends UnitTestCase
{
    /**
     * @var MailMessageBuilder
     */
    protected $mailMessageBuilder;

    /**
     * Set up this testcase
     */
    protected function setUp(): void
    {
        if (!is_subclass_of(MailMessage::class, Email::class)) {
            $this->markTestSkipped('Symfony mail only');
        }

        $this->mailMessageBuilder = new MailMessageBuilder();
    }

    /**
     * Tear down this testcase
     */
    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
    }

    /**
     * @test
     */
    public function buildsMailMessage(): void
    {
        $result = $this->mailMessageBuilder
            ->from('from@example.org')
            ->to('to@example.org')
            ->subject('Mail building test')
            ->text('Plain text content')
            ->html('<p><b>Rich</b> text content</p>')
            ->build();

        $this->assertEquals('from@example.org', $result->getFrom()[0]->toString());
        $this->assertEquals('to@example.org', $result->getTo()[0]->toString());
        $this->assertEquals('Mail building test', $result->getSubject());
        $this->assertEquals('Plain text content', $result->getTextBody());
        $this->assertEquals('<p><b>Rich</b> text content</p>', $result->getHtmlBody());
    }
}
