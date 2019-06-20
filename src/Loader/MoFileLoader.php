<?php

declare(strict_types=1);

namespace Stadly\Translation\Loader;

use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Loader\FileLoader;

final class MoFileLoader extends FileLoader
{
    // phpcs:disable Squiz.Commenting.FunctionComment.ScalarTypeHintMissing
    // phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
    /**
     * Parses machine object (MO) format, independent of the machine's endian it
     * was created on. Both 32bit and 64bit systems are supported.
     *
     * @param string $filename Path to resource to load.
     * @return array<string|int, string> Strings loaded from resource.
     */
    protected function loadResource($filename): array
    {
        $stream = fopen($filename, 'r');
        if ($stream === false) {
            // @codeCoverageIgnoreStart
            throw new InvalidResourceException('Could not open file.');
            // @codeCoverageIgnoreEnd
        }

        $this->readLong($stream); // magicNumber
        $this->readLong($stream); // formatRevision
        $count = $this->readLong($stream);
        $offsetId = $this->readLong($stream);
        $offsetTranslated = $this->readLong($stream);
        $this->readLong($stream); // sizeHashes
        $this->readLong($stream); // offsetHashes

        $messages = [];

        for ($i = 0; $i < $count; ++$i) {
            fseek($stream, $offsetId + $i * 8);
            $id = $this->readEntry($stream);

            fseek($stream, $offsetTranslated + $i * 8);
            $translated = $this->readEntry($stream);

            if ($id !== null && $translated !== null) {
                $messages[$id] = $translated;
            }
        }

        fclose($stream);

        return $messages;
    }
    // phpcs:enable

    /**
     * Reads an unsigned long from stream.
     *
     * @param resource $stream Stream to read from.
     * @return int The read number.
     */
    private function readLong($stream): int
    {
        $string = fread($stream, 4);
        if ($string === false) {
            // @codeCoverageIgnoreStart
            throw new InvalidResourceException('Could not read from file.');
            // @codeCoverageIgnoreEnd
        }
        if (strlen($string) !== 4) {
            throw new InvalidResourceException('MO stream content has an invalid format.');
        }
        $number = unpack('V', $string);

        return $number[1];
    }

    /**
     * @param resource $stream Stream to read from.
     * @return string|null The read entry or null on failure.
     */
    private function readEntry($stream): ?string
    {
        $length = $this->readLong($stream);
        $offset = $this->readLong($stream);

        if ($length < 1) {
            return null;
        }

        fseek($stream, $offset);
        $string = fread($stream, $length);
        if ($string === false) {
            // @codeCoverageIgnoreStart
            throw new InvalidResourceException('Could not read from file.');
            // @codeCoverageIgnoreEnd
        }
        return implode('|', explode("\000", $string));
    }
}
