<?php

namespace BenTools\ETL\Event;

final class ETLEvents
{

    const START                  = 'bentools.etl.start';
    const AFTER_EXTRACT          = 'bentools.etl.after_extract';
    const ON_EXTRACT_EXCEPTION   = 'bentools.etl.on_extract_exception';
    const AFTER_TRANSFORM        = 'bentools.etl.after_transform';
    const ON_TRANSFORM_EXCEPTION = 'bentools.etl.on_transform_exception';
    const AFTER_LOAD             = 'bentools.etl.after_load';
    const ON_LOAD_EXCEPTION      = 'bentools.etl.on_load_exception';
    const AFTER_FLUSH            = 'bentools.etl.after_flush';
    const ON_FLUSH_EXCEPTION     = 'bentools.etl.on_flush_exception';
    const END                    = 'bentools.etl.end';
}
