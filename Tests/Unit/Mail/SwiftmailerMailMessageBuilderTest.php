<?php
declare(strict_types = 1);

namespace PAGEmachine\Hairu\Tests\Mail;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use PAGEmachine\Hairu\Mail\SwiftmailerMailMessageBuilder;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for PAGEmachine\Hairu\Mail\SwiftmailerMailMessageBuilder
 */
final class SwiftmailerMailMessageBuilderTest extends UnitTestCase
{
    /**
     * @var SwiftmailerMailMessageBuilder
     */
    protected $mailMessageBuilder;

    /**
     * Set up this testcase
     */
    protected function setUp()
    {
        if (!is_subclass_of(MailMessage::class, \Swift_Message::class)) {
            $this->markTestSkipped('Swiftmailer mail only');
        }

        $this->mailMessageBuilder = new SwiftmailerMailMessageBuilder();
    }

    /**
     * Tear down this testcase
     */
    protected function tearDown()
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

        $this->assertEquals('from@example.org', array_keys($result->getFrom())[0]);
        $this->assertEquals('to@example.org', array_keys($result->getTo())[0]);
        $this->assertEquals('Mail building test', $result->getSubject());
        $this->assertEquals('Plain text content', $result->getBody());
        $this->assertEquals('<p><b>Rich</b> text content</p>', $result->getChildren()[0]->getBody());
    }
}
