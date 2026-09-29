<?php

$caPath = getenv('MYSQL_ATTR_SSL_CA');

if (!$caPath || !file_exists($caPath) || !is_readable($caPath)) {
    fwrite(STDERR, "Cannot read MySQL CA file. Check Render Secret Files and MYSQL_ATTR_SSL_CA: {$caPath}\n");
    exit(1);
}

$content = file_get_contents($caPath);
if (!str_contains($content, 'BEGIN CERTIFICATE')) {
    fwrite(STDERR, "MySQL CA certificate does not appear to be a valid PEM file.\n");
    exit(1);
}

echo "MySQL CA certificate validated successfully.\n";
exit(0);
