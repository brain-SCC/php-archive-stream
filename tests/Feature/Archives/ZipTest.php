<?php

declare(strict_types=1);

namespace Tests\Feature\Archives;

use PhpArchiveStream\Archives\Zip;
use PhpArchiveStream\IO\Output\OutputStream;
use PhpArchiveStream\Writers\Zip\ZipWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Zip::class)]
class ZipTest extends TestCase
{
    protected string $outputPath = './output.zip';

    protected string $inputPath1 = './input1.txt';

    protected string $inputPath2 = './input2.txt';

    protected function setUp(): void
    {
        if (file_exists($this->outputPath)) {
            unlink($this->outputPath);
        }

        if (file_exists($this->inputPath1)) {
            unlink($this->inputPath1);
        }

        if (file_exists($this->inputPath2)) {
            unlink($this->inputPath2);
        }

        file_put_contents($this->inputPath1, 'Hello World 1');
        file_put_contents($this->inputPath2, 'Hello World 2');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->outputPath)) {
            unlink($this->outputPath);
        }

        if (file_exists($this->inputPath1)) {
            unlink($this->inputPath1);
        }

        if (file_exists($this->inputPath2)) {
            unlink($this->inputPath2);
        }
    }

    public function test_add_file_from_path()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new OutputStream($stream);
        $zipWriter = new ZipWriter($outputStream);
        $zip = new Zip($zipWriter);

        $zip->addFileFromPath('input1.txt', $this->inputPath1);
        $zip->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function test_add_file_from_stream()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new OutputStream($stream);
        $zipWriter = new ZipWriter($outputStream);
        $zip = new Zip($zipWriter);

        $resource = fopen($this->inputPath1, 'r');
        $zip->addFileFromStream('input1.txt', $resource);
        $zip->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function test_add_file_from_content_string()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new OutputStream($stream);
        $zipWriter = new ZipWriter($outputStream);
        $zip = new Zip($zipWriter);

        $zip->addFileFromContentString('input1.txt', 'Hello World 1');
        $zip->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function test_finish()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new OutputStream($stream);
        $zipWriter = new ZipWriter($outputStream);
        $zip = new Zip($zipWriter);

        $zip->finish();

        $reflection = new ReflectionClass($zip);
        $property = $reflection->getProperty('writer');
        $this->assertNull($property->getValue($zip));
    }

    public function test_file_names_are_flagged_as_utf8()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new OutputStream($stream);
        $zipWriter = new ZipWriter($outputStream);
        $zip = new Zip($zipWriter);

        $zip->addFileFromContentString('Grüße/Ärger.txt', 'Hello World 1');
        $zip->finish();

        $data = file_get_contents($this->outputPath);

        $endOfCentralDirectory = substr($data, -22);
        $centralDirectoryOffset = unpack('V', substr($endOfCentralDirectory, 16, 4))[1];

        $this->assertSame("PK\x05\x06", substr($endOfCentralDirectory, 0, 4));
        $this->assertSame("PK\x01\x02", substr($data, $centralDirectoryOffset, 4));

        $localFileHeaderFlag = unpack('v', substr($data, 6, 2))[1];
        $centralDirectoryFlag = unpack('v', substr($data, $centralDirectoryOffset + 8, 2))[1];

        $this->assertSame(0x0808, $localFileHeaderFlag);
        $this->assertSame(0x0808, $centralDirectoryFlag);
    }
}
