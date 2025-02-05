<?php

function obfuscate_email(string $email): string
{
    $parts = explode('@', $email);

    $obsfucateCount = fn (string $val) => (int) floor(strlen($val) * 0.75);

    $firstPartObfuscateCount = $obsfucateCount($parts[0]);
    $firstSection            = substr($parts[0], 0, strlen($parts[0]) - $firstPartObfuscateCount) . str_repeat('*', $firstPartObfuscateCount);

    $secondPartObfuscateCount = $obsfucateCount($parts[1]);
    $secondSection            = str_repeat('*', $secondPartObfuscateCount) . substr($parts[1], (strlen($parts[1]) - $secondPartObfuscateCount) * -1);

    return "{$firstSection}@{$secondSection}";
}
