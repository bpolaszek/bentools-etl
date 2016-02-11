<?php
namespace BenTools\ETL\Transformer;

use Knp\ETL\ContextInterface;
use Knp\ETL\TransformerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class IsoTransformer implements TransformerInterface, LoggerAwareInterface {

    use LoggerAwareTrait;

    /**
     * transforms array data into specific representation
     *
     * @param mixed $data the extracted data to transform
     *
     * @return mixed the transformed data
     */
    public function transform($data, ContextInterface $context) {
        $context->setTransformedData($data);
        return $data;
    }

}