# CSVWriter
Ease to use CSV Writer for PHP 7+

## Instalation
In your project root, run the following composer command:

    $ composer require tonyfarney/csv-writer

In order to use the encoding conversion implemented, you need to install the **mbstring** extension or a equivalent polyfill such as **symfony/polyfill-mbstring**.

## Writing CSV File From an Array of Data
CSVWriter generates CSV file from array of data containing the lines and columns.
Examples:

    <?php
    require __DIR__.'/vendor/autoload.php';
        
    use \CSVWriter\CSVWriter;
        
    $lines = [
        ['name', 'role', 'age'],
        ['Tony Farney', 'Developer', 30],
        ['The Coffe Guy', 'Intern', 18],
        ['Someone Else', 'Developer', 26],
    ];
        
    $writer = new CSVWriter();
        
    // Add lines to the buffer
    foreach ($lines as $line) {
        $writer->addLine($line);
    }
    // Saves the buffer to a file
    $writer->save('/tmp/file.csv');
        
    // Gets de generated CSV from buffer
    $csv = $writer->getCSV();
    echo $csv."\n\n";
    
    // Changes the configuration and get new CSV from buffer
    $newCsv = $writer->setEnclosureRule(CSVWriter::ENCLOSURE_NONE)
        ->setNewLineDelimiter("\r\n")
        ->setColumnDelimiter(';')
        ->setEncodings('UTF-8', 'ISO-8859-1') // Converts from UTF-8 to ISO-8859-1
        ->getCSV()
    ;
    echo $newCsv;
    

echo $csv; output:

    "name","role","age"
    "Tony Farney","Developer","30"
    "The Coffe Guy","Intern","18"
    "Someone Else","Developer","26"

echo $newCsv; output:

    name;role;age
    Tony Farney;Developer;30
    The Coffe Guy;Intern;18
    Someone Else;Developer;26

## Tips
When writing to a file, it's possible save memory limiting the buffer size:

    // Causes writing to file and clearing the buffer when buffer reaches 1000 lines
    $writer->setMaxLinesBuffer(1000);

It's possible reset configuration and clear the buffer at any time

    // Resets all configurations to it's default value
    $writer->resetConfig();
        
    // Clears the buffer 
    $writer->clearBuffer();
        
    // Resets config and clears the buffer
    $writer->reset();

## Contributions/Support
You are welcome to contribute with improvement, bug fixes, new ideas, etc. Any doubt/problem, please contact me by email: tonyfarney@gmail.com. I'll be glad to help you ;)

