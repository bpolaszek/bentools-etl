<?php
namespace BenTools\ETL\Transformer;

use BenTools\ETL\Interfaces\ContextInterface;
use BenTools\ETL\Interfaces\TransformerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class IsoTransformer implements TransformerInterface, LoggerAwareInterface {

    use LoggerAwareTrait;

    /**
     * @param $data
     * @param ContextInterface $context
     * @return mixed
     */
    public function transform($data, ContextInterface $context) {
        $context->setTransformedData($data);
        return $data;
    }

}