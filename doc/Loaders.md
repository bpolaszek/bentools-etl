Loaders
=======
The loaders are responsible to save or output your transformed data. Here are some built-in loaders, feel free to implement your own ones.


ArrayLoader
-----------
This loader stores your data into an array.

DebugLoader
-----------
At the end of the process, this loader `var_dump` your data. You can use another debug function in the constructor if needed.

FileLoader
----------
This loader saves your data in a file. Make sure your transformer adds a `PHP_EOL` if you want a line for each load (you can also hook on the `ETLEvents::AFTER_TRANSFORM` event)

CsvFileLoader
-------------
This loader will take your `SplFileObject` and call `fputcsv` on each load.

JsonFileLoader
--------------
This loader will store your data as a Json file.


Loaders and FlushableLoaders
============================
By default, each loader will store your data when invoked.

You can create your own loader by implementing `BenTools\ETL\Loader\FlushableLoaderInterface`, which will cause the loader to buffer elements and store them only when the `flush()` method is called (cf. `BenTools\ETL\Loader\DoctrineORMLoader`).


Next: [Events](Events.md)