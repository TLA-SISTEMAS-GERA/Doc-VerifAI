<?php
# Includes the autoloader for libraries installed with composer
require dirname(__DIR__ ,1) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

putenv('GOOGLE_APPLICATION_CREDENTIALS' . $_ENV['GOOGLE_APPLICATION_CREDENTIALS']);
# Imports the Google Cloud client library
use Google\Cloud\Storage\StorageClient;

# Your Google Cloud Platform project ID
$projectId = getenv('PROJECT_ID');

# Instantiates a client
$storage = new StorageClient([
    'projectId' => $projectId
]);

# The name for the new bucket
$bucketName = 'doc-verifia-bucket';

# Creates the new bucket
$bucket = $storage->createBucket($bucketName);

echo 'Bucket ' . $bucket->name() . ' created.';
?>