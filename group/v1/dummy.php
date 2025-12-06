<?php

/**
 * Generates a dummy contacts list and saves it to a CSV file.
 *
 * @param string $filePath Path to the output file.
 * @param int $numberOfContacts Number of dummy contacts to generate.
 */
function generateDummyContacts(string $filePath, int $numberOfContacts): void
{
    // Open the file for writing
    $file = fopen($filePath, 'w');

    if (!$file) {
        die("Error: Unable to open file for writing.");
    }

    // Write the CSV header
    fputcsv($file, ['Name', 'Phone']);

    // Generate contacts
    for ($i = 1; $i <= $numberOfContacts; $i++) {
        $name = "Contact_" . $i;
        $phone = '07' . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        fputcsv($file, [$name, $phone]);

        // Optional: Progress indicator
        if ($i % 100000 === 0) {
            echo "Generated $i contacts...\n";
        }
    }

    // Close the file
    fclose($file);

    echo "Dummy contacts list created successfully! File: $filePath\n";
}

// Define the output file and number of contacts
$outputFile = '/home/s/Documents/Customers/Techpitch/dummy_contacts.csv';
$numberOfContacts = 1000000;

// Generate the dummy contacts list
generateDummyContacts($outputFile, $numberOfContacts);
