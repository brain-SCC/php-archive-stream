<?php

declare(strict_types=1);

namespace Tests\Unit\Archives\Zip\Records\Fields;

use PhpArchiveStream\Writers\Zip\Records\Fields\GeneralPurposeBitFlag;
use PHPUnit\Framework\TestCase;

class GeneralPurposeBitFlagTest extends TestCase
{
    public function test_language_encoding_flag_is_set_by_default(): void
    {
        $flag = GeneralPurposeBitFlag::create();

        $this->assertSame(0b0000100000000000, $flag->getValue());
        $this->assertSame(GeneralPurposeBitFlag::LANGUAGE_ENCODING, $flag->getValue());
    }

    public function test_language_encoding_flag_is_kept_for_store(): void
    {
        $flag = GeneralPurposeBitFlag::create()
            ->setZeroHeader()
            ->setCompressionMethod(0x00);

        $this->assertSame(0x0808, $flag->getValue());
    }

    public function test_language_encoding_flag_is_kept_for_deflate(): void
    {
        $flag = GeneralPurposeBitFlag::create()
            ->setZeroHeader()
            ->setCompressionMethod(0x08);

        $this->assertSame(0x0808, $flag->getValue());
    }
}
