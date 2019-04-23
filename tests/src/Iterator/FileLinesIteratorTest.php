<?php

namespace BenTools\ETL\Tests\Iterator;

use BenTools\ETL\Iterator\FileLinesIterator;
use PHPUnit\Framework\TestCase;

class FileLinesIteratorTest extends TestCase
{

    /**
     * @test
     */
    public function it_extracts_lines_without_eol()
    {
        $data = [' foo ', ' bar '];
        $file = new \SplTempFileObject();
        foreach ($data as $item) {
            $file->fwrite($item.\PHP_EOL);
        }

        // Just to be sure
        $this->assertSame([' foo '.\PHP_EOL, ' bar '.\PHP_EOL], \iterator_to_array($file));

        $iterator = new FileLinesIterator($file);
        $this->assertSame($data, \iterator_to_array($iterator));
    }

}
