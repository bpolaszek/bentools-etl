<?php

declare(strict_types=1);

namespace BenTools\ETL\Tests\Unit\Loader\Doctrine;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity]
final class Book
{
    public function __construct(
        #[Id, Column, GeneratedValue]
        public int $id,
        #[Column]
        public string $name,
    ) {
    }
}
