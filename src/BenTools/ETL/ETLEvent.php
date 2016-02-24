<?php
namespace BenTools\ETL;

use Symfony\Component\EventDispatcher\Event;

class ETLEvent extends Event {

    const   ETL_START        = 'etl.start';
    const   ETLBAG_START     = 'etlbag.start';
    const   AFTER_EXTRACT    = 'etl.after.extract';
    const   BEFORE_TRANSFORM = 'etl.before.transform';
    const   AFTER_TRANSFORM  = 'etl.after.transform';
    const   BEFORE_LOAD      = 'etl.before.load';
    const   AFTER_LOAD       = 'etl.after.load';
    const   BEFORE_FLUSH     = 'etl.before.flush';
    const   AFTER_FLUSH      = 'etl.after.flush';
    const   ETLBAG_END       = 'etlbag.end';
    const   ETL_END          = 'etl.end';

    /**
     * @var ETLBag
     */
    protected $etlBag;

    /**
     * ETLEvent constructor.
     * @param ETLBag $etlBag
     * @param Context $context
     */
    public function __construct(ETLBag $etlBag = null) {
        $this->etlBag  = $etlBag;
    }

    /**
     * @return ETLBag
     */
    public function getEtlBag() {
        return $this->etlBag;
    }

    /**
     * @return Context
     */
    public function getContext() {
        if (!$this->etlBag)
            throw new \RuntimeException("No ETLBag provided for this event");
        return $this->getEtlBag()->getContext();
    }
}