<?php

namespace BenTools\ETL\Event;

final class Events {

    const AFTER_EXTRACT   = 'bentools.etl.after_extract';
    const AFTER_TRANSFORM = 'bentools.etl.after_transform';
    const AFTER_LOAD      = 'bentools.etl.after_load';
    const AFTER_FLUSH     = 'bentools.etl.after_flush';

}