<?php

declare(strict_types=1);

namespace Stadly\Translation\Loader;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * @coversDefaultClass \Stadly\Translation\Loader\MoFileLoader
 * @covers ::<protected>
 * @covers ::<private>
 */
final class MoFileLoaderTest extends TestCase
{
    /**
     * @covers ::load
     */
    public function testCanLoadFile(): void
    {
        $loader = new MoFileLoader();
        $resource = __DIR__ . '/../resources/messages.mo';
        $catalogue = $loader->load($resource, 'en_US', 'messages');

        self::assertEquals([
            'foo'
                => 'bar',
            'one foo|%count% foos'
                => 'one bar|%count% bars',
            '{0} no foos|one foo|%count% foos'
                => '{0} no bars|one bar|%count% bars',
            'missing foo plural|missing foo plurals'
                => 'missing bar plural|',
            'string containing || foo'
                => 'string containing || bar',
            'one string containing || foo|%count% strings containing || foos'
                => 'one string containing || bar|%count% strings containing || bars',
            '{0} no strings containing || foos|one string containing || foo|%count% strings containing || foos'
                => '{0} no strings containing || bars|one string containing || bar|%count% strings containing || bars',
            'escaped "foo"'
                => 'escaped "bar"',
            'one escaped "foo"|%count% escaped "foos"'
                => 'one escaped "bar"|%count% escaped "bars"',
            '{0} no escaped "foos"|one one escaped "foo"|%count% escaped "foos"'
                => '{0} no escaped "bars"|one one escaped "bar"|%count% escaped "bars"',
        ], $catalogue->all('messages'));
    }

    /**
     * @covers ::load
     */
    public function testCannotLoadEmptyFile(): void
    {
        $loader = new MoFileLoader();
        $resource = __DIR__ . '/../resources/empty.mo';

        $this->expectException(InvalidResourceException::class);

        $loader->load($resource, 'en_US', 'messages');
    }

    /**
     * @covers ::load
     */
    public function testCannotLoadInvalidFile(): void
    {
        $loader = new MoFileLoader();
        $resource = __DIR__ . '/../resources/invalid.mo';

        $this->expectException(InvalidResourceException::class);

        $loader->load($resource, 'en_US', 'messages');
    }

    /**
     * @covers ::load
     */
    public function testCannotLoadNonExistingFile(): void
    {
        $loader = new MoFileLoader();
        $resource = __DIR__ . '/../resources/non-existing.mo';

        $this->expectException(NotFoundResourceException::class);

        $loader->load($resource, 'en_US', 'messages');
    }
}
