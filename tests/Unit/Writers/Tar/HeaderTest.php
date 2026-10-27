<?php

declare(strict_types=1);

namespace Tests\Unit\Writers\Tar;

use PhpArchiveStream\Writers\Tar\Header;
use PHPUnit\Framework\TestCase;

class HeaderTest extends TestCase
{
    public function test_header_is_512_bytes_for_multibyte_names(): void
    {
        $header = Header::generate('Grüße.txt', 'Übersicht', 13);

        $this->assertSame(512, strlen($header));
        $this->assertSame('Grüße.txt', rtrim(substr($header, 0, 100), "\0"));
        $this->assertSame("ustar\0", substr($header, 257, 6));
        $this->assertSame('Übersicht', rtrim(substr($header, 345, 155), "\0"));
    }
}
